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
        $this->addPropsToMemberObjects(); // memberObject->numDaysApplied = 0,memberObject->numDaysDeployed = 0
    }

    private function addPropsToMemberObjects()
    {
        foreach ($this->arrayMemberObjectsByIdUser as $memberObject) {
            $memberObject->numDaysApplied = 0;
            $memberObject->numDaysDeployed = 0;
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
            $appliedForDate = false;
            foreach (['O', 'A', 'B', 'H', 'C', 'D'] as $shift) {
                $this->arrDateShiftsHandlerByDate[$date] = new DateShiftsHandler($date, $this->master_handler, $this->config_handler);
                // Set properties
                // 1. Push all ShiftAppObjects
                foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                    if ($this->arrMemberApplicationsByIdUser[$id_user][$date . $shift] === '1') {
                        // echo "\$this->arrMemberApplicationsByIdUser[$id_user][$date$shift] = '1'<br><br>";
                        if ($shift === 'O') {
                            echo "$id_user $date Now O! <br>";
                            foreach ($this->arrayShiftsByPart as $arrShifts) {
                                foreach ($arrShifts as $shift) {
                                    $this->genAndPushArrShiftAppObjects($reflectionShiftObject, $id_user, $date, $shift);
                                }
                            }
                        } else {
                            // If person applied for this
                            $this->genAndPushArrShiftAppObjects($reflectionShiftObject, $id_user, $date, $shift);
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
        $this->arrDateShiftsHandlerByDate[16]->init();
        var_dump($this->arrDateShiftsHandlerByDate[16]->arrScoresByIdUser);
        echo '<br><br>';
        var_dump($this->arrDateShiftsHandlerByDate[16]->arrShiftAppObjectsByIdUser);
        // var_dump($this->arrDateShiftsHandlerByDate['2020-02-10']->arrShiftAppObjectsByIdUser);
        // var_dump(($this->arrDateShiftsHandlerByDate)['2020-02-16']);
    }

    private function orig_setArrDateShiftsHandlerByDate()
    {
        $this->arrDateShiftsHandlerByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        foreach ($this->arr_mshifts as $mshift) {
            $shift = substr($mshift, -1); // 'B'
            $date = substr($this->m, 0, 4) . '-' . substr($this->m, -2, 2) . '-' . str_pad(substr($mshift, 0, -1), 2, '0', STR_PAD_LEFT); // '2020-01-20'
            // echo substr($mshift, 0, -1) .'<br>';
            // echo $date .'<br>';
            $this->arrDateShiftsHandlerByDate[$date] = new DateShiftsHandler($date, $this->master_handler, $this->config_handler);

            // Set properties
            // 1. Push all ShiftAppObjects
            foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                if ($this->arrMemberApplicationsByIdUser[$id_user][$mshift] === '1') {
                    if ($shift === 'O') {
                        $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied++;
                        foreach (['A', 'B', 'H', 'C', 'D'] as $shift) {
                            $this->genAndPushArrShiftAppObjects($reflectionShiftObject, $id_user, $date, $shift);
                        }
                    } else {
                        // If person applied for this
                        $this->genAndPushArrShiftAppObjects($reflectionShiftObject, $id_user, $date, $shift);
                    }
                }
            }
            // 2. Initialize properties
            // $this->arrDateShiftsHandlerByDate[$date]->init();
        }
        $this->arrDateShiftsHandlerByDate['2020-02-16']->init();
        var_dump($this->arrDateShiftsHandlerByDate['2020-02-16']->arrShiftAppObjectsByIdUser);
        // var_dump($this->arrDateShiftsHandlerByDate['2020-02-10']->arrShiftAppObjectsByIdUser);
        // var_dump(($this->arrDateShiftsHandlerByDate)['2020-02-16']);
    }

    private function updateNumDaysApplied($id_user, $appliedForDate)
    {
        // echo '<br>';
        // echo $id_user;
        // echo '<br>';
        if ($appliedForDate){

            // echo $id_user . ' Before: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
            $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied++;
            // echo $id_user . ' After: ' . $this->arrayMemberObjectsByIdUser[$id_user]->numDaysApplied . '<br>';
        }
    }

    private function genAndPushArrShiftAppObjects($reflectionShiftObject, $id_user, $date, $shift)
    {
        $shiftObject = $reflectionShiftObject->newInstanceWithoutConstructor();
        $shiftObject->id_user = $id_user;
        $shiftObject->date_shift = $date;
        $shiftObject->shift = $shift;
        $shiftObject->__construct($this->arrayShiftsByPart);
        $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
        $shiftObject->setShiftPart($this->arrayShiftsByPart);

        $this->arrDateShiftsHandlerByDate[$date]->pushShiftAppObjectsToShiftStatus($shiftObject);
        $this->arrDateShiftsHandlerByDate[$date]->pushArrShiftAppObjectsByIdUser($shiftObject);
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
    public $arrNumLangsAppByPart = [];
    public $arrShiftStatusByShift = [];
    public $arrShiftAppObjectsByIdUser = [];
    public $arrScoresByIdUser = [];
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
    }

    public function pushShiftAppObjectsToShiftStatus($shiftObject)
    {
        // This method is called explicitly in ShiftsDistributor instance.
        if (!isset($this->arrShiftStatusByShift[$shiftObject->shift])) {
            $this->arrShiftStatusByShift[$shiftObject->shift] = new ShiftStatus($shiftObject->shift, $this->config_handler);
        }
        // echo '$arrShiftStatusByShift = ';
        // var_dump($this->arrShiftStatusByShift);
        // echo '<br>';
        $this->arrShiftStatusByShift[$shiftObject->shift]->pushArrShiftObjectsByIdUser($shiftObject);
        $this->arrShiftStatusByShift[$shiftObject->shift]->updateProps();
    }

    public function pushArrShiftAppObjectsByIdUser($shiftObject)
    {
        if (!isset($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user])) {
            $this->arrShiftAppObjectsByIdUser[$shiftObject->id_user] = [];
        }
        array_push($this->arrShiftAppObjectsByIdUser[$shiftObject->id_user], $shiftObject);
    }

    public function init()
    {
        // Call after setting arrShiftStatusByShift
        // Updates arrNumLangsAppByPart, arrScoresByIdUser
        $this->setArrNumLangsAppByPart();
        $this->setArrScoresByIdUser();
    }

    public function deployShift()
    {
        $id_user_seleted = $this->getMemberToDeploy();
        $this->deployShiftOfMember($id_user_seleted);
    }

    private function deployShiftOfMember($id_user_seleted)
    {
    }

    public function getMemberToDeploy()
    {
        // Call after setting arrScoresByIdUser
        foreach ($this->arrScoreItems as $score_item_name => $minORmax) {
            if (count($this->arrScoreItems) > 1) {
                // Every step, $this->arrScoreItems is shrinked.
                $this->genAndFilterArrScoresByIdUser($score_item_name, $minORmax);
            } else {
                break;
            }
        }
        $id_user_seleted = array_keys($this->arrScoresByIdUser)[mt_rand(0, count($this->arrScoreItems) - 1)];
        return $id_user_seleted;
    }

    private function genAndFilterArrScoresByIdUser(string $score_item_name, string $minORmax)
    {
        $arrItemValues = [];
        foreach ($this->arrScoresByIdUser as $id_user => $arrScores) {
            $arrItemValues[$id_user] = $arrScores[$score_item_name];
        }
        if ($minORmax === 'max') {
            $$minORmax = max($arrItemValues);
        } elseif ($minORmax === 'min') {
            $$minORmax = min($arrItemValues);
        } else {
            echo 'Argument not understood.';
            exit;
        }
        $arrItemValues = array_filter($arrItemValues, function ($var, $minORmax) {
            return $var === $minORmax;
        });
        $newArrScoresByIdUser = [];
        foreach (array_keys($arrItemValues) as $id_user) {
            $newArrScoresByIdUser[$id_user] = $this->arrScoresByIdUser[$id_user];
        }
        $this->arrScoresByIdUser = $newArrScoresByIdUser;
    }

    public function setArrScoresByIdUser()
    {
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->setArrScores($id_user);
        }
    }

    public function setArrScores($id_user)
    {
        $this->setNumShiftAppObjects($id_user);
        $this->setNumAppNotEnough($id_user);
        $this->setLangScore($id_user);
        $this->setDeployRatio($id_user);
    }

    private function setNumShiftAppObjects($id_user){
        $this->arrScoresByIdUser[$id_user]['numShiftAppObjects'] = count($this->arrShiftAppObjectsByIdUser[$id_user]);
    }

    private function setNumAppNotEnough($id_user){
        $this->arrScoresByIdUser[$id_user]['numAppNotEnough'] = 0;
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            if ($this->arrShiftStatusByShift[$shiftObject->shift]->percentage < 1) {
                $this->arrScoresByIdUser[$id_user]['numAppNotEnough']++;
            }
        }
    }

    private function setDeployRatio($id_user){
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
                                if ($shiftObject->$lang === '1') {
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
                foreach ($shiftStatus->arrShiftObjectsByIdUser as $arrShiftObjects) {
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

    public function pushArrShiftObjectsByIdUser($shiftObject)
    {
        // var_dump($shiftObject);
        // echo '<br>';
        // var_dump($this->arrShiftObjectsByIdUser);
        if (!isset($this->arrShiftObjectsByIdUser[$shiftObject->id_user])) {
            $this->arrShiftObjectsByIdUser[$shiftObject->id_user] = [];
        }
        array_push($this->arrShiftObjectsByIdUser[$shiftObject->id_user], $shiftObject);
    }

    public function updateProps()
    {
        // Call after loop of pushArrShiftObjectsByIdUser method.
        // Update properties
        $this->percentage = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
        $this->numApplicants = count($this->arrShiftObjectsByIdUser);
    }
}
