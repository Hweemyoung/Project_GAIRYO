<?php
class ConfigHandler{
    public $arrayShiftsByPart;
    public $arrayShiftTimes;
    
    // Languages
    public $numLangs = 8; // Includes 'other'
    public $arrayLangsShort = ['cn', 'kr', 'th', 'my', 'ru', 'fr', 'de', 'other'];
    public $arrayLangsLong = ['Chinese', 'Korean', 'Thailand', 'Malaysian', 'Russian', 'French', 'Deutsche', 'Others'];
    
    // Shifts
    public $numOfShiftsPart = 2;
    public $shiftsPart0 = ['A', 'B', 'H'];
    public $shiftsPart1 = ['C', 'D'];

    public function __construct() {
        $this->arrayShiftsByPart = [$this->shiftsPart0, $this->shiftsPart1];
    }

    public function setArrayShiftsByPart(){
        $this->arrayShiftsByPart = [$this->shiftsPart0, $this->shiftsPart1];
    }

    public function setArrayShiftTimes(){
        $shiftA = array('time-start' => '07:40', 'time-end' => '12:00', 'btn-color' => 'btn-info');
        $shiftB = array('time-start' => '08:00', 'time-end' => '13:30', 'btn-color' => 'btn-secondary');
        $shiftH = array('time-start' => '08:00', 'time-end' => '13:00', 'btn-color' => 'btn-success');
        $shiftC = array('time-start' => '12:30', 'time-end' => '18:00', 'btn-color' => 'btn-dark text-light');
        $shiftD = array('time-start' => '13:30', 'time-end' => '18:00', 'btn-color' => 'btn-sky');
        $this->arrayShiftTimes = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);
    }
}

$config_handler = new ConfigHandler();
?>