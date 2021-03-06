<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";

class TransactionUploader extends DBHandler
{
    private $arrayFormIds;
    private $id_user;
    private static $arrayNames = array('id_from', 'month', 'day', 'shift', 'id_to');

    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->SQLS = '';
        $this->url = "transactions.php";
        $this->arrayFormIds = explode(',', $_POST["formIDs"]);
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->lockTablesIfNotInnoDB(['shifts_assigned', 'requests_pending']);
        $sql = "SELECT id_transaction FROM requests_pending ORDER BY id_transaction DESC LIMIT 1 FOR UPDATE;";
        $stmt = $this->querySql($sql);
        // Set next id_transaction
        $arrayIdtrans = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        if (count($arrayIdtrans) != 0) {
            $id_transaction = intval($arrayIdtrans[0]) + 1;
        } else {
            $id_transaction = 1;
        }
        // For every formId
        foreach ($this->arrayFormIds as $formId) {
            // For every name in formId
            foreach ($this::$arrayNames as $name) {
                // Create variables: $id_from, $month, ...
                if (!isset($_POST[$name . '_' . $formId])){
                    $this->redirect(false,  $this->url, ['f' => 2, 'e' => 0]);
                }
                $$name = $_POST[$name . '_' . $formId];
            }
            $month = explode(' ', $month);
            $Y = $month[0];
            $M = $month[1];
            $dateTime = DateTime::createFromFormat('Y-M-j', "$Y-$M-$day");
            $dateShift = $dateTime->format('Y-m-d');
            echo $dateShift;
            // Check if there is such a shift
            $sql = "SELECT id_shift FROM shifts_assigned WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift' AND done=0 FOR UPDATE;";
            echo $sql;
            // echo $sql;
            $stmt = $this->querySql($sql);
            // var_dump($stmt->errorInfo());OK
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
            var_dump($stmt->errorInfo());
            var_dump($result);
            $stmt->closeCursor();
            if (count($result) === 0) {
                echo 'here!';
                // exit;
                $this->redirect(false, $this->url, ['f' => 2, 'e' => 1, 'id_from' => $id_from, 'date' => $dateShift, 'shift' => $shift]);
            } else {
                $id_shift = $result[0];
                // shifts_assigned: Update under_request
                $sql = "UPDATE shifts_assigned SET under_request=1 WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift';";
                $this->SQLS = $this->SQLS . $sql;
                // requests_pending: Insert new request
                $time_created = date('Y-m-d H:i:s');
                $agreed_from = 0;
                $agreed_to = 0;
                $checked_from = 0;
                $checked_to = 0;
                if ($this->id_user === $id_from) {
                    $agreed_from = 1;
                    $checked_from = 1;
                } else if ($this->id_user === $id_to) {
                    $agreed_to = 1;
                    $checked_to = 1;
                }
                $sql = "INSERT INTO requests_pending (id_shift, id_from, date_shift, shift, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($id_shift, $id_from, '$dateShift', '$shift', $id_to, $this->id_user, '$time_created', 2, '$time_created', $id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
                $this->SQLS = $this->SQLS . $sql;
            }
        }
        // echo $this->SQLS;
        $stmt = $this->querySql($this->SQLS);
        $stmt->closeCursor();
        // If NULL
        if ($stmt->errorInfo()[0] !== NULL) {
            echo $this->SQLS . '<br>';
            // exit;
            $this->redirect(true, $this->url, ['f' => 2, 's' => 0]);
        } else {
            echo $stmt->errorInfo()[2];
            // exit;
        }
    }
}

$transaction_uploader = new TransactionUploader($master_handler, $config_handler);
// // $arrayNames = array('id_from', 'month', 'day', 'shift', 'id_to');
// // $arrayFormIds = explode(',', $_POST["formIDs"]);
// // $SQLS = '';
// $master_handler->dbh->query('START TRANSACTION;');
// $sql = "SELECT id_transaction FROM requests_pending ORDER BY id_transaction DESC LIMIT 1 FOR UPDATE;";
// $stmt = $this->querySql($sql);
// // Set next id_transaction
// $arrayIdtrans = $stmt->fetchAll(PDO::FETCH_COLUMN);
// if (count($arrayIdtrans) != 0) {
//     $id_transaction = intval($arrayIdtrans[0]) + 1;
// } else {
//     $id_transaction = 1;
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
//         // $id_transaction = $result[0]["id_transaction"];
//         // $nickname_from = $arrayMemberObjectsByIdUser[$id_from]->nickname;
//         // $nickname_to = $arrayMemberObjectsByIdUser[$id_to]->nickname;
//         // echo "ERROR - Request already exists.<br>Request ID = $id_request<br>Transaction ID = $id_transaction<br>$nickname_from's $dateShift $shift to $nickname_to";
//         echo '2';
//         header(`Location: ' . './transactions.php?f=2&e=1&nickfrom=$nickname&nickto=$nickname_to&date=$dateShift&shift=$shift&idrequest=$id_request&idtrans=$id_transaction`);
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
//         $sql = "INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($id_shift, $id_from, $id_to, $id_created, '$time_created', 2, '$time_created', $id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
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
