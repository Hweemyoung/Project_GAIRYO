<?php
class TempClass{
    public $prop1;
}
$homedir = '/var/www/html/gairyo_temp';
$arr = [NULL => 2];
var_dump($arr[NULL]);

// $test = true;
// $host = 'localhost';
// $DBName = 'gairyo';
// $userName = 'root';
// $pw = '111111';
// $dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// $sql = "SHOW TABLE STATUS WHERE Name='shifts_assigned';";
// var_dump($dbh->query($sql)->fetchAll(PDO::FETCH_NUM)); // Engine = [0][1] = 'InnoDB';


