<?php
function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = date('Ymd', $randomTimestamp);
    return $randomDate;
}

function randomDateTimeInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDateTime = new DateTime();
    $randomDateTime->setTimestamp($randomTimestamp);
    return $randomDateTime->format('YmdHis');
}

$arrayNumShifts = array('A' => 1, 'B' => 3, 'H' => 2, 'C' => 2, 'D' => 3);

$dateTimeStart = new Datetime('2020-01-12');
$arrayIdUser = range(1, 24);
$days = 10;

$timeNow = $dateTimeStart->getTimestamp();
// $host = 'sql304.epizy.com';
// $DBName = 'epiz_24956964_gairyo';
// $userName = 'epiz_24956964';
// $pw = 'STZDGxr4iOPDhv';
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$dbh = new PDO(strtr('mysql:host=	$host;dbname=$DBName', '$userName', '$pw', array('$host'=>$host, '$DBName'=>$DBName, '$userName'=>$userName, '$pw'=>$pw)), array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
for ($d = 0; $d < $days; $d++) {
    // Everyday
    // Copy array
    $date = date('Ymd', $timeNow);
    echo '$date = ' . $date;
    $copyArrayIdUser = $arrayIdUser;
    shuffle($copyArrayIdUser);
    foreach (array_keys($arrayNumShifts) as $shift) {
        $idUsers = array_slice($copyArrayIdUser, 0, $arrayNumShifts[$shift]);
        for ($i = 0; $i < $arrayNumShifts[$shift]; $i++) {
            array_shift($copyArrayIdUser);
        }
        foreach ($idUsers as $id_user) {
            $sql = 'INSERT INTO shifts_assigned (id_user, date_shift, shift) VALUES (:id_user, ' . $date . ', :shift)';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
            $stmt->bindParam(':shift', $shift);
            // $stmt->bindParam(':time_created', $time_created);
            $result = $stmt->execute();
            echo var_dump($stmt->errorInfo());
            echo '<br>';
        }
    }
    $timeNow = strtotime('+1 day', $timeNow);
}
