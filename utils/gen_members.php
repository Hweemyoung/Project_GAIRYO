<?php
function randomDateInRange(DateTime $start, DateTime $end)
{
    $randomTimestamp = mt_rand($start->getTimestamp(), $end->getTimestamp());
    $randomDate = date('Ymd', $randomTimestamp);
    return $randomDate;
}

function generateRandomNumberString($maxlength = 21, $stopprop = 1.)
{
    $characters = '0123456789';
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

function generateRandomString($maxlength = 20, $stopprop = .5)
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

function generateTableMembers($arrayLanguages){
    global $dbh, $DBName;
    $sql = "CREATE TABLE `$DBName`.`members` ( `id_google` CHAR(21) NOT NULL , `id_user` SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT , `nickname` VARCHAR(30) NOT NULL , `first_name` VARCHAR(30) NOT NULL , `middle_name` VARCHAR(30) NULL DEFAULT NULL , `last_name` VARCHAR(30) NOT NULL , `date_sign_up` DATE NOT NULL";
    foreach($arrayLanguages as $lang){
        $sql = $sql . " , `$lang`" . ' BOOLEAN NOT NULL DEFAULT FALSE';
    }
    $sql = $sql . " , PRIMARY KEY (`id_user`)) ENGINE = MyISAM;";
    // echo $sql;
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    var_dump($stmt->errorInfo());
}

function generateRandomMembers($dbh, $num_members, $nationalities, $start, $end)
{
    for ($i = 0; $i < $num_members; $i++) {
        $date_sign_up = randomDateInRange($start, $end);

        $sql = 'INSERT INTO members (id_google, nickname, first_name, middle_name, last_name, nationality, date_sign_up, en, cn, kr, th, my, ru, fr, de) VALUES (:id_google, :nickname, :first_name, :middle_name, :last_name, :nationality, ' . $date_sign_up . ', :en, :cn, :kr, :th, :my, :ru, :fr, :de)';
        echo $sql;
        echo '<br>';
        $id_google = generateRandomNumberString();
        echo $id_google;
        echo '<br>';
        $nationality = $nationalities[mt_rand(0, count($nationalities) - 1)];
        echo $nationality;
        echo '<br>';
        $nickname = generateRandomString(20, .7);
        $first_name = generateRandomString(20, .7);
        echo $first_name;
        echo '<br>';
        $middle_name = generateRandomString(20, .7);
        $last_name = generateRandomString(20, .7);
        $en = strval(mt_rand(0, 1));
        echo $en;
        echo '<br>';
        $cn = strval(mt_rand(0, 1));
        $kr = strval(mt_rand(0, 1));
        $th = strval(mt_rand(0, 1));
        $my = strval(mt_rand(0, 1));
        $ru = strval(mt_rand(0, 1));
        $fr = strval(mt_rand(0, 1));
        $de = strval(mt_rand(0, 1));
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id_google', $id_google, PDO::PARAM_STR);
        $stmt->bindParam(':nickname', $nickname, PDO::PARAM_STR);
        $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $stmt->bindParam(':middle_name', $middle_name, PDO::PARAM_STR);
        $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $stmt->bindParam(':nationality', $nationality, PDO::PARAM_STR);
        $stmt->bindParam(':en', $en, PDO::PARAM_STR);
        $stmt->bindParam(':cn', $cn, PDO::PARAM_STR);
        $stmt->bindParam(':kr', $kr, PDO::PARAM_STR);
        $stmt->bindParam(':th', $th, PDO::PARAM_STR);
        $stmt->bindParam(':my', $my, PDO::PARAM_STR);
        $stmt->bindParam(':ru', $ru, PDO::PARAM_STR);
        $stmt->bindParam(':fr', $fr, PDO::PARAM_STR);
        $stmt->bindParam(':de', $de, PDO::PARAM_STR);


        $result = $stmt->execute();
        echo var_dump($stmt->errorInfo());
        echo '<br>';
        // return $result;
    }
}

$num_members = 10;
$start = new DateTime('2019-10-03');
$end = new DateTime();
$arrayLanguages = ['cn', 'tw', 'kr', 'th', 'my', 'ru', 'other'];

// $host = 'sql304.epizy.com';
// $DBName = 'epiz_24956964_gairyo';
// $userName = 'epiz_24956964';
// $pw = 'STZDGxr4iOPDhv';
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// generateRandomMembers($dbh, $num_members, $nationalities, $start, $end);
// for ($i = 0; $i < 11; $i++) {
//     $sql = 'UPDATE members SET nickname = :nickname WHERE id_user=:id_user';
//     $stmt = $dbh->prepare($sql);
//     $stmt->bindParam(':nickname', $nickname);
//     $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
//     $nickname = generateRandomString();
//     $id_user = $i+1;
//     $stmt->execute();
//     echo $stmt->errorInfo();
//     echo '<br>';
// }
generateTableMembers($arrayLanguages);