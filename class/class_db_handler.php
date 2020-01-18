<?php
require_once '../utils.php';

class DBHandler
{
    public $dbh;
    public $url;
    public $sleepSeconds;
    public $SQLS = '';

    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->sleepSeconds = $config_handler->sleepSeconds;
    }

    public function process()
    {
    }

    public function redirect($commit, string $url, array $query)
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

    public function querySql($sql)
    {
        echo $sql;
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
                $arrayFieldValues[$i] = $colName . '=' . $arrayFieldValues[$i];
            }
        }
        return '(' . implode(" $condition ", $arrayFieldValues) . ')';
    }
}
