<?php
require_once '../utils.php';

class DBHandler
{
    private $dbh;
    private $SQLS;
    private $url;
    private $sleepSeconds;

    public function __construct($master_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->SQLS = '';
    }

    private function process(){
    }

    private function redirect($commit, string $url, array $query)
    {
        if ($commit) {
            $this->dbh->query('COMMIT;');
        } else {
            $this->dbh->query('ROLLBACK;');
        }
        $this->dbh = NULL;
        $url = utils\genHref($url, $query);
        header('Location: ' . $url);
    }

    private function executeSql($sql)
    {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        if (($stmt->errorInfo())[1] === '1213' || ($stmt->errorInfo())[1] === '1205') {
            // Error number: 1213; Symbol: ER_LOCK_DEADLOCK; SQLSTATE: 40001
            // Error number: 1205; Symbol: ER_LOCK_WAIT_TIMEOUT; SQLSTATE: HY000
            // Already rolled back, automatically.
            // Wait awhile and restart process.
            sleep($this->sleepSeconds);
            $this->process();
            exit;
        } else {
            return $stmt;
        }
    }
}