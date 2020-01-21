<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/config.php";

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
        $this->Ym = $config_handler->Ym;
        $this->arr_mshifts = $config_handler->set_arr_mshifts()->arr_mshifts;
        $this->init();
    }

    private function init()
    {
        $this->arrDateShiftsHandlerByDate = [];
    }

    public function process()
    {
        $this->executeSql('START TRANSACTION;');
        $this->loadApplications();
        $this->setArrDateShiftsHandlerByDate();
    }

    private function loadApplications()
    {
        $sql = "SELECT id_user, " . implode(', ', $this->arr_mshifts) . " FROM shifts_submitted WHERE Ym='$this->Ym'";
        $stmt = $this->querySql($sql);
        $this->arrMemberApplicationsByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        // Some values (e.g. 31st) could be NULL
        $stmt->closeCursor();
        return $this;
    }

    private function setArrDateShiftsHandlerByDate()
    {
        $this->arrDateShiftsHandlerByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        foreach ($this->arr_mshifts as $mshift) {
            $shift = substr($mshift, -1); // 'B'
            $date = substr($this->Ym, 0, 4) . '-' . substr($this->Ym, -2, 2) . '-' . str_pad(substr($mshift, 0, -1), 2, '0'); // '2020-01-20'
            $this->arrDateShiftsHandlerByDate[$date] = new DateShiftsHandler($date, $this->config_handler);

            // Set properties
            // 1. Push all ShiftAppObjects
            foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                if ($this->arrMemberApplicationsByIdUser[$id_user][$mshift] === '1') {
                    if ($shift === 'O') {
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
            $this->arrDateShiftsHandlerByDate[$date]->init();
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

        $this->arrDateShiftsHandlerByDate[$date]->pushArrShiftAppObjectsByShift($shiftObject);
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
    public $arrShiftAppObjectsByShift = [];
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftStatus = [];
    public $arrScoresByIdUser = [];
    public $arrMemberObjectsByIdUser = [];
    public function __construct($date, $config_handler)
    {
        $this->date = $date;
        $this->config_handler = $config_handler;
        $this->arrayNumLangsByPart = [];
        $this->arrayShiftObjectsByShift = [];
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
    }



    public function pushArrShiftAppObjectsByShift($shiftObject)
    {
        if (!isset($this->arrShiftAppObjectsByShift[$shiftObject->shift])) {
            $this->arrShiftAppObjectsByShift[$shiftObject->shift] = new ShiftStatus($shiftObject->shift, $this->config_handler);
        }
        $this->arrShiftAppObjectsByShift[$shiftObject->shift]->pushArrShiftObjectsByIdUser($shiftObject);
    }

    public function init()
    {
        // Call after setting arrShiftAppObjectsByShift
        $this->setArrNumLangsAppByPart($this->arrShiftAppObjectsByShift);
    }

    public function updateArrScoresByIdUser()
    {
        foreach (array_keys($this->arrShiftAppObjectsByIdUser) as $id_user) {
            $this->updateArrScores($id_user);
        }
    }

    public function updateArrScores($id_user)
    {
        $this->arrScoresByIdUser[$id_user]['numShiftAppObjects'] = count($this->arrShiftAppObjectsByIdUser[$id_user]);
        $this->arrScoresByIdUser[$id_user]['numNotEnough'];
        $this->arrScoresByIdUser[$id_user]['numFit'];
        $this->arrScoresByIdUser[$id_user]['numEnough'];
        $this->arrScoresByIdUser[$id_user]['langScore'] = $this->calcLangScore($id_user);
    }

    private function calcLangScore($id_user)
    {
        $arr = [];
        foreach ($this->arrShiftAppObjectsByIdUser[$id_user] as $shiftObject) {
            foreach ($this->config_handler->arrayLangsShort as $lang) {
                // For every lang
                if (isset($arr[$shiftObject->part][$lang])) {
                    // If the lang in this part already considered
                } else {
                    if ($this->config_handler->arrayLangsByPart[$shiftObject->part][$lang] !== NULL) {
                        // If the lang is required in this part
                        if (isset($this->arrayNumLangsByPart[$shiftObject->part][$lang])) {
                            // If there is already some people who can speak lang in this part
                            if ($this->arrayNumLangsByPart[$shiftObject->part][$lang] < $this->config_handler->arrayLangsByPart[$shiftObject->part][$lang]) {
                                // If not enough num
                                if ($shiftObject->$lang === '1') {
                                    // If member can speak lang
                                    $arr[$shiftObject->part][$lang] = true;
                                } else {
                                    $arr[$shiftObject->part][$lang] = false;
                                }
                            }
                        } else {
                            if ($shiftObject->$lang === '1') {
                                // If member can speak lang
                                $arr[$shiftObject->part][$lang] = true;
                            } else {
                                $arr[$shiftObject->part][$lang] = false;
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

        return $langScore;
    }

    public function pushArrNumLangsAppByPart($shiftObject)
    {
        // echo $shiftObject->date_shift . '<br>';
        if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart])) {
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart] = [];
        }
        foreach (array_keys($this->config_handler->arrayLangsShorts) as $lang) {
            if (!isset($this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang])) {
                $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] = 0;
            }
            // $mem = $shiftObject->memberObject;
            // echo "$shiftObject->shift / $mem->id_user / $lang = " . $shiftObject->memberObject->$lang .'<br>';
            $this->arrNumLangsAppByPart[$shiftObject->shiftPart][$lang] += $shiftObject->memberObject->$lang;
        }
    }

    private function setArrNumLangsAppByPart($arrShiftAppObjectsByShift)
    {
        if (count($arrShiftAppObjectsByShift)) {
            // Initialize array
            $this->arrNumLangsAppByPart = [];
            foreach ($arrShiftAppObjectsByShift as $arrShiftObjects) {
                // echo $shiftObject->date_shift . '<br>';
                foreach ($arrShiftObjects as $shiftObject) {
                    $this->pushArrayNumLangsByPart($shiftObject);
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
        $this->numNeeded = $this->config_handler->arrayLangsByPart[$this->part];
    }

    private function setPart()
    {
        foreach (array_keys($this->config_handler->arrayShiftsByPart) as $part) {
            if (in_array($this->shift, $this->config_handler->arrayShiftsByPart[$part])) {
                $this->part = $this->config_handler->arrayShiftsByPart;
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
        if (!isset($this->arrShiftObjectsByIdUser[$shiftObject->id_user])) {
            $this->arrShiftObjectsByIdUser[$shiftObject->id_user] = [];
        }
        array_push($this->arrShiftObjectsByIdUser[$shiftObject->id_user], $shiftObject);
        // Update status
        $this->updateProps();
    }

    private function updateProps()
    {
        $this->percentage = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
        $this->numApplicants = count($this->arrShiftObjectsByIdUser);
    }
}
