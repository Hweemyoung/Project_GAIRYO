<?php
$homedir = '/var/www/html/gairyo_temp';
class TempClass
{
    public $prop1;
    public $arr = ['a', 'b', 'c'];
}

// var_dump($_GET);
// var_dump(__FILE__);
// var_dump($_FILES);
echo strspn(' 12 asdv', '0123456789 ') . '<br>';
echo substr(' 12 asdv', strspn(' 12 asdv', '0123456789 ')) . '<br>';
echo intval(substr(' 12 asdv', 0, strspn(' 12 asdv', '0123456789 '))) . '<br>';


// $test = true;
// $host = 'localhost';
// $DBName = 'gairyo';
// $userName = 'root';
// $pw = '111111';
// $dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// $sql = "SHOW TABLE STATUS WHERE Name='shifts_assigned';";
// var_dump($dbh->query($sql)->fetchAll(PDO::FETCH_NUM)); // Engine = [0][1] = 'InnoDB';
?>