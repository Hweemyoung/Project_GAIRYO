<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_date_objects_handler.php";

class RequestsHandler extends DBHandler
{
    private $mode;
    private $id_user;
    private $id_transaction;
    private $timeProceeded;
    private $arrayRequestsInTransaction;
    private $arrayQuery;
    public function __construct($mode, $id_user, $id_transaction, $master_handler, $config_handler, $auto_redirect)
    {
        $this->mode = $mode;
        $this->id_user = $id_user;
        $this->id_transaction = $id_transaction;
        $this->master_handler = $master_handler;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->dbh = $master_handler->dbh;
        $this->url = 'transactions.php';
        $this->config_handler = $config_handler;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->http_host = $config_handler->http_host;
        $this->date_objects_handler = new DateObjectsHandler($master_handler, $this->config_handler);
        $this->auto_redirect = $auto_redirect;

        $this->timeProceeded = date('Y-m-d H:i:s');
        $this->positions = ['from', 'to'];
        $this->SQLS = '';
        $this->arrayErrors = [];
        $this->return = $this->process();
    }

    public function validateUser($id_user)
    {
        if (intval($this->id_user) !== intval($id_user)) {
            echo 'Error - No permission';
            return $this->redirectOrReturn(false, array('f' => 1, 'e' => 0));
        }
        return NULL;
    }

    public function validateAction()
    {
        $sql = strtr('SELECT `status`, id_request, id_from, date_shift, shift, id_to, id_shift, shift FROM requests_pending WHERE id_transaction=$idTrans FOR UPDATE;', array('$idTrans' => $this->id_transaction));
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $this->arrayRequestsInTransaction = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
        if (in_array('0', array_keys($this->arrayRequestsInTransaction)) || in_array('1', array_keys($this->arrayRequestsInTransaction))) {
            echo "Fatal Error - Some requests had already been closed:<br>";
            // var_dump($results);OK
            return $this->redirectOrReturn(false, array('f' => 1, 'e' => 1));
            exit;
        }

        return NULL;
    }

    private function checkShiftsInSamePart()
    {
        foreach ($this->arrayRequestsInTransaction['2'] as $arrRequest) {
            foreach ($this->config_handler->arrayShiftsByPart as $shiftPart => $arrShifts) {
                if (in_array($arrRequest['shift'], $arrShifts)) {
                    break;
                }
            }
            // Found shiftPart
            $sqlConditions = $this->genSqlConditions($arrShifts, 'shift', 'OR');
            $date_shift = $arrRequest['date_shift'];
            $sql = "SELECT EXISTS (SELECT 1 FROM shifts_assigned WHERE id_user=$arrRequest->id_to AND done=0 AND date_shift='$date_shift' AND $sqlConditions);";
            $stmt = $this->querySql($sql);
            $result = $stmt->fetch();
            $stmt->closeCursor();
            if ($result) {
                $id_to = $arrRequest['id_to'];
                return $this->redirectOrReturn(false, array('f' => 1, 'e' => 3, 'id_to' => $id_to, 'date' => $date_shift, 'part' => $shiftPart));
            }
        }
        return NULL;
    }

