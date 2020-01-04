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

function generateRandomString($maxlength = 30, $stopprop = .9)
{
    $characters = 'abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $maxlength; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
        if (mt_rand(0, mt_getrandmax() - 1) / mt_getrandmax() > $stopprop) {
            break;
        }
    }
    return $randomString;
}

// hparams
$start = new Datetime('2019-12-12');
$end = new DateTime('2020-01-20');
$arrayIdUser = range(1, 24);
$num_items = 30;

$dbh = new PDO('mysql:host=localhost;dbname=gairyo', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// for ($i = 0; $i < $num_items; $i++) {
//     $date_created = randomDateInRange($start, $end);
//     $id_user = mt_rand(1, 24);
//     $title = generateRandomString();
//     $content = generateRandomString();
//     $sql = strtr('INSERT INTO board (id_user, title, content, date_created) VALUES (:id_user, :title, :content, $date_created)', array('$date_created'=>$date_created));
//     $stmt = $dbh->prepare($sql);
//     $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
//     $stmt->bindParam(':title', $title);
//     $stmt->bindParam(':content', $content);
//     $stmt->execute();
//     var_dump($stmt->errorInfo());
// }

// Add column checked_id_user
// $num_members = 24;
// for ($i = 1; $i < $num_members+1; $i++) {
    // $sql = 'ALTER TABLE board ADD checked_:id_user BOOLEAN NOT NULL DEFAULT FALSE AFTER date_created';
    // $stmt = $dbh->prepare($sql);
    // $stmt->bindParam(':id_user', $i, PDO::PARAM_INT);
    // $stmt->execute();
    // var_dump($stmt->errorInfo());
// }
