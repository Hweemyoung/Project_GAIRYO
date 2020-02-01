<?php
$arrayShifts = array('O', 'A', 'B', 'H', 'C', 'D');
$arrayDates = range(1, 31);

// $host = 'sql304.epizy.com';
// $DBName = 'epiz_24956964_gairyo';
// $userName = 'epiz_24956964';
// $pw = 'STZDGxr4iOPDhv';
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
function randFloat()
{
    return mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax();
}
$sql = "SELECT id_user FROM members WHERE id_user<>0 AND `status`=1;";
$stmt = $dbh->query($sql);
$arrIdUsers = $stmt->fetchAll(PDO::FETCH_COLUMN);
$stmt->closeCursor();
foreach ($arrIdUsers as $id_user) {
    $columns = 'id_user, m';
    $m = '202002';
    $fields = "$id_user, '$m'";
    foreach ($arrayDates as $date) {
        if (randFloat() > .72) {
            continue;
        } else {
            foreach ($arrayShifts as $shift) {
                if (randFloat() > .6) {
                    $column = strval($date) . $shift;
                    $columns = $columns . ',' . $column;
                    $fields = $fields . ',' . '1';
                    if ($shift === 'O') {
                        break;
                    }
                }
            }
        }
    }
    $sql = strtr('INSERT INTO shifts_submitted ($columns) VALUES ($fields)', array('$columns' => $columns, '$fields' => $fields));
    echo $sql . '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    var_dump($stmt->errorInfo());
}