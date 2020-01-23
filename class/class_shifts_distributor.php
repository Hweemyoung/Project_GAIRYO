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
        $this->http_host = $config_handler->http_host;
        $this->m = $config_handler->m;
        $this->init();
        $this->process();
    }

    private function init()
    {
        $this->arrDateShiftsHandlerByDate = [];
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
        $this->executeSql('START TRANSACTION;');
        $this->loadApplications();
        $this->setArrDateShiftsHandlerByDate();
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
        $this->arrDateShiftsHandlerByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        foreach (range(1, 31) as $date) {
            $this->arrDateShiftsHandlerByDate[$date] = new DateShiftsHandler($date, $this->master_handler, $this->config_handler);
            $appliedForDate = false;
            foreach (['O', 'A', 'B', 'H', 'C', 'D'] as $shift) {
                // Set properties
                // 1. Push all ShiftAppObjects
                foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                    if ($this->arrMemberApplicationsByIdUser[$id_user][$date . $shift] === '1') {
                        // echo "\$this->arrMemberApplicationsByIdUser[$id_user][$date$shift] = '1'<br><br>";
                        if ($shift === 'O') {
                            echo "$id_user $date Now O! <br>";
                            foreach ($this->arrayShiftsByPart as $arrShifts) {
                                foreach ($arrShifts as $shift) {
                                    $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $date, $shift);
                                    $this->pushShiftAppObject($shiftObject);
                                }
                            }
                        } else {
                            // If person applied for this
                            $shiftObject = $this->genShiftAppObject($reflectionShiftObject, $id_user, $date, $shift);
                            $this->pushShiftAppObject($shiftObject);
                        }
                        $appliedForDate = true;
                    }
                }
                // 2. Initialize properties
                // $this->arrDateShiftsHandlerByDate[$date]->init();
            }
            $this->updateNumDaysApplied($id_user, $appliedForDate);
            // Update 
        }
        // echo 'keys of arrShiftStatusByShift<br>';
        // var_dump(array_keys($this->arrDateShiftsHandlerByDate[16]->arrShiftStatusByShift));
        // echo '<br>';
        $this->arrDateShiftsHandlerByDate[16]->deployShift();
        $this->arrDateShiftsHandlerByDate[16]->deployShift();
        // var_dump($this->arrDateShiftsHandlerByDate[16]->arrNumLangsAppByPart);
        // echo '<br>';
        // var_dump($this->arrDateShiftsHandlerByDate[16]->arrScoresByIdUser);
        // echo '<br><br>';
        // echo 'arrShiftAppObjectsByIdUser: <br>';
        // var_dump($this->arrDateShiftsHandlerByDate[16]->arrShiftAppObjectsByIdUser);
        // var_dump($this->arrDateShiftsHandlerByDate['2020-02-10']->arrShiftAppObjectsByIdUser);
        // var_dump(($this->arrDateShiftsHandlerByDate)['2020-02-16']);
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

    private function genShiftAppObject($reflectionShiftObject, $id_user, $date, $shift)
    {
        $shiftObject = $reflectionShiftObject->newInstanceWithoutConstructor();
        $shiftObject->id_user = $id_user;
        $shiftObject->date_shift = $date;
        $shiftObject->shift = $shift;
        $shiftObject->__construct($this->arrayShiftsByPart);
        $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
        return $shiftObject;
    }

    private function pushShiftAppObject($shiftObject)
    {
        // To MemberObject->arrShiftAppObjects
        $shiftObject->memberObject->pushShiftAppObjects($shiftObject);
        // To DateShiftsHandler->arrShiftAppObjectsByIdUser
        $this->arrDateShiftsHandlerByDate[$shiftObject->date_shift]->pushShiftAppObject($shiftObject);
        // To ShiftStatus->arrShiftAppObjectsByIdUser
        $this->arrDateShiftsHandlerByDate[$shiftObject->date_shift]->pushShiftAppObjectToShiftStatus($shiftObject);
    }

    private function chooseMemberToDistribute()
    {
    }

    private function distributeShift()
    {
    }

    private function distributeShiftsOfDate()
    {
        // Choose member
        $this->chooseMemberToDistribute();
        // Distribute date
        $this->distributeShift();
    }
}

