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

foreach (range(3, 24) as $id_user) {
    $columns = 'id_user, m';
    $m = '202002';
    $fields = "$id_user, '$m'";
    foreach ($arrayDates as $date) {
        if (randFloat() > .5) {
            continue;
        } else {
            foreach ($arrayShifts as $shift) {
                if (randFloat() > .5) {
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
