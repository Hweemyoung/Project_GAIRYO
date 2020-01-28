<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/config.php";
require_once "$homedir/utils.php";

class ShiftsDistributor extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->master_handler = $master_handler;
        $this->config_handler = $config_handler;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->m = $config_handler->m;
        $this->init();
        $this->process();
    }

    private function init()
    {
        $this->arrDateShiftsDeployerByDate = [];
        $this->arrDateRange = [];
        $this->addPropsToMemberObjects(); // memberObject->numDaysApplied = 0; memberObject->numDaysDeployed = 0; memberObject->arrShiftAppObjects = [];
    }

    private function addPropsToMemberObjects()
    {
        foreach ($this->arrayMemberObjectsByIdUser as $memberObject) {
            $memberObject->initProps();
        }
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        $this->loadApplications();
        $this->setArrDateShiftsHandlerByDate();
        $this->distributeAllShifts();
        $this->dbh->commit();
    }

    private function loadApplications()
    {
        $sql = "SELECT id_user, " . implode(', ', $this->config_handler->set_arr_mshifts()->arr_mshifts) . " FROM shifts_submitted WHERE m='$this->m'";
        $stmt = $this->querySql($sql);
        $this->arrMemberApplicationsByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        // echo $sql .'<br>';
        // var_dump($this->arrMemberApplicationsByIdUser['3']);
        // Some values (e.g. 31st) could be NULL
        $stmt->closeCursor();
        return $this;
    }

    private function setArrDateShiftsHandlerByDate()
    {
        $this->arrDateShiftsDeployerByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        // echo "Current DateTime<br>";
        // var_dump($dateTime->format('Y-m-d'));
        // echo '<br>';
        foreach (range(1, 31) as $date) {
            $dateTime = DateTime::createFromFormat('Ymd', $this->m . '01'); // '2020-03-01'
            if ($date > 15) {
                $dateTime = $dateTime->modify('-1 days'); // '2020-02-28';
                // echo 'Modified DateTime:' . $dateTime->format('Y-m-d') . '<br>';
                if ($date > 28) {
                    // Check if $date could be valid DateTime
                    if (!checkdate($dateTime->format('n'), $date, $dateTime->format('Y'))) { // If like '2020-02-30'
                        echo "For date = $date, this datetime is NOT valid <br>";
                        break;
                    }
                    echo "For date = $date, this datetime is valid <br>";
                }
            }
            // Add date to arrDateRange
            $this->arrDateRange[] = $date;
            $dateTime->setDate($dateTime->format('Y'), $dateTime->format('n'), $date);
            echo "Modified DateTime<br>";
            var_dump($dateTime->format('Y-m-d'));
            echo '<br>';
            $this->arrDateShiftsDeployerByDate[$date] = new DateShiftsDeployer($date, $this->master_handler, $this->config_handler);
            // echo "$date 's DateShiftsDeployer: <br>";
            // var_dump($this->arrDateShiftsDeployerByDate[$date]);
            // echo '<br>';
            $appliedForDate = false;
            foreach (['O', 'A', 'B', 'H', 'C', 'D'] as $shift) {
                // echo "Now $shift! <br>";
                // Set properties
                // 1. Push all ShiftAppObjects
                foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                    if ($this->arrMemberApplicationsByIdUser[$id_user][$date . $shift] == 1) {
                        // echo "\$this->arrMemberApplicationsByIdUser[$id_user][$date$shift] = '1'<br><br>";
                        if ($shift === 'O') {
                            foreach ($this->arrayShiftsByPart as $arrShifts) {
                                foreach ($arrShifts as $shiftTemp) {
                                    // Create date_shift
                                    $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shiftTemp);
                                    // echo "Created ShiftObject: id_user: $shiftObject->id_user / date_shift: $shiftObject->date_shift / shift: $shiftObject->shift<br>";
                                    $this->pushShiftAppObject($shiftObject);
                                }
                            }
                        } else {
                            // If person applied for this
                            $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shift);
                            // echo "Created ShiftObject: id_user: $shiftObject->id_user / date_shift: $shiftObject->date_shift / shift: $shiftObject->shift<br>";
                            $this->pushShiftAppObject($shiftObject);
                        }
                        $appliedForDate = true;
                    }
                }
            }
            $this->updateNumDaysApplied($id_user, $appliedForDate);
            // Update 
        }
        // echo 'keys of arrShiftStatusByShift<br>';
        // var_dump(array_keys($this->arrDateShiftsDeployerByDate[16]->arrShiftStatusByShift));
        // echo '<br>';
        // $this->arrDateShiftsDeployerByDate[16]->deployAllShifts();
        // echo '<br> Deployment Completed: <br>';
        // foreach ($this->arrDateShiftsDeployerByDate[16]->arrayShiftObjectsByShift as $shift => $arrShiftObjects) {
        //     echo "$shift<br>";
        //     foreach ($arrShiftObjects as $shiftObject) {
        //         echo "id_user : $shiftObject->id_user<br>";
        //     }
        // }
        // $this->arrDateShiftsDeployerByDate[16]->assignAllShifts($this);
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrNumLangsAppByPart);
        // echo '<br>';
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrScoresByIdUser);
        // echo '<br><br>';
        // echo 'arrShiftAppObjectsByIdUser: <br>';
        // var_dump($this->arrDateShiftsDeployerByDate[16]->arrShiftAppObjectsByIdUser);
        // var_dump($this->arrDateShiftsDeployerByDate['2020-02-10']->arrShiftAppObjectsByIdUser);
        // var_dump(($this->arrDateShiftsDeployerByDate)['2020-02-16']);
    }

    private function updateNumDaysApplied($id_user, $appliedForDate)
    {
        // echo '<br>';
        // echo $id_user;
        // echo '<br>';
        if ($appliedForDate) {

            // echo $id_user . ' Before: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
            $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied++;
            // echo $id_user . ' After: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
        }
    }

    private function genShiftAppObject($reflectionShiftObject, $id_user, $dateTime, $shift)
    {
        $shiftObject = $reflectionShiftObject->newInstanceWithoutConstructor();
        $shiftObject->id_user = $id_user;
        $shiftObject->date = intval($dateTime->format('j')); // int(23)
        $shiftObject->date_shift = $dateTime->format('Y-m-d'); // '2020-02-23'
        $shiftObject->shift = $shift;
        $shiftObject->__construct($this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser);
        return $shiftObject;
    }

    private function pushShiftAppObject($shiftObject)
    {
        // To MemberObject->arrShiftAppObjects
        $shiftObject->memberObject->pushShiftAppObjects($shiftObject);
        // To DateShiftsDeployer->arrShiftAppObjectsByIdUser
        $this->arrDateShiftsDeployerByDate[$shiftObject->date]->pushShiftAppObject($shiftObject);
        // To ShiftStatus->arrShiftAppObjectsByIdUser
        $this->arrDateShiftsDeployerByDate[$shiftObject->date]->pushShiftAppObjectToShiftStatus($shiftObject);
    }

    private function distributeAllShifts()
    {
        // Shuffle range of date
        shuffle($this->arrDateRange);
        foreach ($this->arrDateRange as $date) {
            echo "Deploying date $date<br>";
            // Update MemberObjects
            $this->arrDateShiftsDeployerByDate[$date]->addNumDaysProceeded();
            // Deploy all shifts
            $this->arrDateShiftsDeployerByDate[$date]->deployAllShifts();
            // Assign all shifts
            $this->arrDateShiftsDeployerByDate[$date]->assignAllShifts($this);
        }
    }
}

