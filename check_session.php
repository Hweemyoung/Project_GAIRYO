<?php
$homepath = '/var/www/html/gairyo_temp';
require_once "$homepath/config.php";
require_once "$homepath/class/class_master_handler.php";
require_once "$homepath/class/class_date_object.php";

// PDO Object
// $host = 'sql304.epizy.com';
// $DBName = 'epiz_24956964_gairyo';
// $userName = 'epiz_24956964';
// $pw = 'STZDGxr4iOPDhv';

$test = true;
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$params = ['id_user' => '4'];
if (isset($_GET['pseudo_user'])){
    $params = [];
$master_handler = new MasterHandler($test, $host, $DBName, $userName, $pw, $config_handler, $params);
$dbh = $master_handler->dbh;
$signedin = $master_handler->signedin;
$id_google = $master_handler->id_google;
// $arrayMemberObjectsByIdGoogle = $master_handler->arrayMemberObjectsByIdGoogle;
$arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
$id_user = $master_handler->id_user;


// function check_signedin()
// {
//     // POST is when someone signs in.
//     if (!isset($_SESSION['id_google'])) {
//         // No id_google variable in sess
//         if (isset($_POST['id_google'])) {
//             $_SESSION['id_google'] = $_POST['id_google'];
//             return true;
//         } else {
//             return false;
//         }
//     } else {
//         // id_google exists in sess
//         if (isset($_POST['id_google'])) {
//             // and someone signs in
//             if ($_SESSION['id_google'] === $_POST['id_google']) {
//                 return true;
//             } else {
//                 // Sign in ID is different. Something has gone wrong. Change id_google in sess.
//                 $_SESSION['id_google'] = $_POST['id_google'];
//                 return true;
//             }
//         } else {
//             // Nobody newly signs in but session has id_google
//             return true;
//         }
//     }
// }

// function getIdGoogle($signedin)
// {
//     if ($signedin) {
//         return $_SESSION['id_google'];
//     } else {
//         return false;
//     }
// }

// function getArrayMembersByIdGoogle($signedin)
// {
//     if ($signedin) {
//         global $dbh;
//         // Select all valid ids
//         $sql = 'SELECT id_google, members.* FROM members WHERE `status` = 1';
//         $stmt = $dbh->prepare($sql);
//         $stmt->execute();
//         $arrayMembersByIdGoogle = $stmt->fetchAll(PDO::FETCH_UNIQUE);
//         // $arrayMembersByIdGoogle = array('id_google'=>array('column', ...))
//         return $arrayMembersByIdGoogle;
//     } else {
//         return false;
//     }
// }

// function getArrayMembersByIdUser($signedin)
// {
//     if ($signedin) {
//         global $dbh;
//         // Select all valid ids
//         $sql = 'SELECT id_user, members.* FROM members WHERE `status` = 1';
//         $stmt = $dbh->prepare($sql);
//         $stmt->execute();
//         $arrayMembersByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE);
//         // $arrayMembersByIdUser = array('id_user'=>array('column', ...))
//         return $arrayMembersByIdUser;
//     } else {
//         return false;
//     }
// }

// function getIdUser($id_google, $arrayMembersByIdGoogle)
// {
//     if (!array_key_exists($id_google, $arrayMembersByIdGoogle)) {
//         // Something is really wrong...
//         return false;
//     } else {
//         $id_user = $arrayMembersByIdGoogle[$id_google]["id_user"];
//         return $id_user;
//     }
// }

// $dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// $signedin = check_signedin();
// $signedin = true;
// $id_google = getIdGoogle($signedin);
// $id_google = '315977953185055105728';
// Get nicknames of whole members
// $arrayMembersByIdGoogle = getArrayMembersByIdGoogle($signedin);
// $arrayMembersByIdGoogle = array('id_google'=>array('column', ...))
// var_dump($arrayMembersByIdGoogle); OK
// $arrayMembersByIdUser = getArrayMembersByIdUser($signedin);
// var_dump($arrayMembersByIdUser);
// $id_user = getIdUser($id_google, $arrayMembersByIdGoogle);
// var_dump($id_user); OK

// class Overloading
// {
//     private $_arrayProps = array();

//     public function __set($_prop, $value)
//     {
//         $this->_arrayProps[$_prop] = $value;
//     }

//     public function __get($_prop)
//     {
//         if (array_key_exists($_prop, $this->_arrayProps)) {
//             return $this->_arrayProps[$_prop];
//         }
//         $trace = debug_backtrace();
//         trigger_error(
//             'Undefined property via __get(): ' . $_prop .
//                 ' in ' . $trace[0]['file'] .
//                 ' on line ' . $trace[0]['line'],
//             E_USER_NOTICE
//         );
//         return null;
//     }

//     public function __isset($_prop)
//     {
//         return isset($this->_arrayProps[$_prop]);
//     }

//     public function __unset($_prop)
//     {
//         unset($this->_arrayProps[$_prop]);
//     }

//     public function set_all($_array)
//     {
//         foreach (array_keys($_array) as $_prop) {
//             $this->_arrayProps[$_prop] = $_array[$_prop];
//         }
//     }
// }