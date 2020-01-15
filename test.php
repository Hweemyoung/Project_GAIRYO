<?php
require_once './utils.php';

interface DBHandlerInterface
{
    public function process();
    public function redirect();
    public function executeSql();
}

class DBHandler implements DBHandlerInterface
{
    private $dbh;
    private $SQLS;
    private $url;

    public function __construct()
    {
    }

    public function process()
    {
        
    }

    public function redirect()
    {
    }

    public function executeSql()
    {
    }
}

 class asd extends DBHandler{
    public function process(){
        echo 'good';
    }
 }
 $ksl = new asd();
 $ksl->process();
?>