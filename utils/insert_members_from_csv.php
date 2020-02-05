<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session_shift_dist.php";

function checkArrVals($dbh, $i, $arrVals)
{
    var_dump($arrVals);
    echo '<br>';
    if (count($arrVals) !== 4) {
        $idx = $i + 1;
        echo "規格エラー: $idx\行目のセルが４つではありません。<br>";
        exit;
    }
    foreach ($arrVals as $iCol => $val) {
        if ($val === '') {
            $iRow = $i + 1;
            $iCol++;
            echo "空欄エラー: $iRow\行の$iCol\列のデータが空欄です。";
            exit;
        }
    }
    $id_user = $arrVals[0];
    $sql = "SELECT nickname FROM members WHERE id_user=$id_user)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $stmt->closeCursor();
    if (count($result)) {
        $nick = $result[0];
        echo "重複：id_user=$id_user\は、すでに$nick\に与えられています。<br>";
        exit;
    }
}

$dbh = $master_handler->dbh;
$dbh->beginTransaction();
$sql = 'DELETE FROM members;';
$dbh->exec($sql);
// $fp = "$homedir/data/csv/members.csv";
var_dump($_FILES);
if (isset($_FILES['csv_members'])) {
    // $fp = $_FILES['csv_members']["tmp_name"] . '/' . $_FILES['csv_members']["name"];
    $fp = $_FILES['csv_members']["tmp_name"];
} else {
    echo "ファイルが確認できません。";
    exit;
}
echo $fp . '<br>';
$csv = array_map('str_getcsv', file($fp));
echo '<br>';
$arrCols = $csv[0];
$sqlCols = implode(', ', $arrCols);
for ($i = 1; $i < count($csv); $i++) {
    $arrVals = $csv[$i];
    // $sqlVals = implode(', ', $arrVals);
    var_dump($arrVals);
    checkArrVals($dbh, $i, $arrVals);
    echo '<br>';
    // $sql = "INSERT INTO members ($sqlCols) VALUES (?,?,?,?,?,?,?,?,?,?,?);";
    $sql = "INSERT INTO members ($sqlCols) VALUES (?,?,?,?);"; // id_user, nickname, jp, cn
    // echo $sql . '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->execute($arrVals);
    if ($stmt->errorInfo()[2] !== NULL) {
        echo "エラーが発生しました。アップロードを中止し、終了します。<br>";
        var_dump($stmt->errorInfo());
        echo '<br>';
        exit;
    }
}


$dbh->commit();
