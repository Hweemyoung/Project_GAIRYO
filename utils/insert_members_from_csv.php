<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
$sql = 'DELETE FROM members_new WHERE 1';
$master_handler->dbh->exec($sql);
$csv = array_map('str_getcsv', file("$homedir/data/csv/members_new.csv"));
$arrCols = $csv[0];
$sqlCols = implode(', ', $arrCols);
for ($i = 1; $i < count($csv) - 1; $i++) {
    $arrVals = $csv[$i];
    // $sqlVals = implode(', ', $arrVals);
    var_dump($arrVals);
    echo '<br>';
    $sql = "INSERT INTO members_new ($sqlCols) VALUES (?,?,?,?,?,?,?,?,?,?);";
    echo $sql . '<br>';
    $stmt = $master_handler->dbh->prepare($sql);
    $stmt->execute($arrVals);
    var_dump($stmt->errorInfo());
    echo '<br>';
}
