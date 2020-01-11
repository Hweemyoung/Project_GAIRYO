<?php
require './check_session.php';

class requestsHandler
{
    private $mode;
    private $idUser;
    private $idTrans;
    private $dbh;
    private $arrayRequestsInTransaction;
    public function __construct($mode, $idUser, $idTrans, $dbh)
    {
        $this->mode = $mode;
        $this->idUser = $idUser;
        $this->idTrans = $idTrans;
        $this->dbh = $dbh;
        $this->positions = ['from', 'to'];
        $this->SQLS = '';
        $this->arrayErrors = [];
    }

    function validateUser($id_user)
    {
        if ($this->idUser !== $id_user) {
            // Raise Error and exit
            echo 'Error - No permission';
            exit;
        }
    }

    function validateAction()
    {
        $sql = strtr('SELECT `status`, id_request, id_from, id_to, id_shift FROM requests_pending WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $this->arrayRequestsInTransaction = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
        if (in_array('0', array_keys($this->arrayRequestsInTransaction)) || in_array('1', array_keys($this->arrayRequestsInTransaction))) {
            echo "Fatal Error - Some requests had already been closed:<br>";
            // var_dump($results);OK
            exit;
        }
        return true;
    }

    private function decline()
    {
        $sql = strtr('UPDATE requests_pending SET `status`=0 WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $this->SQLS = ($this->SQLS) . $sql;
    }

    private function invalidateAllRequests()
    {
        $sqlConditions = [];
        foreach ($this->arrayRequestsInTransaction['2'] as $request) {
            array_push($sqlConditions, 'id_shift=' . $request['id_shift']);
        }
        $sql = strtr('UPDATE requests_pending SET `status`=0 WHERE `status`=2 AND ' . '(' . implode(' OR ', $sqlConditions) . ');', array('$idShift' => $request['id_shift']));
        echo $sql . '<br>';
        $this->SQLS = ($this->SQLS) . $sql;
    }

    private function agree()
    {
        foreach ($this->positions as $position) {
            $sql = strtr('SELECT id_request, agreed_$position FROM requests_pending WHERE id_transaction=$idTrans AND id_$position=$idUser;', array('$idTrans' => $this->idTrans, '$idUser' => $this->idUser, '$position' => $position));
            $stmt = ($this->dbh)->prepare($sql);
            $stmt->execute();
            var_dump($stmt->errorInfo());
            $arrayIdRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            exit;
        }
    }

    function execute()
    {
        if ($this->mode === 'decline') {
            $this->decline();
        } else if ($this->mode === 'agree') {
            $this->agree();
        } else {
            echo "Error - mode NOT understood<br>mode:";
            echo $this->mode;
            exit;
        }
        $stmt = ($this->dbh)->prepare($this->SQLS);
        echo $this->SQLS;
        $stmt->execute();
        var_dump($stmt->errorInfo());
    }

    function executeTransaction()
    {
        $sql = strtr('SELECT id_shift, id_to FROM requests_pending WHERE id_transaction=$idTrans AND agreed_from=1 AND agreed_to=1;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        echo $sql . '<br>';
        $stmt->execute();
        var_dump($stmt->errorInfo());
        $arrayRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sql = strtr('SELECT COUNT(*) FROM requests_pending WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        var_dump($stmt->errorInfo());
        if (count($arrayRequests) === intval($stmt->fetchAll(PDO::FETCH_COLUMN)[0])) { // '2'
            // Execute
            // Firstly, invalidate all pending(i.e. status=2) requests surrounding these shifts
            $this->invalidateAllRequests();
            // Next, update status of requests
            $sql = strtr('UPDATE requests_pending SET `status`=1 WHERE id_transaction=$idTrans;' , array('$idTrans' => $this->idTrans));
            echo $sql; // HERE!

            $this->SQLS = $this->SQLS . $sql;
            // Next, update shifts assigned
            foreach ($arrayRequests as $arrayRequest) {
                // Execute every request
                $sql = strtr(
                    'UPDATE shifts_assigned SET id_user=$idUser, under_request=0 WHERE id_shift=$idShift;',
                    array('$idUser' => $arrayRequest["id_to"], '$idShift' => $arrayRequest["id_shift"])
                );
                echo $sql . '<br>';
                $this->SQLS = ($this->SQLS) . $sql;
            }
            echo $this->SQLS;
            $stmt = ($this->dbh)->prepare($this->SQLS);
            $stmt->execute();
            var_dump($stmt->errorInfo());
        } else {
            echo "
            Awaiting agreements from other members.
            ";
        }
    }
}
$handler = new requestsHandler($_GET["mode"], $_GET["id_user"], $_GET["id_transaction"], $dbh);
var_dump($handler);
$handler->validateUser($id_user);
$handler->validateAction();
$handler->execute();
$handler->executeTransaction();
// $handler->test();
