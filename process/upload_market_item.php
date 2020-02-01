<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";

class MarketItemUploader extends DBHandler
{
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
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->lockTablesIfNotInnoDB(['shifts_assigned', 'requests_pending']);
        $this->setMode();
        $this->validateUser($this->mode);
        $this->upload_market_item($this->mode);
        $this->matchCounterItem();
    }

    private function setMode()
    {
        if (!isset($_GET['mode'])) {
            echo 'Error: mode not set. exit!<br>';
            exit;
            $this->redirect(false, $this->url, ['f' => 3, 'e' => 0]);
        } elseif (!in_array($_GET['mode'], $this->arrModes)) {
            echo 'Error: mode not understood. exit!<br>';
            exit;
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
                    exit;
                }
                break;
            case 'call':
                if ($this->id_user !== $this->id_to) {
                    echo "Error- invalid user: id_user = $this->id_user and id_from = $this->id_to<br>";
                    exit;
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
        $this->set_props_put();
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
        exit;
        $this->redirect(true, 'transactions.php', ['f' => 3, 's' => 0]);
    }

    private function set_props_put()
    {
        $arrNames = ['id_from', 'id_shift', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                exit;
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
            exit;
        }
        // Check if item already exists in market
        $sqlConditions_requests = "id_shift=$this->id_shift AND id_from=$this->id_from AND id_to IS NULL AND `status`=2";
        $this->check_request_overlap($sqlConditions_requests);
    }

    private function upload_call_item()
    {
        $this->set_props_call();
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
        exit;
        $this->redirect(true, 'transactions.php', ['f' => 3, 's' => 1]);
    }

    private function set_props_call()
    {
        $arrNames = ['id_to', 'date_shift', 'shift'];
        foreach ($arrNames as $name) {
            if (!isset($_GET[$name])) {
                echo "Error: Not enough \$_GET variables: $name. exit!<br>";
                exit;
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
            exit;
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
            exit;
        }
    }

    private function matchCounterItem($mode){
        
    }
}

$market_item_uploader = new MarketItemUploader($master_handler, $config_handler);
// // $arrayNames = array('id_from', 'month', 'day', 'shift', 'id_to');
// // $arrayFormIds = explode(',', $_POST["formIDs"]);
// // $SQLS = '';
// $master_handler->dbh->query('START TRANSACTION;');
// $sql = "SELECT id_transaction FROM requests_pending ORDER BY id_transaction DESC LIMIT 1 FOR UPDATE;";
// $stmt = $this->querySql($sql);
// // Set next id_transaction
// $arrayIdtrans = $stmt->fetchAll(PDO::FETCH_COLUMN);
// if (count($arrayIdtrans) != 0) {
//     $this->id_transaction = intval($arrayIdtrans[0]) + 1;
// } else {
//     $this->id_transaction = 1;
// }
// // For every formId
// foreach ($arrayFormIds as $formId) {
//     // For every name in formId
//     foreach ($arrayNames as $name) {
//         // Create variables: $id_from, $month, ...
//         $$name = $_POST[$name . '_' . $formId];
//     }
//     $month = explode(' ', $month);
//     $Y = $month[0];
//     $M = $month[1];
//     $dateShift = date('Y-m-d', strtotime("$Y-$M-$day")); // new DateTime(strtotime('2020 Jan 20'))
//     // Check if there is such a shift
//     $sql = "SELECT id_shift FROM shifts_assigned WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift' AND done=0 FOR UPDATE;";
//     // echo $sql;
//     $stmt = $master_handler->dbh->prepare($sql);
//     $stmt->execute();
//     // var_dump($stmt->errorInfo());OK
//     $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
//     if (!$result) {
//         $nickname = $arrayMemberObjectsByIdUser[$id_from]->nickname;
//         echo '1';
//         header(`Location: ' . './transactions.php?f=2&e=0&nick=$nickname&date=$dateShift&shift=$shift`);
//     } else {
//         $id_shift = $result[0];
//         // Check if there is the same request already
//         // $sql = "SELECT id_request, id_transaction FROM requests_pending WHERE id_shift=$id_shift AND id_to=$id_to AND `status`=2;";
//         // $stmt = $master_handler->dbh->prepare($sql);
//         // $stmt->execute();
//         // var_dump($stmt->errorInfo());OK
//         // $result = $stmt->fetchAll();
//         // if ($result){
//         // $id_request = $result[0]["id_request"];
//         // $this->id_transaction = $result[0]["id_transaction"];
//         // $nickname_from = $arrayMemberObjectsByIdUser[$id_from]->nickname;
//         // $nickname_to = $arrayMemberObjectsByIdUser[$id_to]->nickname;
//         // echo "ERROR - Request already exists.<br>Request ID = $id_request<br>Transaction ID = $this->id_transaction<br>$nickname_from's $dateShift $shift to $nickname_to";
//         echo '2';
//         header(`Location: ' . './transactions.php?f=2&e=1&nickfrom=$nickname&nickto=$nickname_to&date=$dateShift&shift=$shift&idrequest=$id_request&idtrans=$this->id_transaction`);
//         // }
//         // shifts_assigned: Update under_request
//         $sql = "UPDATE shifts_assigned SET under_request=1 WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift';";
//         $SQLS = $SQLS . $sql;
//         // requests_pending: Insert new request
//         $id_created = $id_user;
//         $time_created = new DateTime();
//         $time_created = $time_created->format('Y-m-d H:i:s'); // '2020-01-20 19:23:13'
//         $agreed_from = 0;
//         $agreed_to = 0;
//         $checked_from = 0;
//         $checked_to = 0;
//         if ($id_user === $id_from) {
//             $agreed_from = 1;
//             $checked_from = 1;
//         } else if ($id_user === $id_to) {
//             $agreed_to = 1;
//             $checked_to = 1;
//         }
//         $sql = "INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($id_shift, $id_from, $id_to, $id_created, '$time_created', 2, '$time_created', $this->id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
//         $SQLS = $SQLS . $sql;
//     }
// }
// $master_handler->dbh->query($SQLS);
// $master_handler->dbh->query('COMMIT;');
// // If NULL
// if (!$stmt->errorInfo()[2]) {
//     echo '3';
//     header('Location: ' . './transactions.php?f=2&s=0');
// } else {
//     echo $stmt->errorInfo()[2];
// }
