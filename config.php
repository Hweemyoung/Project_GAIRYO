<?php
class ConfigHandler
{
    // Server
    // public $host = 'sql304.epizy.com';
    // public $DBName = 'epiz_24956964_gairyo';
    // public $userName = 'epiz_24956964';
    // public $pw = 'STZDGxr4iOPDhv';

    // DB
    public $cols_required_members = ['id_google', 'nickname', 'first_name', 'middle_name', 'last_name'];

    // DBHandler
    public $http_host;
    public $homedir = '/var/www/html/gairyo_temp';

    // Languages
    public $numLangs = 8; // Includes 'other'
    public $arrayLangsShort = ['cn', 'kr', 'th', 'my', 'ru', 'fr', 'de', 'other'];
    public $arrayLangsLong = ['cn' => 'Chinese', 'kr' => 'Korean', 'th' => 'Thailand', 'my' => 'Malaysian', 'ru' => 'Russian', 'fr' => 'French', 'de' => 'Deutsche', 'other' => 'Others'];
    public $defaultArrLangsByPart = [['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]];
    public $arrLangsByDate = [];

    // Shifts
    public $numOfShiftsPart = 2;
    public $shiftsPart0 = ['A', 'B', 'H'];
    public $shiftsPart1 = ['C', 'D'];
    public $arrayPartNames = ['午前', '午後'];
    public $numMaxByShift = ['A' => 1, 'B' => 4, 'H' => 2, 'C' => 2, 'D' => 4];
    public $numNeededByShift = ['H' => 1, 'C' => 1];
    public $defaultNumNeededByPart = [5, 4];
    public $arrNumNeededByDate = [];

    // ConfigHandler
    public $sleepSeconds = 2;

    // DailyMembersHandler
    public $YLowerBound = 2020;
    public $dayStart = 'Mon';
    public $dayEnd = 'Sun';

    // ShiftsDistributor
    public $m = '202002';
    public $arr_mshifts = [];
    public $arrScoreItems = ['appForTargetPart' => 'max', 'numShiftAppObjects' => 'min', 'langScore' => 'max', 'deployRatio' => 'min'];
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

    private function arsortArrLangs(){
        foreach ($this->defaultArrLangsByPart as $arrLangs) {
            arsort($arrLangs);
        }
        foreach($this->arrLangsByDate as $arrLangsByPart){
            foreach($arrLangsByPart as $arrLangs){
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
        $shiftA = array('time-start' => '07:40', 'time-end' => '12:00', 'btn-color' => 'btn-info');
        $shiftB = array('time-start' => '08:00', 'time-end' => '13:30', 'btn-color' => 'btn-secondary');
        $shiftH = array('time-start' => '08:00', 'time-end' => '13:00', 'btn-color' => 'btn-success');
        $shiftC = array('time-start' => '12:30', 'time-end' => '18:00', 'btn-color' => 'btn-dark text-light');
        $shiftD = array('time-start' => '13:30', 'time-end' => '18:00', 'btn-color' => 'btn-sky');
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

    public function getNumNeeded($date, $shiftPart)
    {
        if (isset($this->arrNumNeededByDate[$date])) {
            return $this->arrNumNeededByDate[$date][$shiftPart];
        } else {
            return $this->defaultNumNeededByPart[$shiftPart];
        }
    }

    public function getArrayLangsByPart($date){
        if (isset($this->arrLangsByDate[$date])) {
            return $this->arrLangsByDate[$date];
        } else {
            return $this->defaultArrLangsByPart;
        }
    }
}

$config_handler = new ConfigHandler();
