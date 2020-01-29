<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";

class ShiftPartStatus
{
    public $numNeeded;
    public $arrLangs;
    public $arrShiftAppObjectsByIdUser;
    public $arrShiftObjectsByIdUser;
    // public $numNeeded;
    public $numApplicants;
    public $vacancy; // <1: insufficient int(1): full. Always defined.
    public $percentApp; // <1: insufficient int(1): fitted >1: Over. Always defined.
    public function __construct($shiftPart, $date, $config_handler)
    {
        $this->shiftPart = $shiftPart;
        $this->numNeeded = $config_handler->getNumNeeded($date, $shiftPart);
        $this->arrLangs = $config_handler->getArrayLangsByPart($date)[$shiftPart];
        $this->arrShiftAppObjectsByIdUser = [];
        $this->arrShiftObjectsByIdUser = [];
        $this->arrNumLangs = [];
        $this->initArrNumLangs();
    }

    private function initArrNumLangs()
    {
        foreach ($this->arrLangs as $lang => $numLangNeeded) {
            if ($numLangNeeded !== NULL) {
                $arrNumLangs[$lang] = 0;
            }
        }
    }

    public function addNumLangs($memberObject)
    {
        foreach ($this->arrLangs as $lang => $numLangNeeded) {
            if ($numLangNeeded !== NULL) {
                $arrNumLangs[$lang] += intval($memberObject->$lang);
            }
        }
    }

    public function pushArrShiftAppObjectsByIdUser($shiftObject)
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
        // Call after loop of pushArrShiftAppObjectsByIdUser method.
        // Update properties
        $this->setVacancy();
        $this->setPercentApp();
        $this->numApplicants = count($this->arrShiftAppObjectsByIdUser);
    }

    private function setVacancy()
    {
        // echo $this->numNeeded;
        if (count($this->arrShiftObjectsByIdUser) === $this->numNeeded) {
            $this->vacancy = 1;
        } else {
            $this->vacancy = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
        }
    }

    private function setPercentApp()
    {
        if (count($this->arrShiftAppObjectsByIdUser) === ($this->numNeeded - count($this->arrShiftObjectsByIdUser))) {
            $this->percentApp = 1;
        } else {
            $this->percentApp = count($this->arrShiftAppObjectsByIdUser) / ($this->numNeeded - count($this->arrShiftObjectsByIdUser));
        }
    }
}
