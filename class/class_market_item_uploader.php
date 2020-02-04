<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_market_purchase_processor.php";
require_once "$homedir/class/class_requests_handler.php";
class MarketItemUploader extends DBHandler
{
    public $useSavepoints;
    public $arrModes;
    public $url;
    public $shiftPart;
    # This process heavily depends on DBEngine, for it uses MySQL savepoints.
    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->config_handler = $config_handler;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
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
            // MyISAM Not compatible. Expel.
            $this->redirectIfNotInnoDB();
            $this->setMode();
            $this->setProps();
            $this->validateUser($this->mode);
        }
        $this->upload_market_item($this->mode);
        echo "key: $this->key<br>";
        echo "marketItemForIter: $this->marketItemForIter<br>";
        if ($this->key !== NULL && $this->marketItemForIter === NULL) {
            // Iteration completed but no call objects found. Just upload put object and commit.
            echo '// Iteration completed but no counter objects found. Just upload and commit.';
            // exit;
            $this->redirect(true, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 's' => 0]);
        } else {
            // Continue iteration
            $this->matchCounterItem($this->mode, $this->key, $this->marketItemForIter);
        }
    }

    private function redirectIfNotInnoDB()
    {
        if (count($this->arrTableStatus)) {
            foreach ($this->arrTableStatus as $tableStatus) {
                if ($tableStatus->Engine !== 'InnoDB') {
                    $this->redirect(false, $this->url, ['f' => 4, 'e' => 8]);
                }
            }
        }
    }

    private function setProps()
    {
        $this->setShiftPart();
        if ($this->mode === 'put') {
            $this->set_props_put();
        } elseif ($this->mode === 'call') {
            $this->set_props_call();
        }
    }

    private function setShiftPart(){
        foreach($this->config_handler->arrayShiftsByPart as $shiftPart=>$arrShifts){
            if (in_array($this->shift, $arrShifts)){
                $this->shiftPart = $shiftPart;
                break;
            }
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
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 0]);
        } elseif (!in_array($_GET['mode'], $this->arrModes)) {
            echo 'Error: mode not understood. exit!<br>';
            // exit;
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 1]);
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
                    $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 2, 'id_user' => $this->id_user, 'id_from' => $this->id_from]);
                }
                break;
            case 'call':
                if ($this->id_user !== $this->id_to) {
                    echo "Error- invalid user: id_user = $this->id_user and id_to = $this->id_to<br>";
                    // exit;
                    $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 3, 'id_user' => $this->id_user, 'id_to' => $this->id_to]);
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
        $this->executeSql($sql);
        // requests_pending: Insert new request
        $time_created = date('Y-m-d H:i:s');
        $agreed_from = 1;
        $agreed_to = 0;
        $checked_from = 1;
        $checked_to = 0;
        $sql = "INSERT INTO requests_pending (id_shift, id_from, date_shift, shift, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($this->id_shift, $this->id_from, '$this->date_shift', '$this->shift', NULL, $this->id_from, '$time_created', 2, '$time_created', $this->id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
        echo $sql . '<br>';
        $this->executeSql($sql);
        // $stmt = $this->querySql($sql);
        // var_dump($stmt->errorInfo());
    }

    private function set_props_put()
    {
        $arrNames = ['id_from', 'id_shift', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                // exit;
                $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 4, 'var' => $name]);
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
        echo $sql . '<br>';
        $stmt = $this->querySql($sql);
        $exists = $stmt->fetch();
        $stmt->closeCursor();
        if (!$exists) {
            echo "ERROR: No such shift found: WHERE $sqlConditions_shifts AND done=0<br>";
            // exit;
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 5]);
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
        $sql = "INSERT INTO requests_pending (id_shift, id_from, date_shift, shift, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES (NULL, NULL, '$this->date_shift', '$this->shift', $this->id_to, $this->id_to, '$time_created', 2, '$time_created', $this->id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
        echo "Upload call item: $sql <br>";
        $this->executeSql($sql);
    }

    private function set_props_call()
    {
        echo 'Setting props for call<br>';
        $arrNames = ['id_to', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                // exit;
                $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 4, 'var' => $name]);
            }
        }
        $this->id_to = $_GET['id_to'];
        $this->date_shift = $_GET['date_shift'];
        $this->shift = $_GET['shift'];
    }

    private function validate_call($sqlConditions_shifts)
    {
        // Check if there is shift candidates and lock it (to prevent id_user from turning into id_to while processing)
        echo '// Check if there is any shift for this call<br>';
        $sql = "SELECT EXISTS (SELECT 1 FROM shifts_assigned WHERE $sqlConditions_shifts AND done=0 LIMIT 1 FOR UPDATE);";
        echo $sql . '<br>';
        $stmt = $this->querySql($sql);
        $exists = $stmt->fetch();
        $stmt->closeCursor();
        if (!$exists) {
            echo "ERROR: No such shift found: WHERE $sqlConditions_shifts AND done=0<br>";
            // exit;
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 5]);
        }
        // Check if user already has any shift in this shift part
        $this->checkShiftsInSamePart();
        // Check if item already exists in market
        $sqlConditions_requests = "id_from IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_to=$this->id_to AND `status`=2";
        $this->check_request_overlap($sqlConditions_requests);
    }

    private function checkShiftsInSamePart()
    {
        $sqlConditions = $this->genSqlConditions($this->config_handler->arrayShiftsByPart[$this->shiftPart], 'shift', 'OR');
        $sql = "SELECT shift FROM shifts_assigned WHERE done=0 AND id_user=$this->id_to AND date_shift='$this->date_shift' AND $sqlConditions;";
        $stmt = $this->querySql($sql);
        $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($result)) {
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 7, 'date' => $this->date_shift, 'shift' => $result[0]]);
        }
    }

    private function check_request_overlap($sqlConditions_requests)
    {
        echo '// Check if there is already item in market<br>';
        $sql = "SELECT id_request FROM requests_pending WHERE $sqlConditions_requests;";
        echo $sql . '<br>';
        $stmt = $this->querySql($sql);
        $id_request = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        if (count($id_request)) {
            $id_request = $id_request[0];
            echo "ERROR: Item already exists in market: id_request=$id_request.<br>";
            // exit;
            $this->redirect(false, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 'e' => 6, 'id_request' => $id_request]);
        }
    }

    private function matchCounterItem($mode, $key, $marketItemForIter)
    {
        if ($mode === 'put') {
            echo 'Searching for valid counter call item<br>.';
            if ($marketItemForIter !== NULL) { // If this call is iteration by MyISAM
                $this->checkValidCallObject($key, $marketItemForIter);
            } else { // Could be InnoDB or MyISAM(haven't started iteration)
                // Load call objects candidates
                $sql = "SELECT id_transaction, id_request, id_from, id_to FROM requests_pending WHERE id_from IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_to IS NOT NULL AND `status`=2 ORDER BY time_created ASC FOR UPDATE;";
                $stmt = $this->querySql($sql);
                $this->arrCallObjectsCandidates = $stmt->fetchAll(PDO::FETCH_CLASS, 'RequestObject');
                $stmt->closeCursor();
                if (count($this->arrCallObjectsCandidates)) {
                    // Savepoint
                    if (!$this->useSavepoints) {
                        // This is MyISAM(haven't started iteration)
                        // Find any counter item

                        // Start iteration
                        $this->checkValidCallObject(0, $this->arrCallObjectsCandidates[0]);
                    } else {
                        // This is InnoDB
                        echo '// This is InnoDB<br>';
                        $this->executeSql('SAVEPOINT awaiting_counter;');
                        foreach ($this->arrCallObjectsCandidates as $key => $callObject) {
                            $this->checkValidCallObject($key, $callObject);
                        }
                        // No valid call objects found. Just commit.
                        echo '// Iteration completed but no call objects found. Just upload put object and commit.';
                        
                    }
                    // (InnoDB)No valid call object found.
                } else {
                    echo 'No counter item. Commit.';
                }
                // exit;
                $this->redirect(true, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 's' => 0, 'date' => $this->date_shift, 'shift' => $this->shift]);
            }
        } elseif ($mode === 'call') {
            echo 'Searching for valid counter put item.<br>';
            if ($marketItemForIter !== NULL) { // If this call is iteration by MyISAM
                $this->checkValidPutObject($key, $marketItemForIter);
            } else { // Could be InnoDB or MyISAM(haven't started iteration)
                // Load put objects candidates
                $sql = "SELECT id_transaction, id_request, id_shift, id_from, date_shift, shift, id_to FROM requests_pending WHERE id_to IS NULL AND date_shift='$this->date_shift' AND shift='$this->shift' AND id_from IS NOT NULL AND `status`=2 ORDER BY time_created ASC FOR UPDATE;";
                $stmt = $this->querySql($sql);
                $this->arrPutObjectsCandidates = $stmt->fetchAll(PDO::FETCH_CLASS, 'RequestObject');
                echo "date_shift: $this->date_shift, shift: $this->shift<br>";
                echo '$this->arrPutObjectsCandidates';
                var_dump($this->arrPutObjectsCandidates);
                echo '<br>';
                $stmt->closeCursor();
                if (count($this->arrPutObjectsCandidates)) {
                    // Savepoint
                    if (!$this->useSavepoints) {
                        // This is MyISAM(haven't started iteration)
                        // Find any counter item
                        // Start iteration
                        echo '// This is MyISAM(haven\'t started iteration).<br>';
                        echo '// Start iteration<br>';
                        $this->checkValidPutObject(0, $this->arrPutObjectsCandidates[0]);
                    } else {
                        // This is InnoDB
                        echo '// This is InnoDB<br>';
                        echo 'Savepoint!<br>';
                        $this->executeSql('SAVEPOINT awaiting_counter;');
                        echo '// Start iteration<br>';
                        foreach ($this->arrPutObjectsCandidates as $key => $callObject) {
                            echo "iter: $key th<br>";
                            $this->checkValidPutObject($key, $callObject);
                        }
                        // No valid put objects found. Just commit.
                        echo '// Iteration completed but no call objects found. Just upload put object and commit.';
                        
                    }
                    // (InnoDB)No valid put object found.
                } else {
                    echo 'No counter item. Commit.';
                }
                // exit;
                $this->redirect(true, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 's' => 1, 'date' => $this->date_shift, 'shift' => $this->shift]);
            }
        }
    }

    private function checkValidPutObject($key, $putObject)
    {
        $market_purchase_processor = new MarketPurchaseProcessor(['mode' => 'put', 'id_request' => $putObject->id_request, 'id_transaction' => $putObject->id_transaction], $this->master_handler); // Set id_to of put object // User is purchasing 'mode' item.
        $requests_handler = new RequestsHandler('agree', $market_purchase_processor->id_user, $market_purchase_processor->id_transaction, $this->master_handler, $this->config_handler, false);
        $this->commitOrBackToLoop($key, $putObject, $requests_handler);
    }

    private function checkValidCallObject($key, $callObject)
    {
        $market_purchase_processor = new MarketPurchaseProcessor(['mode' => 'call', 'id_request' => $callObject->id_request, 'id_transaction' => $callObject->id_transaction, 'id_shift' => $this->id_shift], $this->master_handler); // Set id_from and id_shift of call object // User is purchasing 'mode' item.
        $requests_handler = new RequestsHandler('agree', $market_purchase_processor->id_user, $market_purchase_processor->id_transaction, $this->master_handler, $this->config_handler, false);
        $this->commitOrBackToLoop($key, $callObject, $requests_handler);
    }

    private function commitOrBackToLoop($key, $counterObject, $requests_handler)
    {
        echo '$requests_handler->return<br>';
        var_dump($requests_handler->return);
        echo '<br>';
        if ($requests_handler->return[0]) { // $commit = true
            // Matched valid counter!
            echo "// Matched valid counter!<br>";
            // Set original market item target.
            if ($this->mode === 'put') {
                // $counterObject is call object
                $sql = "UPDATE requests_pending SET id_to=$counterObject->id_to, `status`=1, time_proceeded=NOW(), agreed_to=1, checked_to=1 WHERE id_transaction=$this->id_transaction;";
                echo "Setting put object id_to to counterobject: $sql <br>";
                $this->executeSql($sql);
                // commit and redirect!
                // exit;
                $this->redirect(true, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 's' => 2, 'date_shift' => $this->date_shift, 'shift' => $this->shift, 'id_to' => $counterObject->id_to]);
            } elseif ($this->mode === 'call') {
                $sql = "UPDATE requests_pending SET id_shift=$counterObject->id_shift, id_from=$counterObject->id_from, date_shift='$counterObject->date_shift', shift= '$counterObject->shift', `status`=1, time_proceeded=NOW(), agreed_from=1, checked_from=1 WHERE id_transaction=$this->id_transaction;";
                echo "Setting call object id_shift, id_from, date_shift, shift to counterobject: $sql <br>";
                $this->executeSql($sql);
                // commit and redirect!
                // exit;
                $this->redirect(true, $this->url, $this->master_handler->arrPseudoUser + ['f' => 4, 's' => 3, 'date_shift' => $this->date_shift, 'shift' => $this->shift, 'id_from' => $counterObject->id_from]);
            }
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
