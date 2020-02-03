<?php
$homedir = '/var/www/html/gairyo_temp';
$host = 'localhost';
$DBName = 'gairyo_shift_dist';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$dbh->beginTransaction();
$sql = 'DELETE FROM members;';
$dbh->exec($sql);
$fp = "$homedir/data/csv/members.csv";
if (isset($_FILES['csv_member'])){
    $fp = $_FILES['csv_member']["tmp_name"] . $_FILES['csv_member']["name"];
}
$csv = array_map('str_getcsv', file($fp));
$arrCols = $csv[0];
$sqlCols = implode(', ', $arrCols);
for ($i = 1; $i < count($csv) - 1; $i++) {
    $arrVals = $csv[$i];
    // $sqlVals = implode(', ', $arrVals);
    var_dump($arrVals);
    echo '<br>';
    // $sql = "INSERT INTO members ($sqlCols) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
    $sql = "INSERT INTO members ($sqlCols) VALUES (?,?,?,?);"; // id_user, nickname, jp, cn
    echo $sql . '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->execute($arrVals);
    if ($stmt->errorInfo()[0] !== NULL){
        echo "エラーが発生しました。アップロードを中止し、終了します。<br>";
        var_dump($stmt->errorInfo());
        echo '<br>';
        exit;
    }
}
$dbh->commit();