    private function decline($sqlConditions)
    // Method 'decline' returns NULL.
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
        return NULL;
    }

    private function invalidateAllRequests()
    // Method 'invalidateAllRequests' returns NULL.
    {
        // Select pending id_transactions surrounding the shifts
        $arrayIdShifts = [];
        foreach ($this->arrayRequestsInTransaction['2'] as $request) {
            array_push($arrayIdShifts, $request['id_shift']);
        }
        $sqlConditions = $this->genSqlConditions($arrayIdShifts, 'id_shift', 'OR');
        echo $sqlConditions . '<br>';
        $sql = 'SELECT id_transaction FROM requests_pending WHERE `status`=2 AND ' . $sqlConditions . 'FOR UPDATE;';
        $stmt = ($this->dbh)->prepare($sql);
        $stmt->execute();
        $arrayByIdTrans = $stmt->fetchAll(PDO::FETCH_GROUP);
        // Invalidate transactions
        $sqlConditions = $this->genSqlConditions(array_keys($arrayByIdTrans), 'id_transaction', 'OR');
        echo $sqlConditions . '<br>';
        $this->decline($sqlConditions);
        return NULL;
    }

    private function saveDateObjects()
    {
        // Method 'saveDateObjects shouldn't return NULL
        $stmt = $this->querySql($this->sqlForNumLangs);
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]);
        $stmt->closeCursor();
        $this->date_objects_handler->setArrayDateObjects($arrayShiftObjectsByDate);
        // var_dump($this->date_objects_handler->arrayDateObjects);
        $arr = [];
        foreach ($this->date_objects_handler->arrayDateObjects as $date => $dateObject) {
            $arr[$date] = clone $dateObject;
        }
        return $arr;
    }

    private function updateUnderRequest($sqlConditions)
    {
        // For every shift in invalidated transactions, check if there is any other requests surrounding it and update under_request.
        $sql = 'SELECT id_shift FROM requests_pending WHERE ' . $sqlConditions;
        $stmt = $this->querySql($sql);
        $arrayByIdShift = $stmt->fetchAll(PDO::FETCH_GROUP);
        $stmt->closeCursor();
        // if call request
        if (array_keys($arrayByIdShift) === [NULL]) {
            // No shift object to handle. Return.
            echo 'This is CALL request. No shift object to handle. <br>';
        } else {
            // Lock. Dates will be used for checking language changes between before and after.
            $stmt = $this->querySql('SELECT date_shift, shift, id_user FROM shifts_assigned WHERE id_shift in (' . $sql . ') FOR UPDATE;');
            $arrayByDateShift = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]); // array(0) if call request
            $stmt->closeCursor();
            // Save Date Objects Before execution.
            $sqlConditions = $this->genSqlConditions(array_keys($arrayByDateShift), 'date_shift', 'OR');
            // $sqlConditions === (0) if id_shift === NULL i.e. call request
            // This sql will be used again after execution.
            $this->sqlForNumLangs = "SELECT date_shift, date_shift, shift, id_user, id_shift FROM shifts_assigned WHERE $sqlConditions FOR UPDATE;";
            // echo '<br>$this->sqlForNumLangs = ' . $this->sqlForNumLangs . '<br>';
            $this->arrayDateObjects_before = $this->saveDateObjects(); // === [] if call request
            // Get id_shifts
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
        return NULL;
    }

    private function agree()
    {
        foreach ($this->positions as $position) {
            $sql = strtr('SELECT id_request, agreed_$position FROM requests_pending WHERE id_transaction=$idTrans AND id_$position=$idUser FOR UPDATE;', array('$idTrans' => $this->id_transaction, '$idUser' => $this->id_user, '$position' => $position));
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
            echo "Fatal Error - The request had already been agreed with by the user.<br>Request ID:";
            return $this->redirectOrReturn(false, array('f' => 1, 'e' => 2));
        }
        return NULL;
    }

    public function execute()
    {
        $checkpoint = $this->validateAction();
        if ($checkpoint !== NULL) {
            return $checkpoint;
        }
        if ($this->mode === 'decline') {
            echo $this->id_transaction;
            // $this->decline(['id_transaction=' . $this->id_transaction]);
            $this->decline("(id_transaction=$this->id_transaction)");
        } else if ($this->mode === 'agree') {
            $checkpoint = $this->agree();
            if ($checkpoint !== NULL) {
                return $checkpoint;
            }
        } else {
            echo "Error - mode NOT understood<br>mode:";
            return $this->redirectOrReturn(false, ['f' => 1, 'e' => 3]);
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
            return $this->redirectOrReturn(true, ['f' => 1, 's' => 2]);
        }
        $this->SQLS = '';
    }

    public function executeTransaction()
    {
        // Lock shifts_assigned
        $sql = 'SELECT id_shift FROM shifts_assigned WHERE under_request=1 FOR UPDATE;';
        $this->querySql($sql);

        $sql = strtr('SELECT COUNT(*) FROM requests_pending WHERE id_transaction=$idTrans FOR UPDATE;', array('$idTrans' => $this->id_transaction));
        $stmt = $this->querySql($sql);
        $sql = strtr('SELECT id_shift, id_to FROM requests_pending WHERE id_transaction=$idTrans AND agreed_from=1 AND agreed_to=1 FOR UPDATE;', array('$idTrans' => $this->id_transaction));
        $arrayRequests = $this->querySql($sql)->fetchAll(PDO::FETCH_ASSOC);
        if (count($arrayRequests) === intval($stmt->fetchAll(PDO::FETCH_COLUMN)[0])) { // intval('2')
            echo 'All members agreed.<br>';

            // Check if any member has a shift in the same shift part
            $checkpoint = $this->checkShiftsInSamePart();
            if ($checkpoint !== NULL) {
                return $checkpoint;
            }

            // Execute
            // Firstly, invalidate all pending(i.e. status=2) transactions surrounding these shifts
            $this->invalidateAllRequests();
            // Next, update status of requests
            $sql = "UPDATE requests_pending SET `status`=1, time_proceeded='$this->timeProceeded' WHERE id_transaction=$this->id_transaction;";
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
                $this->arrayQuery = ['f' => 1, 's' => 1];
                echo 'Dont stop!';
            } else {
                var_dump($stmt->errorInfo());
                exit;
            }
        } else {
            // echo "Awaiting agreements from other members.";
            return $this->redirectOrReturn(true, ['f' => 1, 's' => 0]);
        }
        return NULL;
    }

    public function checkLangsChange()
    {
        // After
        $this->arrayDateObjects_after = $this->saveDateObjects();
        // var_dump($this->arrayDateObjects_before);
        $arrNotEnough = [];
        // var_dump($this->arrayDateObjects_after);
        foreach (array_keys($this->arrayDateObjects_after) as $date) {
            // For each date object (after)
            foreach (array_keys($this->arrayDateObjects_after[$date]->enoughLangsByPart) as $part) {
                if (!$this->arrayDateObjects_after[$date]->enoughLangsByPart[$part]) {
                    echo $date . ': not enough<br>';
                    echo 'part:' . $part . '<br>';
                    // Find if this transactions contributes to lack of langs
                    // foreach ($this->arrayDateObjects_after[$date]->arrBalancesByPart[$part] as $arrBalances) {
                    foreach (array_keys($this->arrayDateObjects_after[$date]->arrBalancesByPart[$part]) as $lang) {
                        // echo 'lang:' . $lang . '<br>';
                        // var_dump($this->arrayDateObjects_before[$date]->arrBalancesByPart[$part]);
                        // echo '<br>';
                        if (isset($this->arrayDateObjects_before[$date]->arrBalancesByPart[$part][$lang])) {
                            // if it was not sufficient before execution
                            // echo 'it was not sufficient before execution.<br>';
                            echo 'before:' . $this->arrayDateObjects_before[$date]->arrBalancesByPart[$part][$lang] . '<br>';
                            echo 'after:' . $this->arrayDateObjects_after[$date]->arrBalancesByPart[$part][$lang] . '<br>';
                            if ($this->arrayDateObjects_after[$date]->arrBalancesByPart[$part][$lang] >= $this->arrayDateObjects_before[$date]->arrBalancesByPart[$part][$lang]) {
                                // No Contribution
                                // echo 'But no contribution. <br>';
                                continue;
                            }
                        }
                        // echo 'It has contribution. <br>';
                        array_push($arrNotEnough, [$date, $part, $lang]);
                        // }
                    }
                }
            }
        }
        // var_dump($arrNotEnough);
        if (count($arrNotEnough)) {
            var_dump($arrNotEnough);
            $this->arrayQuery = ['f' => 1, 'e' => 4];
            for ($i = 0; $i < count($arrNotEnough); $i++) {
                $this->arrayQuery["case$i"] = implode('_', $arrNotEnough[$i]);
            }
            // var_dump($this->arrayQuery);
            echo 'NOT ENOUGH LANGS!';
            return $this->redirectOrReturn(false, $this->arrayQuery);
        } else {
            // var_dump($this->arrayQuery);
            echo 'All Good!';
            return $this->redirectOrReturn(true, $this->arrayQuery);
        }
    }

    private function redirectOrReturn($commit, $arrQuery)
    {
        if ($this->auto_redirect) {
            $this->redirect($commit, $this->url, $arrQuery);
        } else {
            return [$commit, $arrQuery];
        }
    }

    public function process()
    {
        $checkpoint = $this->validateUser($this->master_handler->id_user);
        if ($checkpoint !== NULL) {
            return $checkpoint;
        }
        $this->beginTransactionIfNotIn();
        $this->lockTablesIfNotInnoDB(['shifts_assigned', 'requests_pending']);
        $checkpoint = $this->execute();
        if ($checkpoint !== NULL) {
            return $checkpoint;
        }
        $checkpoint = $this->executeTransaction();
        if ($checkpoint !== NULL) {
            return $checkpoint;
        }
        $checkpoint = $this->checkLangsChange();
        if ($checkpoint !== NULL) {
            return $checkpoint;
        }
        // $this->goOrReturn($this->execute());
        // $this->goOrReturn($this->executeTransaction());
        // $this->goOrReturn($this->checkLangsChange());
    }
}
