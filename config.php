<?php
class ConfigHandler
{
    // Server

    // DB
    public $cols_required_members = ['id_google', 'nickname', 'first_name', 'middle_name', 'last_name'];

    // DBHandler
    public $http_host;
    public $homedir = '/var/www/html/gairyo_temp';

    // Working conditions: '0': Not JP, '1': JP
    // Per week
    public $maxWorkedMinsPerWeekByJp = ['0' => 1680, 1 => 2400];
    public $maxWorkedDaysPerWeekByJp = ['0' => 5, 1 => 7];
    // Per month
    public $maxWorkedMinsPerMonthByJp = ['0' => 6600, 1 => 10000];
    public $maxWorkedDaysPerMonthByJp = ['0' => 16, '1' => 16];

    // Languages
    public $numLangs = 8; // Includes 'other'
    public $arrayLangsShort = ['cn', 'kr', 'th', 'my', 'ru', 'fr', 'de', 'other'];
    public $arrayLangsLong = ['cn' => 'Chinese', 'kr' => 'Korean', 'th' => 'Thailand', 'my' => 'Malaysian', 'ru' => 'Russian', 'fr' => 'French', 'de' => 'Deutsche', 'other' => 'Others'];
    public $defaultArrLangsByPart = [['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]];
    public $arrLangsByDate = [16 => [['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 4, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]]];

    // Shifts
    public $numOfShiftsPart = 2;
    public $shiftsPart0 = ['A', 'B', 'H'];
    public $shiftsPart1 = ['C', 'D'];
    public $arrayPartNames = ['午前', '午後'];

    public $defaultNumMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4];
    public $arrNumMaxByShiftByDate = [];
    // public $arrNumMaxByShiftByDate = [16 => ['A' => 1, 'B' => 6, 'H' => 4, 'C' => 3, 'D' => 6]];

    public $defaultNumNeededByShift = ['H' => 1, 'C' => 1];
    public $arrNumNeededByShiftByDate = [];
    // public $arrNumNeededByShiftByDate = [16 => ['B' => 3, 'H' => 2, 'C' => 2, 'D' => 4]];

    public $defaultNumNeededByPart = [5, 4];
    public $arrNumNeededByPartByDate = [];
    // public $arrNumNeededByPartByDate = [16 => [8, 8]];


    // ConfigHandler
    public $sleepSeconds = 2;

    // DailyMembersHandler
    public $YLowerBound = 2020;
    public $dayStart = 'Mon';
    public $dayEnd = 'Sun';

    // tab_submit_shifts
    public $enableSubmit = 1;
    public $m_submit = '202002';
    public $message = 'シフト希望は2020年2月2日から受け付けます';

    // ShiftsDistributor
    public $m = '202003';
    public $arr_mshifts = [];
    public $arrScoreItems = ['appForTargetPart' => 'max', 'langScore' => 'max', 'deployRatio' => 'min'];
    // public $arrScoreItems = ['appForTargetPart' => 'max', 'numShiftAppObjects' => 'min', 'langScore' => 'max', 'deployRatio' => 'min'];
    // public $arrScoreItems = ['deployRatio' => 'min', 'appForTargetPart' => 'max', 'numAppNotEnough' => 'max', 'langScore' => 'max']; // BEST?
    // public $arrScoreItems = ['deployRatio' => 'min', 'appForTargetPart' => 'max', 'langScore' => 'max'];
    // public $arrScoreItems = ['appForTargetPart' => 'max', 'numShiftAppObjects' => 'min', 'numAppNotEnough' => 'max', 'langScore' => 'max', 'deployRatio' => 'min'];
    // public $arrScoreItems = ['numAppNotEnough' => 'min', 'langScore' => 'max', 'deployRatio' => 'min'];

    public $arrayShiftsByPart;
    public $arrayShiftTimes;

    public function __construct()
    {
        $http_host = $_SERVER['HTTP_HOST'] . '/' . 'gairyo_temp';
        $this->http_host = "http://$http_host";
        $this->arsortArrLangs();
        $this->setArrayShiftsByPart();
        $this->setArrayShiftTimes();
    }

    private function arsortArrLangs()
    {
        foreach ($this->defaultArrLangsByPart as $arrLangs) {
            arsort($arrLangs);
        }
        foreach ($this->arrLangsByDate as $arrLangsByPart) {
            foreach ($arrLangsByPart as $arrLangs) {
                arsort($arrLangs);
            }
        }
    }

    public function setArrayShiftsByPart()
    {
        $this->arrayShiftsByPart = [$this->shiftsPart0, $this->shiftsPart1];
    }

    public function setArrayShiftTimes()
    {
        $shiftA = array('workingMins' => 260, 'time-start' => '07:40', 'time-end' => '12:00', 'btn-color' => 'btn-info');
        $shiftB = array('workingMins' => 330, 'time-start' => '08:00', 'time-end' => '13:30', 'btn-color' => 'btn-secondary');
        $shiftH = array('workingMins' => 300, 'time-start' => '08:00', 'time-end' => '13:00', 'btn-color' => 'btn-success');
        $shiftC = array('workingMins' => 330, 'time-start' => '12:30', 'time-end' => '18:00', 'btn-color' => 'btn-dark text-light');
        $shiftD = array('workingMins' => 270, 'time-start' => '13:30', 'time-end' => '18:00', 'btn-color' => 'btn-sky');
        $this->arrayShiftTimes = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);
    }

    public function set_arr_mshifts()
    {
        foreach (range(1, 31) as $j) {
            foreach ($this->arrayShiftsByPart as $arrShifts) {
                foreach ($arrShifts as $shift) {
                    array_push($this->arr_mshifts, $j . $shift);
                }
                // Don't forget 'O'
                array_push($this->arr_mshifts, $j . 'O');
            }
        }
        return $this;
    }

    public function getNumNeededByPart($date, $shiftPart)
    {
        if (isset($this->arrNumNeededByPartByDate[$date])) {
            return $this->arrNumNeededByPartByDate[$date][$shiftPart];
        } elseif ($this->defaultNumNeededByPart[$shiftPart]) {
            return $this->defaultNumNeededByPart[$shiftPart];
        } else {
            echo 'numNeededByPart cannot be NULL!';
            exit;
        }
    }

    public function getNumMaxByShift($date, $shift)
    {
        if (isset($this->arrNumMaxByShiftByDate[$date][$shift])) {
            return $this->arrNumMaxByShiftByDate[$date][$shift];
        } elseif (isset($this->defaultNumMaxByShift[$shift])) {
            return $this->defaultNumMaxByShift[$shift];
        } else {
            return NULL;
        }
    }

    public function getNumNeededByShift($date, $shift)
    {
        if (isset($this->arrNumNeededByShiftByDate[$date][$shift])) {
            return $this->arrNumNeededByShiftByDate[$date][$shift];
        } elseif (isset($this->defaultNumNeededByShift[$shift])) {
            return $this->defaultNumNeededByShift[$shift];
        } else {
            return NULL;
        }
    }

    public function getArrayLangsByPart($date)
    {
        if (isset($this->arrLangsByDate[$date])) {
            return $this->arrLangsByDate[$date];
        } else {
            return $this->defaultArrLangsByPart;
        }
    }
}

$config_handler = new ConfigHandler();
