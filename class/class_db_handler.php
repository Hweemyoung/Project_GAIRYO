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
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
    }

    public function process()
    {
    }

    public function lockTablesIfNotInnoDB(array $arrTableName)
    {
        $sqlConditions = $this->genSqlConditions($arrTableName, 'Name', 'OR');
        $sql = "SHOW TABLE STATUS WHERE $sqlConditions;";
        echo $sql . '<br>';
        $stmt = $this->dbh->query($sql);
        $this->arrTableStatus = $stmt->fetchAll(PDO::FETCH_CLASS);
        $stmt->closeCursor();
        $sqlConditions = '';
        $arrTableNamesLocked = [];
        if (count($this->arrTableStatus)) {
            foreach ($this->arrTableStatus as $tableStatus) {
                if ($tableStatus->Engine !== 'InnoDB') {
                    $sqlConditions = $sqlConditions . "$tableStatus->Name WRITE ";
                    $arrTableNamesLocked[] = $tableStatus->Name;
                }
            }
            $sql = "LOCK TABLE $sqlConditions;";
            echo 'Locking tables: ' . $sql . '<br>';
            $this->executeSql($sql);
            return $arrTableNamesLocked;
        }
    }

    public function redirect($commit, string $url, array $query)
    {
        if ($commit) {
            $this->dbh->commit();
        } else {
            $this->dbh->rollBack();
        }
        $this->executeSql('UNLOCK TABLES;');
        $this->dbh = NULL;
        $url = utils\genHref($this->http_host, $url, $this->master_handler->arrPseudoUser + $query);
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
        return $stmt;
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
            if (in_array($colName, ['date_shift', 'shift', 'Name'])) {
                // for ($i = 0; $i < count($arrayFieldValues); $i++) {
                foreach ($arrayFieldValues as $key => $val) {
                    $arrayFieldValues[$key] = $colName . '="' . $val . '"';
                }
            } else {
                foreach ($arrayFieldValues as $key => $val) {
                    $arrayFieldValues[$key] = $colName . '=' . $val;
                }
            }
        }
        return '(' . implode(" $condition ", $arrayFieldValues) . ')';
    }

    public function beginTransactionIfNotIn()
    {
        if (!$this->dbh->inTransaction()) {
            $this->dbh->beginTransaction();
            // echo 'Starting transaction!';
            // var_dump($this->dbh->inTransaction());
        }
    }
}
