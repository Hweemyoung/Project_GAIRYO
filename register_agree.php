<?php
require './check_session.php';

class requestsHandler
{
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
        var_dump($stmt->errorInfo());
        $results = $stmt->fetchAll(PDO::FETCH_GROUP);
        if (in_array('0', array_keys($results)) || in_array('1', array_keys($results))) {
            // Raise Error
            echo "Fatal Error - Some requests had already been closed:<br>";
            var_dump($results);
            exit;
        }
        return true;
    }

    function validateDecline()
    {
        $sql = strtr('SELECT id_request, `status` FROM requests_pending WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        var_dump($stmt->errorInfo());
        $arrayStatus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($arrayStatus as $array) {
            $status = $array["status"];
            if ($status == '0' || $status == '1') {
                array_push($array["id_request"], $this->arrayErrors);
            }
        }
        if (count($this->arrayErrors)) {
            echo "
            Fatal Error - Some requests had already been declined.<br>Request Id:";
            var_dump($this->arrayErrors);
            exit;
        }

        $sql = strtr('UPDATE requests_pending SET `status`=0 WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $this->SQLS = ($this->SQLS) . $sql;
    }

    function validateAgreement()
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
        $sql = strtr('UPDATE requests_pending SET `status`=0 WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $this->SQLS = ($this->SQLS) . $sql;
    }

    function execute()
    {
        if ($this->mode === 'decline') {
            $this->validateDecline();
        } else if ($this->mode === 'agree') {
            $this->validateAgreement();
        } else {
            echo "Error - mode NOT understood<br>mode:";
            echo $this->mode;
            exit;
        }
        $stmt = ($this->dbh)->prepare($this->SQLS);
        $stmt->execute();
        var_dump($stmt->errorInfo());
    }

    function executeTransaction()
    {
        $sql = strtr('SELECT id_shift, id_to FROM requests_pending WHERE id_transaction=$idTrans AND agreed_from=1 AND agreed_to=1;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        var_dump($stmt->errorInfo());
        $arrayRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sql = strtr('SELECT COUNT(*) FROM requests_pending WHERE id_transaction=$idTrans;', array('$idTrans' => $this->idTrans));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        var_dump($stmt->errorInfo());
        if (count($arrayRequests) == $stmt->fetchAll(PDO::FETCH_COLUMN)[0]) {
            foreach ($arrayRequests as $arrayRequest) {
                // Execute transaction
                $sql = strtr(
                    'UPDATE shifts_assigned SET id_user=$idUser WHERE id_shift=`$idShift`;',
                    array('$idUser' => $arrayRequest["id_to"], '$idShift' => $arrayRequest["id_shift"])
                );
                $this->SQLS = ($this->SQLS) . $sql;
            }
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
$handler->validateUser($id_user);
$handler->validateAction();
$handler->execute();
$handler->executeTransaction();