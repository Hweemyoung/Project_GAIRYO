<?php
// ini_set('include_path', '/var/www/html/gairyo_temp');
require_once 'class_member_object.php';
class MasterHandler
{
    public $test;
    public $dbh;
    public $signedin;
    public $id_google;
    public $arrayMemberObjectsByIdUser;
    public $arrayMemberObjectsByIdGoogle;
    public function __construct($test, $host, $DBName, $userName, $pw, array $params)
    {
        $this->dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->test = $test;
        foreach(array_keys($params) as $prop){
            $this->$prop = $params[$prop];
        }
        $this->init();
    }
    
    private function init(){
        $this->setArrayMemberObjects();
        if ($this->test){
            $this->signedin = true;
        } else {
            $this->set_signedin();
            $this->set_id_google();
        }
        $this->setIdUser();
    }

    private function setArrayMemberObjects()
    {
        $sql = 'SELECT id_user, members.* FROM members WHERE `status` = 1';
        $this->arrayMemberObjectsByIdUser = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'MemberObject');
        $sql = 'SELECT id_google, members.* FROM members WHERE `status` = 1';
        $this->arrayMemberObjectsByIdGoogle = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'MemberObject');
    }
    private function set_signedin()
    {
        // POST is when someone signs in.
        if (!isset($_SESSION['id_google'])) {
            // No id_google variable in sess
            if (isset($_POST['id_google'])) {
                $_SESSION['id_google'] = $_POST['id_google'];
                $this->signedin = true;
            } else {
                $this->signedin = false;
            }
        } else {
            // id_google exists in sess
            if (isset($_POST['id_google'])) {
                // and someone signs in
                if ($_SESSION['id_google'] === $_POST['id_google']) {
                    $this->signedin = true;
                } else {
                    // Sign in ID is different. Something has gone wrong. Change id_google in sess.
                    $_SESSION['id_google'] = $_POST['id_google'];
                    $this->signedin = true;
                }
            } else {
                // Nobody newly signs in but session has id_google
                $this->signedin = true;
            }
        }
    }

    private function set_id_google()
    {
        if ($this->signedin) {
            return $_SESSION['id_google'];
        } else {
            return false;
        }
    }

    private function setIdUser()
    {
        if (!array_key_exists($this->id_google, $this->arrayMemberObjectsByIdGoogle)) {
            echo 'Something is really wrong...';
            exit;
        } else {
            $this->id_user = $this->arrayMemberObjectsByIdGoogle[$this->id_google]->id_user;
        }
    }
}
