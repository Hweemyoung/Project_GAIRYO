<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";

class MarketPurchaseProcessor extends DBHandler
{
    public function __construct($master_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->handleRequest();
        $this->setGets();
    }

    private function handleRequest()
    {
        // $mode = $_POST['mode'];
        // $id_request = $_POST['id_request'];
        $mode = $_GET['mode'];
        $id_request = $_GET['id_request'];
        if ($mode === 'put') {
            $sql = "SELECT id_transaction FROM requests_pending WHERE id_request=$id_request AND `status`=2 AND id_to IS NULL;";
            echo $sql . '<br>';
            $stmt = $this->querySql($sql);
            $result = $stmt->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_NUM);
            $stmt->closeCursor();
            if (!count($result)) {
                echo 'No valid put request found. Exit!';
                exit;
            }
            $this->id_transaction = $result[0];
            $sql = "UPDATE requests_pending SET id_to=$this->id_user WHERE id_request=$id_request;";
            echo $sql . '<br>';
            $stmt = $this->executeSql($sql);
        }
    }

    private function setGets()
    {
        // $_GET["mode"], $_GET["id_user"], $_GET["id_transaction"]
        $_GET['mode'] = 'agree';
        $_GET["id_user"] = $this->id_user;
        $_GET["id_transaction"] = $this->id_transaction;
    }
}

$market_purchase_processor = new MarketPurchaseProcessor($master_handler, $config_handler);
// Still in transaction.
// var_dump($master_handler->dbh->inTransaction());
var_dump($_GET);

// Then, load register_agree.php
require_once "$homedir/process/register_agree.php";
