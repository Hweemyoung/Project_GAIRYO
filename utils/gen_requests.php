<?php
function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = date('Ymd', $randomTimestamp);
    return $randomDate;
}

function randomDateTimeInRange(DateTime $start, DateTime $end){
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDateTime = new DateTime();
    $randomDateTime->setTimestamp($randomTimestamp);
    return $randomDateTime->format('YmdHis');
}

$start = new DateTime();
$end = new DateTime('2020-02-15');
$shifts = ['A', 'B', 'H', 'C', 'D'];
$num_shifts = 30;

// $host = 'sql304.epizy.com';
// $DBName = 'epiz_24956964_gairyo';
// $userName = 'epiz_24956964';
// $pw = 'STZDGxr4iOPDhv';
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
for ($i = 0; $i < $num_shifts; $i++) {
    $date = randomDateInRange($start, $end);
    $time_created = randomDateTimeInRange($start, new DateTime($date));
    echo $date;
    echo '<br>';
    echo $time_created;
    echo '<br>';

    $sql = 'INSERT INTO requests_pending (id_shift, id_from, id_to, id_created, time_created, time_proceeded, id_transaction) VALUES (:id_shift, :id_from, :id_to, :id_created, ' . $time_created . ', ' . $time_created . ', :id_transaction)';
    echo $sql;
    echo '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id_shift', $id_shift, PDO::PARAM_INT);
    $stmt->bindParam(':id_from', $id_from, PDO::PARAM_INT);
    $stmt->bindParam(':id_to', $id_to, PDO::PARAM_INT);
    $stmt->bindParam(':id_created', $id_created, PDO::PARAM_INT);
    $stmt->bindParam(':id_transaction', $transaction, PDO::PARAM_INT);
    $id_shift = mt_rand(1, 100);
    $id_from = mt_rand(1,20);
    $id_to = mt_rand(1,20);
    $id_created = mt_rand(1,4);
    $transaction = mt_rand(1,15);
    $result = $stmt->execute();
    echo var_dump($stmt->errorInfo());
    echo '<br>';
}