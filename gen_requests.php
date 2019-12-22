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
$end = new DateTime('2020-01-15');
$shifts = ['A', 'B', 'H', 'C', 'D'];
$num_shifts = 30;

$dbh = new PDO('mysql:host=localhost;dbname=gairyo', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
for ($i = 0; $i < $num_shifts; $i++) {
    $date = randomDateInRange($start, $end);
    $time_created = randomDateTimeInRange($start, new DateTime($date));
    echo $date;
    echo '<br>';
    echo $time_created;
    echo '<br>';

    $sql = 'INSERT INTO requests_pending (id_from, date_shift, shift, id_to, id_created, time_created, transaction_order) VALUES (:id_from, ' . $date . ', :shift, :id_to, :id_created, ' . $time_created . ', :transaction_order)';
    echo $sql;
    echo '<br>';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id_from', $id_from, PDO::PARAM_INT);
    $stmt->bindParam(':shift', $shift);
    $stmt->bindParam(':id_to', $id_to, PDO::PARAM_INT);
    $stmt->bindParam(':id_created', $id_created, PDO::PARAM_INT);
    $stmt->bindParam(':transaction_order', $transaction, PDO::PARAM_INT);
    // $stmt->bindParam(':time_created', $time_created);
    $id_from = mt_rand(1,5);
    $shift = $shifts[mt_rand(0, 4)];
    $id_to = mt_rand(1,5);
    $id_created = mt_rand(1,4);
    $transaction = mt_rand(1,15);
    $result = $stmt->execute();
    echo var_dump($stmt->errorInfo());
    echo '<br>';
}