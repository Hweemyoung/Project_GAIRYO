<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";

class RequestsHandler extends DBHandler
{
    private $mode;
    private $idUser;
    private $idTrans;
    private $timeProceeded;
    private $arrayRequestsInTransaction;
    public function __construct($mode, $idUser, $idTrans, $master_handler, $config_handler)
    {
        $this->mode = $mode;
        $this->idUser = $idUser;
        $this->idTrans = $idTrans;
        $this->dbh = $master_handler->dbh;
        $this->url = 'transactions.php';
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->http_host = $config_handler->http_host;
        $this->timeProceeded = date('Y-m-d H:i:s');
        $this->positions = ['from', 'to'];
        $this->SQLS = '';
        $this->arrayErrors = [];
        $this->validateUser($master_handler->id_user);
        $this->process();
    }

    public function validateUser($id_user)
    {
        if ($this->idUser !== $id_user) {
            // echo 'Error - No permission';
            $this->redirect(false, $this->url, array('f' => 1, 'e' => 0));
        }
    }

    public function validateAction()
    {
        $sql = strtr('SELECT `status`, id_request, id_from, id_to, id_shift FROM requests_pending WHERE id_transaction=$idTrans FOR UPDATE;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $this->arrayRequestsInTransaction = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
        if (in_array('0', array_keys($this->arrayRequestsInTransaction)) || in_array('1', array_keys($this->arrayRequestsInTransaction))) {
            // echo "Fatal Error - Some requests had already been closed:<br>";
            // var_dump($results);OK
            $this->redirect(false, $this->url, array('f' => 1, 'e' => 1));
        }
        return true;
    }

    private function decline($sqlConditions)
    {
        // var_dump($sqlConditions);
        if ($sqlConditions === '(0)') {
            echo 'here';
            exit;
        }
        $sql = "UPDATE requests_pending SET `status`=0, time_proceeded='$this->timeProceeded' WHERE `status`=2 AND " . $sqlConditions . ';';
        $this->SQLS = ($this->SQLS) . $sql;
        // For every shift in declined transaction, check if there is any other requests surrounding it and update under_request.
        $this->updateUnderRequest($sqlConditions);
    }

    private function invalidateAllRequests()
    {
        // Select pending id_transactions surrounding the shifts
        $arrayIdShifts = [];
        foreach($this->arrayRequestsInTransaction['2'] as $request){
            array_push($arrayIdShifts, $request['id_shift']);
        }
        $sqlConditions = $this->genSqlConditions($arrayIdShifts, 'id_shift', 'OR');
        echo $sqlConditions.'<br>';
        $sql = 'SELECT id_transaction FROM requests_pending WHERE `status`=2 AND ' . $sqlConditions . 'FOR UPDATE;';
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $arrayByIdTrans = $stmt->fetchAll(PDO::FETCH_GROUP);
        var_dump($arrayByIdTrans);
        // Invalidate transactions
        $sqlConditions = $this->genSqlConditions(array_keys($arrayByIdTrans), 'id_transaction', 'OR');
        echo $sqlConditions.'<br>';
        $this->decline($sqlConditions);
    }

    private function updateUnderRequest($sqlConditions)
    {
        // For every shift in invalidated transactions, check if there is any other requests surrounding it and update under_request.
        $sql = 'SELECT id_shift FROM requests_pending WHERE ' . $sqlConditions;
        // Lock
        $this->querySql('SELECT id_shift FROM shifts_assigned WHERE id_shift in (' . $sql . ') FOR UPDATE;');
        // Get id_shifts
        $arrayByIdShift = $this->querySql($sql)->fetchAll(PDO::FETCH_GROUP);
        var_dump($arrayByIdShift);
        $sqlConditions = [];
        foreach (array_keys($arrayByIdShift) as $idShift) {
            $sql = "SELECT EXISTS (SELECT 1 FROM requests_pending WHERE`status`=2 AND id_shift=$idShift LIMIT 1);";
            $exists = $this->querySql($sql)->fetch();
            if (!$exists) {
                array_push($sqlConditions, 'id_shift=' . $idShift);
            }
        }
        if (count($sqlConditions)) {
            $sql = "UPDATE shifts_assigned SET under_request=0 WHERE " . '(' . implode(' OR ', $sqlConditions) . ');';
            $this->SQLS = ($this->SQLS) . $sql;
        }
    }

    private function agree()
    {
        foreach ($this->positions as $position) {
            $sql = strtr('SELECT id_request, agreed_$position FROM requests_pending WHERE id_transaction=$idTrans AND id_$position=$idUser FOR UPDATE;', array('$idTrans' => $this->idTrans, '$idUser' => $this->idUser, '$position' => $position));
            $arrayIdRequests = $this->querySql($sql)->fetchAll(PDO::FETCH_ASSOC);
            foreach ($arrayIdRequests as $arrayIdRequest) {
                $idRequest = $arrayIdRequest["id_request"];
                if ($arrayIdRequest["agreed_$position"] == 1) {
                    array_push($this->arrayErrors, $idRequest);
                } else {
                    $sql = "UPDATE requests_pending SET agreed_$position=1, time_proceeded='$this->timeProceeded' WHERE id_request=$idRequest;";
                    $this->SQLS = ($this->SQLS) . $sql;
                }
            }
        }
        if (count($this->arrayErrors)) {
            // echo "Fatal Error - The request had already been agreed with by the user.<br>Request ID:";
            // exit;
            $this->redirect(false, $this->url, array('f' => 1, 'e' => 2));
        }
    }

    public function execute()
    {
        $this->validateAction();
        if ($this->mode === 'decline') {
            echo $this->idTrans;
            // $this->decline(['id_transaction=' . $this->idTrans]);
            $this->decline("(id_transaction=$this->idTrans)");
        } else if ($this->mode === 'agree') {
            $this->agree();
        } else {
            // echo "Error - mode NOT understood<br>mode:";
            $this->redirect(false, $this->url, ['f' => 1, 'e' => 3]);
        }
        echo $this->SQLS;
        $stmt = $this->querySql($this->SQLS);
        if (($stmt->errorInfo())[2] !== NULL) {
            echo ($stmt->errorInfo())[2];
            exit;
        }
        $stmt->closeCursor();
        if ($this->mode === 'decline') {
            // exit;
            $this->redirect(true, $this->url, ['f' => 1, 's' => 2]);
        }
        $this->SQLS = '';
    }

    public function executeTransaction()
    {
        // Lock shifts_assigned
        $sql = 'SELECT id_shift FROM shifts_assigned WHERE under_request=1 FOR UPDATE;';
        $this->querySql($sql);

        $sql = strtr('SELECT COUNT(*) FROM requests_pending WHERE id_transaction=$idTrans FOR UPDATE;', array('$idTrans' => $this->idTrans));
        $stmt = $this->querySql($sql);
        $sql = strtr('SELECT id_shift, id_to FROM requests_pending WHERE id_transaction=$idTrans AND agreed_from=1 AND agreed_to=1 FOR UPDATE;', array('$idTrans' => $this->idTrans));
        $arrayRequests = $this->querySql($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($arrayRequests) === intval($stmt->fetchAll(PDO::FETCH_COLUMN)[0])) { // intval('2')
            // Execute
            // Firstly, invalidate all pending(i.e. status=2) transactions surrounding these shifts
            $this->invalidateAllRequests();
            // Next, update status of requests
            $sql = "UPDATE requests_pending SET `status`=1, time_proceeded='$this->timeProceeded' WHERE id_transaction=$this->idTrans;";
            $this->SQLS = $this->SQLS . $sql;
            // Next, update shifts assigned
            foreach ($arrayRequests as $arrayRequest) {
                // Execute every request: update id_user and under_request=0
                $sql = strtr(
                    'UPDATE shifts_assigned SET id_user=$idUser, under_request=0 WHERE id_shift=$idShift;',
                    array('$idUser' => $arrayRequest["id_to"], '$idShift' => $arrayRequest["id_shift"])
                );
                echo $sql . '<br>';
                $this->SQLS = ($this->SQLS) . $sql;
            }
            echo $this->SQLS;
            $stmt = $this->querySql($this->SQLS);
            if (($stmt->errorInfo())[2] === NULL) {
                $this->redirect(true, $this->url, ['f' => 1, 's' => 1]);
            } else {
                var_dump($stmt->errorInfo());
                exit;
            }
        } else {
            // echo "Awaiting agreements from other members.";
            $this->redirect(true, $this->url, ['f' => 1, 's' => 0]);
        }
    }

    public function process()
    {
        $this->dbh->query('START TRANSACTION;');
        $this->execute();
        $this->executeTransaction();
    }
}
$handler = new RequestsHandler($_GET["mode"], $_GET["id_user"], $_GET["id_transaction"], $master_handler, $config_handler);
// var_dump($handler);
