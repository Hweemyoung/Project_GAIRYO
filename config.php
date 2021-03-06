<?php
class ConfigHandler
{
    // シフトごとの最多人数
    public $defaultNumMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4]; // デフォルト値
    public $arrNumMaxByShiftByDate = []; // 指定しない場合 右辺を[]にすること。
    // public $arrNumMaxByShiftByDate = [16 => ['A' => 1, 'B' => 6, 'H' => 4, 'C' => 3, 'D' => 6]]; // 特定日付のシフトごとの最多人数

    // シフトごとの最低限人
    public $defaultNumNeededByShift = ['H' => 1, 'C' => 1];
    public $arrNumNeededByShiftByDate = []; // デフォルト値
    // public $arrNumNeededByShiftByDate = [16 => ['B' => 3, 'H' => 2, 'C' => 2, 'D' => 4]]; // 特定日付のシフトごとの最低限人数
    
    // パートごとの定員
    public $defaultNumNeededByPart = [6, 4]; // デフォルト値
    public $arrNumNeededByPartByDate = []; // 指定しない場合 右辺を[]にすること。
    // public $arrNumNeededByPartByDate = [16 => [8, 8]]; // 特定日付のパートごとの定員

    // 言語
    public $defaultArrLangsByPart = [['cn' => 2], ['cn' => 2]]; // デフォルト値
    public $arrLangsByDate = [];
    // public $arrLangsByDate = [16 => [['kr' => 3, 'de' => 3], []]]; // 指定しない場合 右辺を[]にすること。

    // 労働規制
    // 日本人・外国人の最大連続出社日
    public $maxConsecutiveWorkedDatesByJp = ['0' => 2, '1' => 2];
    // 日本人・外国人の週間最長労働時間(分)
    public $maxWorkedMinsPerWeekByJp = ['0' => 1600, '1' => 1600];
    // 日本人・外国人の週間最多労働日数
    public $maxWorkedDaysPerWeekByJp = ['0' => 5, '1' => 5];
    // 日本人・外国人の月間最長労働時間
    public $maxWorkedMinsPerMonthByJp = ['0' => 12000, '1' => 12000];
    // 日本人・外国人の月間最多労働日数
    public $maxWorkedDaysPerMonthByJp = ['0' => 20, '1' => 20];

    #######以下は触らないでください#######

    // Working conditions: '0': Not JP, '1': JP
    // Per week
    // public $maxWorkedMinsPerWeekByJp = ['0' => 1680, 1 => 2400];
    // public $maxWorkedDaysPerWeekByJp = ['0' => 5, 1 => 7];
    // Per month
    // public $maxWorkedMinsPerMonthByJp = ['0' => 6600, 1 => 10000];
    // public $maxWorkedDaysPerMonthByJp = ['0' => 16, '1' => 16];
    
    // ShiftsDistributor
    public $m = '202002';

    // Languages
    public $numLangs = 8; // Includes 'other'
    public $arrayLangsShort = ['cn', 'kr', 'th', 'my', 'ru', 'fr', 'de', 'other'];
    public $arrayLangsLong = ['cn' => 'Chinese', 'kr' => 'Korean', 'th' => 'Thailand', 'my' => 'Malaysian', 'ru' => 'Russian', 'fr' => 'French', 'de' => 'Deutsche', 'other' => 'Others'];
    // public $defaultArrLangsByPart = [['cn' => 2], ['cn' => 2]];
    // public $arrLangsByDate = [16 => [['kr' => 3, 'de' => 3], []]];

    // Shifts
    public $numOfShiftsPart = 2;
    public $shiftsPart0 = ['A', 'B', 'H'];
    public $shiftsPart1 = ['C', 'D'];
    public $arrayPartNames = ['午前', '午後'];

    // public $defaultNumMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4];
    // public $arrNumMaxByShiftByDate = [];
    // // public $arrNumMaxByShiftByDate = [16 => ['A' => 1, 'B' => 6, 'H' => 4, 'C' => 3, 'D' => 6]];

    // public $defaultNumNeededByShift = ['H' => 1, 'C' => 1];
    // public $arrNumNeededByShiftByDate = [];
    // // public $arrNumNeededByShiftByDate = [16 => ['B' => 3, 'H' => 2, 'C' => 2, 'D' => 4]];

    // public $defaultNumNeededByPart = [5, 4];
    // public $arrNumNeededByPartByDate = [];
    // // public $arrNumNeededByPartByDate = [16 => [8, 8]];

    // Server

    // DB
    public $cols_required_members = ['id_google', 'nickname', 'first_name', 'middle_name', 'last_name'];

    // DBHandler
    public $http_host;
    public $homedir = '/home/vol15_8/epizy.com/epiz_24956964/htdocs';
    // /home/vol15_8/epizy.com/epiz_24956964/htdocs
    
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
        $http_host = $_SERVER['HTTP_HOST'];
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
        if (isset($this->arrLangsByDate[intval($date)])) {
            return $this->arrLangsByDate[intval($date)];
        } else {
            return $this->defaultArrLangsByPart;
        }
    }
}

$config_handler = new ConfigHandler();
