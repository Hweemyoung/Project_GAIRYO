<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_market_purchase_processor.php";
class MarketItemUploader extends DBHandler
{
    public $useSavepoints;
    public $arrModes;
    public $url;
    # This process heavily depends on DBEngine, for it uses MySQL savepoints.
    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->SQLS = '';
        $this->arrModes = ['put', 'call'];
        $this->url = "transactions.php";
        $this->key = NULL;
        $this->marketItemForIter = NULL;
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        // First call of process
        if ($this->key === NULL) {
            $this->setUseSavepoints($this->lockTablesIfNotInnoDB(['shifts_assigned', 'requests_pending']));
            $this->setMode();
            $this->setProps();
            $this->validateUser($this->mode);
        }
        $this->upload_market_item($this->mode);
        echo "key: $this->key<br>";
        echo "marketItemForIter: $this->marketItemForIter<br>";
        if ($this->key !== NULL && $this->marketItemForIter === NULL) {
            // Iteration completed but no call objects found. Just upload put object and commit.
            echo '// Iteration completed but no call objects found. Just upload put object and commit.';
            $stmt = $this->querySql($this->SQLS);
            var_dump($stmt->errorInfo());
            // exit;
            $this->redirect(true, $this->url, ['f' => 3, 's' => 0]);
        } else {
            // Continue iteration
            $this->matchCounterItem($this->mode, $this->key, $this->marketItemForIter);
        }
    }

    private function setProps(){
        if ($this->mode === 'put'){
            $this->set_props_put();
        } elseif($this->mode === 'call'){
            $this->set_props_call();
        }
    }

    private function setUseSavepoints($arrTableNamesLock)
    {
        if (count($arrTableNamesLock)) {
            // Don't use savepoint.
            $this->useSavepoints = false;
        } else {
            $this->useSavepoints = true;
        }
    }

    private function setMode()
    {
        if (!isset($_GET['mode'])) {
            echo 'Error: mode not set. exit!<br>';
            // exit;
            $this->redirect(false, $this->url, ['f' => 3, 'e' => 0]);
        } elseif (!in_array($_GET['mode'], $this->arrModes)) {
            echo 'Error: mode not understood. exit!<br>';
            // exit;
            $this->redirect(false, $this->url, ['f' => 3, 'e' => 1]);
        }
        $this->mode = $_GET['mode'];
    }

    private function validateUser($mode)
    {
        switch ($mode) {
            case 'put':
                if ($this->id_user !== $this->id_from) {
                    echo "Error- invalid user: id_user = $this->id_user and id_from = $this->id_from<br>";
                    // exit;
                }
                break;
            case 'call':
                if ($this->id_user !== $this->id_to) {
                    echo "Error- invalid user: id_user = $this->id_user and id_from = $this->id_to<br>";
                    // exit;
                }
                break;
        }
    }

    private function upload_market_item(string $mode)
    {
        $this->setIdTrans();
        if ($mode === 'put') {
            $this->upload_put_item();
        } elseif ($mode === 'call') {
            $this->upload_call_item();
        }
    }

    private function setIdTrans()
    {
        $sql = "SELECT id_transaction FROM requests_pending ORDER BY id_transaction DESC LIMIT 1 FOR UPDATE;";
        $stmt = $this->querySql($sql);
        // Set next id_transaction
        $arrayIdtrans = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        if (count($arrayIdtrans)) {
            $this->id_transaction = intval($arrayIdtrans[0]) + 1;
        } else {
            $this->id_transaction = 1;
        }
    }

    private function upload_put_item()
    {
        $sqlConditions_shifts = "id_user=$this->id_from AND id_shift=$this->id_shift AND date_shift='$this->date_shift' AND shift='$this->shift'";
        $this->validate_put($sqlConditions_shifts);
        // shifts_assigned: Update under_request
        $sql = "UPDATE shifts_assigned SET under_request=1 WHERE $sqlConditions_shifts AND done=0;";
        $this->SQLS = $this->SQLS . $sql;
        // requests_pending: Insert new request
        $time_created = date('Y-m-d H:i:s');
        $agreed_from = 1;
        $agreed_to = 0;
        $checked_from = 1;
        $checked_to = 0;
        $sql = "INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($this->id_shift, $this->id_from, NULL, $this->id_from, '$time_created', 2, '$time_created', $this->id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
        $this->SQLS = $this->SQLS . $sql;
        echo "Put SQLS: $this->SQLS<br>";
    }

    private function set_props_put()
    {
        $arrNames = ['id_from', 'id_shift', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                // exit;
            }
        }
        $this->id_from = $_GET['id_from'];
        $this->id_shift = $_GET['id_shift'];
        $this->date_shift = $_GET['date_shift'];
        $this->shift = $_GET['shift'];
    }

    private function validate_put($sqlConditions_shifts)
    {
        // Check if there is such a shift and lock it.
        echo '// Check if there is such a shift<br>';
        $sql = "SELECT EXISTS (SELECT 1 FROM shifts_assigned WHERE $sqlConditions_shifts AND done=0 LIMIT 1 FOR UPDATE);";
        echo $sql;
        $stmt = $this->querySql($sql);
        $exists = $stmt->fetch();
        $stmt->closeCursor();
        if (!$exists) {
            echo "ERROR: No such shift found: WHERE $sqlConditions_shifts AND done=0<br>";
            // exit;
        }
        // Check if item already exists in market
        $sqlConditions_requests = "id_shift=$this->id_shift AND id_from=$this->id_from AND id_to IS NULL AND `status`=2";
        $this->check_request_overlap($sqlConditions_requests);
    }

    private function upload_call_item()
    {
        $sqlConditions_shifts = "id_user<>$this->id_to AND date_shift='$this->date_shift' AND shift='$this->shift'";
        $this->validate_call($sqlConditions_shifts);
        // requests_pending: Insert new request
        $time_created = date('Y-m-d H:i:s');
        $agreed_from = 0;
        $agreed_to = 1;
        $checked_from = 0;
        $checked_to = 1;
        $sql = "INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES (NULL, NULL, $this->id_to, $this->id_to, '$time_created', 2, '$time_created', $this->id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
        $this->SQLS = $this->SQLS . $sql;
        echo "Call SQLS: $this->SQLS<br>";
        $stmt = $this->querySql($this->SQLS);
        var_dump($stmt->errorInfo());
        // exit;
        $this->redirect(true, 'transactions.php', ['f' => 3, 's' => 1]);
    }

    private function set_props_call()
    {
        $arrNames = ['id_to', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                // exit;
            }
        }
        $this->id_to = $_GET['id_to'];
        $this->date_shift = $_GET['date_shift'];
        $this->shift = $_GET['shift'];
    }

    private function validate_call($sqlConditions_shifts)
    {
        // Check if there is such a shift and lock it (to prevent id_user from turning into id_to while processing)
        echo '// Check if there is any shift for this call<br>';
        $sql = "SELECT EXISTS (SELECT 1 FROM shifts_assigned WHERE $sqlConditions_shifts AND done=0 LIMIT 1 FOR UPDATE);";
        echo $sql;
        $stmt = $this->querySql($sql);
        $exists = $stmt->fetch();
        $stmt->closeCursor();
        if (!$exists) {
            echo "ERROR: No such shift found: WHERE $sqlConditions_shifts AND done=0<br>";
            // exit;
        }
        // Check if item already exists in market
        $sqlConditions_requests = "id_from IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_to=$this->id_to AND `status`=2";
        $this->check_request_overlap($sqlConditions_requests);
    }

    private function check_request_overlap($sqlConditions_requests)
    {
        echo '// Check if there is already item in market<br>';
        $sql = "SELECT id_request FROM requests_pending WHERE $sqlConditions_requests;";
        echo $sql . '<br>';
        $stmt = $this->querySql($sql);
        $idRequest = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        if (count($idRequest)) {
            $idRequest = $idRequest[0];
            echo "ERROR: Item already exists in market: id_request=$idRequest.<br>";
            // exit;
        }
    }

    private function matchCounterItem($mode, $key, $marketItemForIter)
    {
        if ($mode === 'put') {
            echo 'HERE!<br>';
            if ($marketItemForIter !== NULL) { // If this call is iteration by MyISAM
                $this->checkValidCallObjectMyIsam($key, $marketItemForIter);
            } else { // Could be InnoDB or MyISAM(haven't started iteration)
                // Savepoint
                if (!$this->useSavepoints) {
                    // This is MyISAM(haven't started iteration)
                    // Find any counter item
                    $sql = "SELECT id_transaction, id_request, id_from, id_to FROM requests_pending WHERE id_from IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_to IS NOT NULL AND `status`=2 ORDER BY time_created ASC FOR UPDATE;";
                    $stmt = $this->querySql($sql);
                    $this->arrCallObjectsCandidates = $stmt->fetchAll(PDO::FETCH_CLASS, 'RequestObject');
                    $stmt->closeCursor();
                    if (count($this->arrCallObjectsCandidates)) {
                        // Start iteration
                        $this->checkValidCallObjectMyIsam(0, $this->arrCallObjectsCandidates[0]);
                    }
                } else {
                    // This is InnoDB
                    echo '// This is InnoDB<br>';
                    $this->executeSql('SAVEPOINT awaiting_counter;');
                    $sql = "SELECT id_transaction, id_request, id_from, id_to FROM requests_pending WHERE id_from IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_to IS NOT NULL AND `status`=2 ORDER BY time_created ASC FOR UPDATE;";
                    $stmt = $this->querySql($sql);
                    $this->arrCallObjectsCandidates = $stmt->fetchAll(PDO::FETCH_CLASS, 'RequestObject');
                    $stmt->closeCursor();
                    if (count($this->arrCallObjectsCandidates)) {
                        foreach ($this->arrCallObjectsCandidates as $key => $callObject) {
                            $this->checkValidCallObjectMyIsam($key, $callObject);
                        }
                    }
                    // No valid call objects found. Just commit.
                    echo '// Iteration completed but no call objects found. Just upload put object and commit.';
                    $stmt = $this->querySql($this->SQLS);
                    var_dump($stmt->errorInfo());
                    // exit;
                    $this->redirect(true, $this->url, ['f' => 3, 's' => 0]);
                }
            }
            // No valid call object found.
        }
    }

    private function checkValidCallObjectMyIsam($key, $callObject)
    {
        $market_purchase_processor = new MarketPurchaseProcessor(['mode' => 'call', 'id_request' => $callObject->id_request, 'id_transaction' => $callObject->id_transaction, 'id_shift' => $this->id_shift], $this->master_handler); // Set id_from of call object // User is purchasing 'mode' item.
        $requests_handler = new RequestsHandler('agree', $market_purchase_processor->id_user, $market_purchase_processor->id_transaction, $this->master_handler, $this->config_handler, false);

        if ($requests_handler->return[0]) { // $commit = true
            // Matched valid counter!: commit and redirect!
            echo "// Matched valid counter!: commit and redirect!<br>";
            $stmt = $this->querySql($this->SQLS);
            var_dump($stmt->errorInfo());
            // exit;
            $requests_handler->redirect(true, $this->url, ['f' => 3, 's' => 2]);
        } else { // $commit = false
            if ($this->useSavepoints) {
                // InnoDB
                echo 'Rollback to savepoint.<br>';
                $this->executeSql('ROLLBACK TO awaiting_counter');
                // Back to loop
                return;
            } else {
                // MyISAM
                $this->key = $key + 1;
                if ($key !== count($this->arrCallObjectsCandidates) - 1) {
                    $this->dbh->rollBack();
                    $this->marketItemForIter = $this->arrCallObjectsCandidates[$key + 1];
                } else {
                    // This was last call object and no valid one found.
                    $this->marketItemForIter = NULL;
                }
                $this->process();
            }
        }
    }
}
