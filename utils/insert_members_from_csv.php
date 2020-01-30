<?php
$homedir = '/var/www/html/gairyo_temp';
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '9957';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$sql = 'DELETE FROM members_new WHERE 1';
$dbh->exec($sql);
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
    $stmt = $dbh->prepare($sql);
    $stmt->execute($arrVals);
    var_dump($stmt->errorInfo());
    echo '<br>';
}