class DateShiftsDeployer extends DateObject
{
    public function __construct($date, $master_handler, $config_handler)
    {
        $this->date = $date;
        $this->master_handler = $master_handler;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->config_handler = $config_handler;
        $this->arrScoreItems = $config_handler->arrScoreItems;
        $this->arrayNumLangsByPart = [];
        $this->arrayShiftObjectsByShift = [];
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
        $this->arrShiftStatusByShift = [];
        $this->arrShiftAppObjectsByIdUser = [];
        $this->arrScoresByIdUser = [];
    }

    public function pushShiftAppObject($shiftObject)
    {
        if (!isset($this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user])) {
            $this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user] = [];
        }
        array_push($this->arrShiftAppObjectsByIdUser[$shiftObject->memberObject->id_user], $shiftObject);
        // echo "Pushing shiftAppObject to DateShiftsDeployer... $shiftObject->date $shiftObject->shift<br>";
        // echo 'Now id_user ' . $shiftObject->memberObject->id_user . ' has ' . count($shiftObject->memberObject->arrShiftAppObjects) . ' shiftAppObjects.<br>';
        // var_dump($shiftObject->memberObjects->arrShiftAppObjects);
        // echo count($shiftObject->memberObject->arrShiftAppObjects);
    }

    public function pushShiftAppObjectToShiftStatus($shiftObject)
    {
        // This method is called explicitly in ShiftsDistributor instance.
        if (!isset($this->arrShiftStatusByShift[$shiftObject->shift])) {
            $this->arrShiftStatusByShift[$shiftObject->shift] = new ShiftStatus($shiftObject->shift, $this->config_handler);
        }
        // echo '$arrShiftStatusByShift = ';
        // var_dump($this->arrShiftStatusByShift);
        // echo '<br>';
        $this->arrShiftStatusByShift[$shiftObject->shift]->pushShiftAppObjectsByIdUser($shiftObject);
        $this->arrShiftStatusByShift[$shiftObject->shift]->updateProps();
    }

    public function deployAllShifts()
    {
        while (count($this->arrShiftAppObjectsByIdUser)) {
            $this->deployShift();
        }
    }

    private function deployShift()
    {
        // Calc scores
        $this->setArrScoresByIdUser();

        // Select member and shift
        $id_user_seleted = $this->getMemberToDeploy();
        $shiftObjectDeployed = $this->deployShiftOfMember($id_user_seleted);

        // Unset Member from candidates i.e. Unset from DateShiftsDeployer::arrShiftAppObjects
        unset($this->arrShiftAppObjectsByIdUser[$id_user_seleted]);
        // echo "User $id_user_seleted has been unset.<br>";
        // var_dump($this->arrShiftAppObjectsByIdUser[$id_user_seleted]);
        // echo '<br>';
        // var_dump(array_keys($this->arrShiftAppObjectsByIdUser));
        // echo '<br>';

        if ($shiftObjectDeployed === false) {
            // No valid shift found for this member.
            return;
        } else {
            // Push to arrayShiftObjectsByShift
            $this->pushArrayShiftObjectByShift($shiftObjectDeployed); // DateObject method
            $this->pushArrayNumLangsByPart($shiftObjectDeployed); // DateObject method: update numLangs
            // echo 'keys of arrShiftStatusByShift<br>';
            // var_dump($this->arrShiftStatusByShift);
            // echo '<br>';
            $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->pushArrShiftObjectByIdUser($shiftObjectDeployed); // For ShiftStatus

            // Unset from ShiftStatus::arrShiftAppObjects. For DateShiftsDeployer, already done above.
            $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);

            // Update props for ShiftStatus
            $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->updateProps();
            if ($this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->vacancy === 1){
                foreach($this->arrShiftAppObjectsByIdUser as $id_user => $arrShiftAppObjects){
                    foreach($arrShiftAppObjects as $key => $shiftObject){
                        if($shiftObject->shift === $shiftObjectDeployed->shift){
                            unset($arrShiftAppObjects[$key]);
                        }
                    }
                }
            }

            // Update prop of MemberObject;
            $shiftObjectDeployed->memberObject->numDaysDeployed++;
        }
    }

    public function assignAllShifts(ShiftsDistributor $shifts_distributor)
    {
        if (count($this->arrayShiftObjectsByShift)) {
            $arrValues = [];
            foreach ($this->arrayShiftObjectsByShift as $shift => $arrShiftObjects) {
                foreach ($arrShiftObjects as $shiftObject) {
                    array_push($arrValues, "($shiftObject->id_user, '$shiftObject->date_shift', '$shiftObject->shift')");
                }
            }
            $SQLS = "INSERT INTO shifts_assigned (id_user, date_shift, shift) VALUES " . implode(', ', $arrValues) . ';';
            echo "SQLS = $SQLS<br>";
            $shifts_distributor->executeSql($SQLS);
            // $stmt = $shifts_distributor->querySql($SQLS);
            // echo "Now assigning all shifts of $this->date <br>";
            // var_dump($stmt->errorInfo());
            // echo '<br>';
        }
    }

    private function deployShiftOfMember($id_user_seleted)
    {
        // Select Part
        $filteredValues = $this->pickPartAndShiftObjects($id_user_seleted); // [$part, $arrShiftObjectsFiltered] OR false
        // var_dump($filteredValues);
        // echo '<br>';

        // Select a Shift in the part
        if ($filteredValues === false) {
            return false;
        } else {
            $shiftObjectDeployed = $this->decideShift($filteredValues[1]);
            return $shiftObjectDeployed;
        }
    }

    private function pickPartAndShiftObjects($id_user_seleted)
    {
        echo "Deploying Shift of id_user = $id_user_seleted <br>";
        // if count shift = 1
        if (count($this->arrShiftAppObjectsByIdUser[$id_user_seleted]) === 1) {
            echo 'User applied for only one shift.<br>';
            if ($this->arrShiftStatusByShift[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shift]->vacancy >= 1) {
                echo 'This shift is already full.<br>';
                echo 'Vacancy = ' . $this->arrShiftStatusByShift[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shift]->vacancy . '<br>';
                // Unset this shiftApp from DateShiftsDeployer::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
                $this->unsetShiftAppObject($this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]);
                // This member is out.
                return false;
            } else {
                echo 'Part and Shift decided!<br>';
                $part = $this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shiftPart;
                $arrShiftObjectsFiltered = $this->arrShiftAppObjectsByIdUser[$id_user_seleted];
                // var_dump($arrShiftObjectsFiltered);
            }
        } else {
            echo 'Check splited part.<br>';
            // check splited part?
            $arrKeyPartsApp = [];
            $arrShiftAppObjectsByPart = [];
            foreach ($this->arrShiftAppObjectsByIdUser[$id_user_seleted] as $shiftObject) {
                // Check if this shift is already filled out.
                if ($this->arrShiftStatusByShift[$shiftObject->shift]->vacancy >= 1) {
                    echo 'This shift is already full.<br>';
                    // Unset this shiftApp from DateShiftsDeployer::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
                    $this->unsetShiftAppObject($shiftObject);
                    // Search for next shift
                    continue;
                }
                $arrKeyPartsApp[$shiftObject->shiftPart] = true;
                if (!isset($arrShiftAppObjectsByPart[$shiftObject->shiftPart])) {
                    $arrShiftAppObjectsByPart[$shiftObject->shiftPart] = [];
                }
                array_push($arrShiftAppObjectsByPart[$shiftObject->shiftPart], $shiftObject);
            }
            echo "Count of part candidates: " . count($arrKeyPartsApp) . '<br>';
            if (count($arrKeyPartsApp) === 0) {
                // This member is out.
                return false;
            } elseif (count($arrKeyPartsApp) === 1) {
                $part = array_keys($arrKeyPartsApp)[0];
                $arrShiftObjectsFiltered = $this->arrShiftAppObjectsByIdUser[$id_user_seleted];
            } else {
                echo 'Multiple part candidates.<br>';
                // Splited.
                // Select part accordingto lang contribution
                // Compare in order of lang priority in each part
                $arrLingualities = [];
                foreach ($this->config_handler->arrayLangsShort as $lang) {
                    if ($this->arrayMemberObjectsByIdUser[$id_user_seleted]->$lang === '1') {
                        array_push($arrLingualities, $lang);
                    }
                }
                // $i: i th priority of language
                for ($i = 0; $i < $this->config_handler->numLangs; $i++) {
                    $arrLangVacancyByPart = [];
                    foreach (array_keys($arrKeyPartsApp) as $part) {
                        $arrLangs = $this->config_handler->arrayLangsByPart[$part];
                        $lang = array_keys($arrLangs)[$i];
                        if (!in_array($lang, $arrLingualities) || $arrLangs[$lang] === NULL) {
                            // Search for next part
                            continue;
                        }
                        if (!isset($this->arrayNumLangsByPart[$part][$lang])) {
                            $arrLangVacancyByPart[$part] = 0;
                        } else {
                            $arrLangVacancyByPart[$part] = $this->arrayNumLangsByPart[$part][$lang] / $arrLangs[$lang];
                        }
                    }
                    if (count($arrLangVacancyByPart)) {
                        if (min($arrLangVacancyByPart) < 1) {
                            // Insufficient
                            $part = array_keys($arrLangVacancyByPart, min($arrLangVacancyByPart));
                            if (is_array($part)) {
                                // part selected: $part
                                $part = $part[mt_rand(0, count($part) - 1)];
                            }
                            break;
                        }
                    }
                    // This will give randomly selected part if not decided until last priority
                    $part = array_keys($arrKeyPartsApp)[mt_rand(0, count($arrKeyPartsApp) - 1)];
                    // If not break, search for next priority
                }
            }
            $arrShiftObjectsFiltered = $arrShiftAppObjectsByPart[$part];
        }
        echo "Selected part: $part";
        return [$part, $arrShiftObjectsFiltered];
    }

    private function decideShift($arrShiftObjectsFiltered)
    {
        if (count($arrShiftObjectsFiltered) > 1) {
            // echo '<br>ShiftPriority<br>';
            // echo 'Before sort:<br>';
            // var_dump(array_keys($this->arrShiftStatusByShift));
            // echo '<br>';
            uasort($this->arrShiftStatusByShift, function ($a, $b) {
                if ($a->percentApp == $b->percentApp) {
                    return 0;
                }
                return ($a->percentApp < $b->percentApp) ? -1 : 1;
            });
            echo 'After sort:<br>';
            foreach ($this->arrShiftStatusByShift as $shiftStatus) {
                echo "$shiftStatus->shift $shiftStatus->percentApp<br>";
            }
            echo '<br>';
            $shiftPriority = array_keys($this->arrShiftStatusByShift);
            // echo '$arrShiftObjectsFiltered';
            // echo 'Before sort:<br>';
            // var_dump($arrShiftObjectsFiltered);
            // echo '<br>';
            usort($arrShiftObjectsFiltered, function ($a, $b) use ($shiftPriority) {
                $pos_a = array_search($a->shift, $shiftPriority);
                $pos_b = array_search($b->shift, $shiftPriority);
                return $pos_a - $pos_b;
            });
        } else {
            echo 'One shift passed. No sorting needed.';
        }
        return $arrShiftObjectsFiltered[0];
    }

    private function unsetShiftAppObject($shiftObjectDeployed)
    {
        // For ShiftsDistributor: No way to access 
        // For DateShiftsDeployer
        $this->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);
        // For ShiftStatus
        $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);
    }

    private function unsetShiftAppObjectsByIdUser($shiftObject)
    {
        $key = array_search($shiftObject, $this->arrShiftAppObjectsByIdUser[$shiftObject->id_user], true);
        if ($key !== false) {
            unset($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user][$key]);
        } else {
            echo 'ERROR! Shift object being deployed has already been unset from (or never been pushed into) DateShiftsDeployer::arrShiftAppObjectsByIdUser.';
            exit;
        }
    }

    public function getMemberToDeploy()
    {
        // Call after setting arrScoresByIdUser
        foreach ($this->arrScoreItems as $score_item_name => $minORmax) {
            if (count($this->arrScoreItems) > 1) {
                // Every step, $this->arrScoresByIdUser is shrinked.
                $this->genAndFilterArrScoresByIdUser($score_item_name, $minORmax);
            } else {
                break;
            }
        }
        echo 'Filtering completed: <br>';
        if (count($this->arrScoresByIdUser) > 1) {
            $id_user_seleted = array_keys($this->arrScoresByIdUser)[mt_rand(0, count($this->arrScoresByIdUser) - 1)];
        } else {
            $id_user_seleted = array_keys($this->arrScoresByIdUser)[0];
        }
        echo "id_user_seleted: $id_user_seleted";
        echo '<br>';
        return $id_user_seleted;
    }

    private function genAndFilterArrScoresByIdUser(string $score_item_name, string $minORmax)
    {
        $arrItemValues = [];
        foreach ($this->arrScoresByIdUser as $id_user => $arrScores) {
            $arrItemValues[$id_user] = $arrScores[$score_item_name];
        }
        var_dump($arrItemValues);
        echo '<br>';
        echo "Now selecting $minORmax $score_item_name <br>";
        // var_dump($arrItemValues);
        if ($minORmax === 'max') {
            $threshold = max($arrItemValues);
        } elseif ($minORmax === 'min') {
            $threshold = min($arrItemValues);
        } else {
            echo 'Argument not understood.';
            exit;
        }
        echo "Threshold: " . $threshold . '<br>';
        $arrItemValues = array_filter($arrItemValues, function ($val) use ($threshold) {
            return $val === $threshold;
        });
        $newArrScoresByIdUser = [];
        foreach (array_keys($arrItemValues) as $id_user) {
            $newArrScoresByIdUser[$id_user] = $this->arrScoresByIdUser[$id_user];
        }
        $this->arrScoresByIdUser = $newArrScoresByIdUser;
    }

    public function setArrScoresByIdUser()
    {
        // echo '2nd call of setArrScoresByIdUser<br>';
        // var_dump(array_keys($this->arrShiftAppObjectsByIdUser));
        // echo '<br>';
        echo 'Num of Candidates: ' . count($this->arrShiftAppObjectsByIdUser) . '<br>';
        $this->arrScoresByIdUser = [];
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->setArrScores($id_user);
        }
        var_dump($this->arrScoresByIdUser);
        echo '<br>';
    }

    public function setArrScores($id_user)
    {
        $this->setNumShiftAppObjects($id_user);
        $this->setNumAppNotEnough($id_user);
        $this->setLangScore($id_user);
        $this->setDeployRatio($id_user);
        
    }

    private function setNumShiftAppObjects($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['numShiftAppObjects'] = count($this->arrShiftAppObjectsByIdUser[$id_user]);
    }

    private function setNumAppNotEnough($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['numAppNotEnough'] = 0;
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            // echo "$this->date $shiftObject->shift" . ' Percentage:' . $this->arrShiftStatusByShift[$shiftObject->shift]->vacancy . '<br>';
            if ($this->arrShiftStatusByShift[$shiftObject->shift]->vacancy < 1) {
                $this->arrScoresByIdUser[$id_user]['numAppNotEnough']++;
            }
        }
    }

    private function setDeployRatio($id_user)
    {
        if ($this->arrayMemberObjectsByIdUser[$id_user]->numDaysProceeded === 0) {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = INF;
        } else {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = $this->arrayMemberObjectsByIdUser[$id_user]->numDaysDeployed / $this->arrayMemberObjectsByIdUser[$id_user]->numDaysProceeded;
        }
    }

    private function setLangScore($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['langScore'] = 0;
        $arr = [];
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            foreach ($this->config_handler->arrayLangsShort as $lang) {
                // For every lang
                if (isset($arr[$shiftObject->shiftPart][$lang])) {
                    // If the lang in this part already considered
                } else {
                    if ($this->config_handler->arrayLangsByPart[$shiftObject->shiftPart][$lang] !== NULL) {
                        // If the lang is required in this part
                        if (isset($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang])) {
                            // If there is already some people who can speak lang in this part
                            if ($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] < $this->config_handler->arrayLangsByPart[$shiftObject->shiftPart][$lang]) {
                                // If not enough num
                                if ($shiftObject->memberObject->$lang === '1') {
                                    // If member can speak lang
                                    $arr[$shiftObject->shiftPart][$lang] = true;
                                } else {
                                    $arr[$shiftObject->shiftPart][$lang] = false;
                                }
                            }
                        } else {
                            if ($shiftObject->memberObject->$lang === '1') {
                                // If member can speak lang
                                $arr[$shiftObject->shiftPart][$lang] = true;
                            } else {
                                $arr[$shiftObject->shiftPart][$lang] = false;
                            }
                        }
                    }
                }
            }
        }
        // Like $arr = [['cn' => true], ['cn' => true, 'de' => false, 'kr' => true]];

        $langScore = 0;
        if (count($arr)) {
            foreach ($arr as $subArr) {
                $j = 0;
                foreach ($subArr as $good) {
                    if ($good) {
                        $j++;
                    }
                }
                $langScore += $j / count($subArr);
            }
            $langScore /= count($arr);
            // Like (1/1 + 2/3) / 2
        }
        // $arr could be empty. e.g. all required langs have already been fulfilled.

        $this->arrScoresByIdUser[$id_user]['langScore'] = $langScore;
    }

    public function pushArrNumLangsAppByPart($shiftObject)
    {
        // echo $shiftObject->date . '<br>';
        if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart])) {
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart] = [];
        }
        foreach ($this->config_handler->arrayLangsShort as $lang) {
            if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang])) {
                $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] = 0;
            }
            // $mem = $shiftObject->memberObject;
            // echo "$shiftObject->shift / $mem->id_user / $lang = " . $shiftObject->memberObject->$lang .'<br>';
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] += $shiftObject->memberObject->$lang;
        }
    }

    private function setArrNumLangsAppByPart()
    {
        if (count($this->arrShiftStatusByShift)) {
            // Initialize array
            $this->arrNumLangsAppByPart = [];
            // var_dump($this->arrShiftStatusByShift);
            foreach ($this->arrShiftStatusByShift as $shiftStatus) {
                // echo $shiftObject->date . '<br>';
                // echo '$arrShiftObjects = ';
                // var_dump($arrShiftObjects);
                // echo '<br>';
                foreach ($shiftStatus->arrShiftAppObjectsByIdUser as $arrShiftObjects) {
                    foreach ($arrShiftObjects as $shiftObject) {
                        // var_dump($shiftObject);
                        // echo '<br>';
                        $this->pushArrNumLangsAppByPart($shiftObject);
                    }
                }
            }
            ksort($this->arrNumLangsAppByPart);
        }
    }

    public function addNumDaysProceeded()
    {
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->arrayMemberObjectsByIdUser[$id_user]->numDaysProceeded++;
        }
    }
}