class DateShiftsHandler extends DateObject
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
        // echo "Pushing shiftAppObject to DateShiftsHandler... $shiftObject->date_shift $shiftObject->shift<br>";
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

    public function deployShift()
    {
        // Calc scores
        $this->setArrScoresByIdUser();

        // Select member and shift
        $id_user_seleted = $this->getMemberToDeploy();
        $shiftObjectDeployed = $this->deployShiftOfMember($id_user_seleted);

        // Unset Member from candidates i.e. Unset from DateShiftsHandler::arrShiftAppObjects
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

            // Unset from ShiftStatus::arrShiftAppObjects. For DateShiftsHandler, already done above.
            $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->unsetShiftAppObjectsByIdUser($shiftObjectDeployed);

            // Update props for ShiftStatus
            $this->arrShiftStatusByShift[$shiftObjectDeployed->shift]->updateProps();
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
            if ($this->arrShiftStatusByShift[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shift]->percentage >= 1) {
                echo 'This shift is already full.<br>';
                echo 'percentage = ' . $this->arrShiftStatusByShift[$this->arrShiftAppObjectsByIdUser[$id_user_seleted][0]->shift]->percentage . '<br>';
                // Unset this shiftApp from DateShiftsHandler::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
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
            // check splited part?
            $arrKeyPartsApp = [];
            $arrShiftAppObjectsByPart = [];
            foreach ($this->arrShiftAppObjectsByIdUser[$id_user_seleted] as $key => $shiftObject) {
                // Check if this shift is already filled out.
                if ($this->arrShiftStatusByShift[$shiftObject->shift]->percentage >= 1) {
                    echo 'This shift is already full.<br>';
                    // Unset this shiftApp from DateShiftsHandler::arrShiftAppObjectsByIdUser and ShiftStatus::arrShiftAppObjectsByIdUser. This can no longer be used.
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
            if (count($arrKeyPartsApp) === 0) {
                // This member is out.
                return false;
            } elseif (count($arrKeyPartsApp) === 1) {
                // part already selected: $part
                $arrShiftObjectsFiltered = $this->arrShiftAppObjectsByIdUser[$id_user_seleted];
            } else {
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
                    $arrLangPercentageByPart = [];
                    foreach (array_keys($arrKeyPartsApp) as $part) {
                        $arrLangs = $this->config_handler->arrayLangsByPart[$part];
                        $lang = array_keys($arrLangs)[$i];
                        if (!in_array($lang, $arrLingualities) || $arrLangs[$lang] === NULL) {
                            // Search for next part
                            continue;
                        }
                        if (!isset($this->arrayNumLangsByPart[$part][$lang])) {
                            $arrLangPercentageByPart[$part] = 0;
                        } else {
                            $arrLangPercentageByPart[$part] = $this->arrayNumLangsByPart[$part][$lang] / $arrLangs[$lang];
                        }
                    }
                    if (count($arrLangPercentageByPart)) {
                        if (min($arrLangPercentageByPart) < 1) {
                            // Insufficient
                            $part = array_keys($arrLangPercentageByPart, min($arrLangPercentageByPart));
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
            echo '$shiftPriority<br>';
            echo 'Before sort:<br>';
            var_dump(array_keys($this->arrShiftStatusByShift));
            echo '<br>';
            uasort($this->arrShiftStatusByShift, function ($a, $b) {
                if ($a->percentage == $b->percentage) {
                    return 0;
                }
                return ($a < $b) ? -1 : 1;
            });
            echo 'After sort:<br>';
            var_dump(array_keys($this->arrShiftStatusByShift));
            echo '<br>';
            $shiftPriority = array_keys($this->arrShiftStatusByShift);
            echo '$arrShiftObjectsFiltered';
            // echo 'Before sort:<br>';
            // var_dump($arrShiftObjectsFiltered);
            // echo '<br>';
            usort($arrShiftObjectsFiltered, function ($a, $b) use ($shiftPriority) {
                $pos_a = array_search($a->shift, $shiftPriority);
                $pos_b = array_search($b->shift, $shiftPriority);
                return $pos_a - $pos_b;
            });
            // echo 'After sort:<br>';
            // var_dump($arrShiftObjectsFiltered);
            // echo '<br>';
        } else {
            echo 'One shift passed. No sorting needed.';
        }
        return $arrShiftObjectsFiltered[0];
    }

    private function unsetShiftAppObject($shiftObjectDeployed)
    {
        // For ShiftsDistributor: No way to access 
        // For DateShiftsHandler
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
            echo 'ERROR! Shift object being deployed has already been unset from (or never been pushed into) DateShiftsHandler::arrShiftAppObjectsByIdUser.';
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
        // var_dump($this->arrScoresByIdUser);
        echo '<br>';
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
        echo "Now $score_item_name <br>";
        // var_dump($arrItemValues);
        echo '<br>';
        if ($minORmax === 'max') {
            $threshold = max($arrItemValues);
        } elseif ($minORmax === 'min') {
            $threshold = min($arrItemValues);
        } else {
            echo 'Argument not understood.';
            exit;
        }
        echo "Threshold: " . $threshold;
        echo '<br>';
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
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->setArrScores($id_user);
        }
    }

    public function setArrScores($id_user)
    {
        $this->arrScoresByIdUser = [];
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
            // echo "$this->date $shiftObject->shift" . ' Percentage:' . $this->arrShiftStatusByShift[$shiftObject->shift]->percentage . '<br>';
            if ($this->arrShiftStatusByShift[$shiftObject->shift]->percentage < 1) {
                $this->arrScoresByIdUser[$id_user]['numAppNotEnough']++;
            }
        }
    }

    private function setDeployRatio($id_user)
    {
        if ($this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied === 0) {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = INF;
        } else {
            $this->arrScoresByIdUser[$id_user]['deployRatio'] = $this->arrayMemberObjectsByIdUser[$id_user]->numDaysDeployed / $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied;
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
        // echo $shiftObject->date_shift . '<br>';
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
                // echo $shiftObject->date_shift . '<br>';
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
}

class ShiftStatus
{
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftObjectsByIdUser = [];
    public $numNeeded;
    public $numApplicants;
    public $percentage; // 0: insufficient 1: fitted 2: enough

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
        $this->percentage = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
        $this->numApplicants = count($this->arrShiftAppObjectsByIdUser);
    }
}
