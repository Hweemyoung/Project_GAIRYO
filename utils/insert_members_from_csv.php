<?php
$homedir = '/var/www/html/gairyo_temp';
$host = 'localhost';
$DBName = 'gairyo_shift_dist';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$dbh->beginTransaction();
$sql = 'DELETE FROM members WHERE 1';
$dbh->exec($sql);
$csv = array_map('str_getcsv', file("$homedir/data/csv/members.csv"));
$arrCols = $csv[0];
$sqlCols = implode(', ', $arrCols);
for ($i = 1; $i < count($csv) - 1; $i++) {
    $arrVals = $csv[$i];
    // $sqlVals = implode(', ', $arrVals);
    var_dump($arrVals);
    echo '<br>';
    $sql = "INSERT INTO members ($sqlCols) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
    echo $sql . '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->execute($arrVals);
    var_dump($stmt->errorInfo());
    echo '<br>';
}
$dbh->commit();