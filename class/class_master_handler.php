<?php
// ini_set('include_path', '/var/www/html/gairyo_temp');
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_member_object.php";
require_once "$homedir/config.php";

class MasterHandler
{
    public $test;
    public $homedir;
    public $dbh;
    public $signedin;
    public $id_google;
    public $arrayMemberObjectsByIdUser;
    // public $arrayMemberObjectsByIdGoogle;
    public function __construct($test, string $host, string $DBName, string $userName, string $pw, ConfigHandler $config_handler, array $params)
    {
        $this->dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
        $this->test = $test;
        $this->config_handler = $config_handler;
        $this->homedir = $config_handler->homedir;
        foreach ($params as $prop => $val) {
            $this->$prop = $val;
        }
        if ($this->test) {
            // id_user, id_google must be given via array params
            $this->signedin = true;
        } elseif (isset($_POST['id_google'])) {
            // Newly signed in
            $this->newSignin($_POST['id_google']);
        } else {
            $this->checkSession();
        }
        if ($this->signedin) {
            $this->setArrayMemberObjects();
        }
        $this->unsetPrivates();
    }

    private function unsetPrivates(){
    }

    private function newSignin($id_google)
    {
        $sql = "SELECT id_user FROM members WHERE `status`=1 AND id_google=$id_google LIMIT 1;";
        $stmt = $this->dbh->query($sql);
        $exists = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $stmt->closeCursor();
        if (count($exists)) {
            session_save_path("$this->homedir/sess");
            session_name('sess_gairyo');
            session_start();
            $_SESSION['id_google'] = $id_google;
            $_SESSION['id_user'] = $exists[0];
            $_SESSION['signedin'] = true;
        } else {
            // Redirect to signup
            // HERE!
        }
    }

    private function checkSession()
    {
        $this->signedin = false;
        if (isset($_COOKIE['sess_gairyo'])) {
            // if ($this->sessIdInTable($_COOKIE['sess_gairyo'])) {
            if (file_exists($this->homedir . '/sess/sess_' . $_COOKIE['sess_gairyo'])) {
                session_save_path("$this->homedir/sess");
                session_name('sess_gairyo');
                session_start();
                if ($_SESSION['signedin']) {
                    $this->signedin = true;
                    $this->id_google = $_SESSION['id_google'];
                    $this->id_user = $_SESSION['id_user'];
                }
            }
        }
    }

    private function setArrayMemberObjects()
    {
        $stringLangs = implode(', ', $this->config_handler->arrayLangsShort);
        $sql = "SELECT id_user, id_user, nickname, $stringLangs FROM members_new;";
        $this->arrayMemberObjectsByIdUser = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'MemberObject');
        // $sql = 'SELECT id_google, members.* FROM members WHERE `status` = 1';
        // $this->arrayMemberObjectsByIdGoogle = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'MemberObject');
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
