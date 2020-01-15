<?php
require './check_session.php';
require './utils.php';
class RequestsHandler
{
    private $mode;
    private $idUser;
    private $idTrans;
    private $dbh;
    private $arrayRequestsInTransaction;
    private $url = './transactions.php';
    public function __construct($mode, $idUser, $idTrans, $master_handler)
    {
        $this->mode = $mode;
        $this->idUser = $idUser;
        $this->idTrans = $idTrans;
        $this->dbh = $master_handler->dbh;
        $this->positions = ['from', 'to'];
        $this->SQLS = '';
        $this->arrayErrors = [];
        $this->process($master_handler->id_user);
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

    private function decline($arrayCondsIdTrans)
    {
        $sql = 'UPDATE requests_pending SET `status`=0 WHERE `status`=2 AND ' . '(' . implode(' OR ', $arrayCondsIdTrans) . ');';
        $this->SQLS = ($this->SQLS) . $sql;
        // For every shift in declined transaction, check if there is any other requests surrounding it and update under_request.
        $this->updateUnderRequest($arrayCondsIdTrans);
    }

    private function invalidateAllRequests()
    {
        // Select pending id_transactions surrounding the shifts
        $sqlConditions = [];
        foreach ($this->arrayRequestsInTransaction['2'] as $request) {
            array_push($sqlConditions, 'id_shift=' . $request['id_shift']);
        }
        $sql = 'SELECT id_transaction FROM requests_pending WHERE `status`=2 AND ' . '(' . implode(' OR ', $sqlConditions) . ') FOR UPDATE;';
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $arrayByIdTrans = $stmt->fetchAll(PDO::FETCH_GROUP);
        // Invalidate transactions
        $sqlConditions = [];
        foreach (array_keys($arrayByIdTrans) as $idTrans) {
            array_push($sqlConditions, 'id_transaction=' . $idTrans);
        }
        $this->decline($sqlConditions);
    }

    private function updateUnderRequest($arrayCondsIdTrans)
    {
        // For every shift in invalidated transactions, check if there is any other requests surrounding it and update under_request.
        $sql = 'SELECT id_shift FROM requests_pending WHERE ' . '(' . implode(' OR ', $arrayCondsIdTrans) . ')';
        // Lock
        $this->dbh->query('SELECT id_shift FROM shifts_assigned WHERE id_shift in (' . $sql . ') FOR UPDATE;');
        // Get id_shifts
        $arrayByIdShift = $this->dbh->query($sql)->fetchAll(PDO::FETCH_GROUP);
        $sqlConditions = [];
        foreach (array_keys($arrayByIdShift) as $idShift) {
            $sql = "SELECT COUNT(*) FROM requests_pending WHERE `status`=2 AND id_shift=$idShift;";
            $arrayCount = $this->dbh->query($sql)->fetchAll(PDO::FETCH_COLUMN);
            if (intval($arrayCount[0]) === 0) {
                array_push($sqlConditions, 'id_shift=' . $idShift);
            }
        }
        $sql = "UPDATE shifts_assigned SET under_request=0 WHERE " . '(' . implode(' OR ', $sqlConditions) . ');';
        $this->SQLS = ($this->SQLS) . $sql;
    }

    private function agree()
    {
        foreach ($this->positions as $position) {
            $sql = strtr('SELECT id_request, agreed_$position FROM requests_pending WHERE id_transaction=$idTrans AND id_$position=$idUser FOR UPDATE;', array('$idTrans' => $this->idTrans, '$idUser' => $this->idUser, '$position' => $position));
            $arrayIdRequests = $this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
            foreach ($arrayIdRequests as $arrayIdRequest) {
                $idRequest = $arrayIdRequest["id_request"];
                if ($arrayIdRequest["agreed_$position"] == 1) {
                    array_push($this->arrayErrors, $idRequest);
                } else {
                    $sql = "UPDATE requests_pending SET agreed_$position=1 WHERE id_request=$idRequest;";
                    $this->SQLS = ($this->SQLS) . $sql;
                }
            }
        }
        if (count($this->arrayErrors)) {
            echo "Fatal Error - The request had already been agreed with by the user.<br>Request ID:";
            var_dump($this->arrayErrors);
            // exit;
            $this->redirect(false, $this->url, array('f' => 1, 'e' => 2));
        }
    }

    public function execute($id_user)
    {
        $this->validateUser($id_user);
        $this->validateAction();
        if ($this->mode === 'decline') {
            var_dump([$this->idTrans]);
            echo '<br>';
            $this->decline([$this->idTrans]);
        } else if ($this->mode === 'agree') {
            $this->agree();
        } else {
            // echo "Error - mode NOT understood<br>mode:";
            $this->redirect(false, $this->url, ['f' => 1, 'e' => 3]);
        }
        $stmt = ($this->dbh)->prepare($this->SQLS);
        echo $this->SQLS;
        $stmt->execute();
        var_dump($stmt->errorInfo());
        if (($stmt->errorInfo())[2]) {
            echo ($stmt->errorInfo())[2];
            exit;
        }
        if ($this->mode === 'decline') {
            $this->redirect(true, $this->url, ['f' => 1, 's' => 2]);
        }
        $this->SQLS = '';
    }

    public function executeTransaction()
    {
        // Lock shifts_assigned
        $sql = 'SELECT id_shift FROM shifts_assigned WHERE under_request=1 FOR UPDATE;';
        $this->dbh->query($sql);

        $sql = strtr('SELECT COUNT(*) FROM requests_pending WHERE id_transaction=$idTrans FOR UPDATE;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $sql = strtr('SELECT id_shift, id_to FROM requests_pending WHERE id_transaction=$idTrans AND agreed_from=1 AND agreed_to=1 FOR UPDATE;', array('$idTrans' => $this->idTrans));
        $arrayRequests = $this->dbh->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($arrayRequests) === intval($stmt->fetchAll(PDO::FETCH_COLUMN)[0])) { // '2'
            // Execute
            // Firstly, invalidate all pending(i.e. status=2) transactions surrounding these shifts
            $this->invalidateAllRequests();
            // Next, update status of requests
            $sql = strtr('UPDATE requests_pending SET `status`=1 WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
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
            $stmt = ($this->dbh)->prepare($this->SQLS);
            $this->redirect(true, $this->url, ['f' => 1, 's' => 1]);
        } else {
            // echo "Awaiting agreements from other members.";
            $this->redirect(true, $this->url, ['f' => 1, 's' => 0]);
        }
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

    public function process($id_user)
    {
        $this->dbh->query('START TRANSACTION;');
        $this->execute($id_user);
        $this->executeTransaction();
    }
}
$handler = new RequestsHandler($_GET["mode"], $_GET["id_user"], $_GET["id_transaction"], $master_handler);
// var_dump($handler);
