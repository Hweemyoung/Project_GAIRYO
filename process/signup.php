<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/config.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_master_handler.php";
$host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '111111';
$params = ['id_google' => '315977953185055105728'];

$master_handler = new MasterHandler(true, $host, $DBName, $userName, $pw, $params);

class SignupHandler extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->cols_required_members = $config_handler->cols_required_members;
        $this->checkCols();
        $this->process();
    }

    public function process()
    {
        $this->dbh->query('START TRANSACTION;');
        echo 'before:<br>';
        var_dump($_POST);
        $this->convertToNull();
        echo 'after:<br>';
        var_dump($_POST);
        $cols = implode(', ', array_keys($_POST));
        $values = "'". implode("', '", $_POST) . "'";
        $date_signup = date('Y-m-d');
        $sql = "INSERT INTO members (" . $cols . ", date_signup) VALUES (" . $values . ", '$date_signup');";
        $stmt = $this->querySql($sql);
        if (($stmt->errorInfo())[1] === NULL) {
            $this->redirect(true, '../admin.php', ['f' => 3, 's' => 0]);
        } else {
            var_dump($stmt->errorInfo());
            exit;
        };
    }

    private function convertToNull()
    {
        foreach (array_keys($_POST) as $col) {
            if ($_POST[$col] === '') {
                $_POST[$col] = 'NULL';
            }
        }
    }

    private function checkCols()
    {
        foreach (array_values($this->cols_required_members) as $col) {
            if (!in_array($col, array_keys($_POST))) {
                $this->redirect(false, '../admin.php', ['f' => 3, 'e' => 1, 'col' => $col]);
            }
        }
    }
}

$signup_handler = new SignupHandler($master_handler, $config_handler);
