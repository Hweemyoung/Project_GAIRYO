<?php
require './check_session.php';

$arrayNames = array('id_from', 'month', 'day', 'shift', 'id_to');
$arrayFormIds = explode(',', $_POST["formIDs"]);
// var_dump($arrayFormIds);OK
$SQLS = '';
$sql = "SELECT id_transaction FROM requests_pending ORDER BY id_transaction DESC LIMIT 1;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
// Set next id_transaction
$id_transaction = intval(($stmt->fetchAll())[0]['id_transaction']) + 1;
// For every formId
foreach ($arrayFormIds as $formId) {
    // For every name in formId
    foreach ($arrayNames as $name) {
        // Create variables: $id_from, $month, ...
        $$name = $_POST[$name . '_' . $formId];
    }
    $month = explode(' ', $month);
    $Y = $month[0];
    $M = $month[1];
    $dateShift = date('Y-m-d', strtotime("$Y-$M-$day")); // new DateTime(strtotime('2020 Jan 20'))
    // Check if there is such a shift
    $sql = "SELECT id_shift FROM shifts_assigned WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift' AND done=0;";
    // echo $sql;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    // var_dump($stmt->errorInfo());OK
    $result = $stmt->fetchAll();
    if (!$result) {
        $nickname = $arrayMembersByIdUser[$id_from]["nickname"];
        echo "ERROR - Shift NOT Found: $nickname $dateShift $shift";
        exit;
    } else {
        $id_shift = $result[0]["id_shift"];
        // Check if there is the same request already
        $sql = "SELECT id_request, id_transaction FROM requests_pending WHERE id_shift=$id_shift AND id_to=$id_to AND `status`=2;";
        $stmt = $dbh->prepare($sql);
        $stmt->execute();
        // var_dump($stmt->errorInfo());OK
        $result = $stmt->fetchAll();
        if ($result){
            $id_request = $result[0]["id_request"];
            $id_transaction = $result[0]["id_transaction"];
            $nickname_from = $arrayMembersByIdUser[$id_from]["nickname"];
            $nickname_to = $arrayMembersByIdUser[$id_to]["nickname"];
            echo "ERROR - Request already exists.<br>Request ID = $id_request<br>Transaction ID = $id_transaction<br>$nickname_from's $dateShift $shift to $nickname_to";
            exit;
        }
        // shifts_assigned: Update under_request
        $sql = "UPDATE shifts_assigned SET under_request=1 WHERE id_user=$id_from AND shift='$shift' AND date_shift='$dateShift';";
        $SQLS = $SQLS . $sql;
        // requests_pending: Insert new request
        $id_created = $id_user;
        $time_created = new DateTime();
        $time_created = $time_created->format('Y-m-d H:i:s'); // '2020-01-20 19:23:13
        $agreed_from = 0;
        $agreed_to = 0;
        $checked_from = 0;
        $checked_to = 0;
        if ($id_user === $id_from) {
            $agreed_from = 1;
            $checked_from = 1;
        } else if ($id_user === $id_to){
            $agreed_to = 1;
            $checked_to = 1;
        }
        $sql = "INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, `status`, time_proceeded, id_transaction, agreed_from, agreed_to, checked_from, checked_to) VALUES ($id_shift, $id_from, $id_to, $id_created, '$time_created', 2, '$time_created', $id_transaction, $agreed_from, $agreed_to, $checked_from, $checked_to);";
        $SQLS = $SQLS . $sql;
    }
}
echo $SQLS;

$stmt = $dbh->prepare($SQLS);
$stmt->execute();
// var_dump($stmt->errorInfo());