class ShiftStatus
{
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftObjectsByIdUser = [];
    public $numNeeded;
    public $numApplicants;
    public $vacancy; // <1: insufficient int(1): full
    public $percentApp; // <1: insufficient int(1): fitted >1: Over

    public function __construct($shift, $config_handler)
    {
        $this->shift = $shift;
        $this->config_handler = $config_handler;
        $this->init();
    }

    private function init()
    {
        $this->setPart();
        $this->numNeeded = $this->config_handler->numNeededByShift[$this->shift];
    }

    private function setPart()
    {
        foreach ($this->config_handler->arrayShiftsByPart as $part => $arrShifts) {
            if (in_array($this->shift, $arrShifts)) {
                $this->part = $part;
                break;
            }
        }
        if ($this->part === NULL) {
            echo 'Error occurred setting part. exit!';
            exit;
        }
    }

    public function pushShiftAppObjectsByIdUser($shiftObject)
    {
        // var_dump($shiftObject);
        // echo '<br>';
        // var_dump($this->arrShiftAppObjectsByIdUser);
        if (!isset($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user])) {
            $this->arrShiftAppObjectsByIdUser[$shiftObject->id_user] = [];
        }
        array_push($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user], $shiftObject);
    }

    public function pushArrShiftObjectByIdUser($shiftObject)
    {
        if (!isset($this->arrShiftObjectsByIdUser[$shiftObject->id_user])) {
            $this->arrShiftObjectsByIdUser[$shiftObject->id_user] = [];
        }
        array_push($this->arrShiftObjectsByIdUser[$shiftObject->id_user], $shiftObject);
    }

    public function unsetShiftAppObjectsByIdUser($shiftObject)
    {
        $key = array_search($shiftObject, $this->arrShiftAppObjectsByIdUser[$shiftObject->id_user], true);
        if ($key !== false) {
            unset($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user][$key]);
        } else {
            echo 'ERROR! Shift object being deployed has already been unset from (or never been pushed into) ShiftStatus::arrShiftAppObjectsByIdUser.';
            exit;
        }
    }

    public function updateProps()
    {
        // Call after loop of pushShiftAppObjectsByIdUser method.
        // Update properties
        $this->setVacancy();
        $this->setPercentApp();
        $this->numApplicants = count($this->arrShiftAppObjectsByIdUser);
    }

    private function setVacancy()
    {
        if (count($this->arrShiftObjectsByIdUser) === $this->numNeeded) {
            $this->vacancy = 1;
        } else {
            $this->vacancy = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
        }
    }

    private function setPercentApp()
    {
        if (count($this->arrShiftAppObjectsByIdUser) === $this->numNeeded) {
            $this->percentApp = 1;
        } else {
            $this->percentApp = count($this->arrShiftAppObjectsByIdUser) / $this->numNeeded;
        }
    }
}

class ShiftPartStatus
{
    $numNeeded;
    $vacancy;
    $arrLangs;
    public function __construct($shiftPart, $config_handler){
        $this->shiftPart = $shiftPart;
        $this->numNeeded = $config_hanfder->numNeededByPart[$shiftPart];
        $this->arrNumLangs = [];
        $this->initArrNumLangs();
    }
    
    private function initProps{
        
    }
    
    private function initArrNumLangs(){
        foreach(array_keys($this->arrLangs) as $lang){
            $arrNumLangs[$lang] = 0;
        }
    }
    
    public function addNumLangs($memberObject){
        foreach($arrLangs as $lang => $numNeeded){
            if($numNeed !== NULL){
                $arrNumLangs[$lang] += intval($memberObject->$lang);
            }
        }
    }
}
