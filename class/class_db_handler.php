<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/config.php";

class DBHandler
{
    public $dbh;
    public $url;
    public $sleepSeconds;
    public $SQLS = '';

    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
    }

    public function process()
    {
    }

    public function redirect($commit, string $url, array $query)
    {
        if ($commit) {
            $this->dbh->exec('COMMIT;');
        } else {
            $this->dbh->exec('ROLLBACK;');
        }
        $this->dbh = NULL;
        $url = utils\genHref($this->http_host, $url, $query);
        echo $url;
        header('Location: ' . $url);
    }

    public function querySql($sql)
    {
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        return $this->restartIfErLock($stmt);
    }

    public function executeSql($sql)
    {
        $stmt = $this->dbh->exec($sql);
        return $this->restartIfErLock($stmt);
    }

    public function restartIfErLock($stmt)
    {
        if (($stmt->errorInfo())[1] === '1213' || ($stmt->errorInfo())[1] === '1205') {
            // Error number: 1213; Symbol: ER_LOCK_DEADLOCK; SQLSTATE: 40001
            // Error number: 1205; Symbol: ER_LOCK_WAIT_TIMEOUT; SQLSTATE: HY000
            // Already rolled back, automatically.
            // Wait awhile and restart process.
            sleep($this->sleepSeconds);
            $this->process();
            exit;
        } elseif (($stmt->errorInfo())[1] !== NULL) {
            var_dump($stmt->errorInfo());
            exit;
        } else {
            return $stmt;
        }
    }

    public function genSqlConditions($arrayFieldValues, $colName, $condition)
    {
        if (count($arrayFieldValues) === 0) {
            $arrayFieldValues = [0];
        } else {
            for ($i = 0; $i < count($arrayFieldValues); $i++) {
                var_dump($arrayFieldValues[$i]);
                $arrayFieldValues[$i] = $colName . '=' . $arrayFieldValues[$i];
            }
        }
        return '(' . implode(" $condition ", $arrayFieldValues) . ')';
    }
}
