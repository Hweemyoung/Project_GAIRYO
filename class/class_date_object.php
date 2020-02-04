<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/class/class_member_object.php";
require_once "$homedir/class/class_shift_object.php";
class DateObject
{
    public $date;
    public $arrayShiftObjectsByShift;
    public $arrayNumLangsByPart;
    public $enoughLangsByPart;
    public $arrBalancesByPart;
    function __construct($date, $arrayShiftObjectsOfDate, $config_handler)
    {
        $this->date = $date;
        $this->config_handler = $config_handler;
        $this->arrayLangsByPart = $config_handler->getArrayLangsByPart($date);
        $this->arrayNumLangsByPart = [];
        $this->arrayShiftObjectsByShift = [];
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
        $this->setArrayShiftObjectsByShift($arrayShiftObjectsOfDate);
        // var_dump($arrayShiftObjectsOfDate);
        $this->setArrayNumLangsByPart($arrayShiftObjectsOfDate);
        $this->setEnoughLangsByPart();
    }

    public function pushArrayNumLangsByPart($shiftObject)
    {
        // echo 'HERE pushArrayNumLangsByPart<br>';
        // echo $shiftObject->date_shift . '<br>';
        if (!isset($this->arrayNumLangsByPart[$shiftObject->shiftPart])) {
            $this->arrayNumLangsByPart[$shiftObject->shiftPart] = [];
        }
        foreach ($this->config_handler->arrayLangsShort as $lang) {
            if (intval($shiftObject->memberObject->$lang)) {
                if (!isset($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang])) {
                    $this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] = 1;
                } else {
                    $this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] += intval($shiftObject->memberObject->$lang);
                }
            }
            // $mem = $shiftObject->memberObject;
            // echo "$shiftObject->shift / $mem->id_user / $lang = " . $shiftObject->memberObject->$lang .'<br>';
        }
    }

    private function setArrayNumLangsByPart($arrayShiftObjectsOfDate)
    {
        if (count($arrayShiftObjectsOfDate)) {
            // Initialize array
            $this->arrayNumLangsByPart = [];
            foreach ($arrayShiftObjectsOfDate as $shiftObject) {
                // echo $shiftObject->date_shift . '<br>';
                $this->pushArrayNumLangsByPart($shiftObject);
            }
            ksort($this->arrayNumLangsByPart);
        }
    }

    public function pushArrayShiftObjectByShift($shiftObject)
    {
        $this->arrayShiftObjectsByShift[$shiftObject->shift][] = $shiftObject;
    }

    public function setArrayShiftObjectsByShift($arrayShiftObjectsOfDate)
    {
        if (count($arrayShiftObjectsOfDate)) {
            // Initialize array
            $this->arrayShiftObjectsByShift = [];
            foreach ($arrayShiftObjectsOfDate as $shiftObject) {
                $this->pushArrayShiftObjectByShift($shiftObject);
            }
        }
    }

    public function setEnoughLangsByPart()
    {
        // Initialize arrays
        // echo 'HERE setEnoughLangsByPart<br>';
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
        // echo '$this->arrayNumLangsByPart<br>';
        // var_dump($this->arrayNumLangsByPart);
        // echo '<br>';
        // echo $this->date .'<br>';
        if (count($this->arrayNumLangsByPart)) {
            foreach ($this->arrayLangsByPart as $shiftPart => $arrLangs) {
                $partEnough = true;
                foreach ($arrLangs as $lang => $numLangNeeded) {
                    if ($numLangNeeded === NULL) {
                        continue;
                    }
                    if (!isset($this->arrayNumLangsByPart[$shiftPart][$lang])) {
                        $balance = -$numLangNeeded;
                        $this->enoughLangsByPart[$shiftPart] = false;
                    } else {
                        $balance = $this->arrayNumLangsByPart[$shiftPart][$lang] - $numLangNeeded;
                    }
                    // Negative value means not enough
                    $this->arrBalancesByPart[$shiftPart][$lang] = $balance;
                    if ($balance < 0) {
                        $partEnough = false;
                    }
                }
                $this->enoughLangsByPart[$shiftPart] = $partEnough;
            }
        }
        $partEnough = NULL;
    }
}