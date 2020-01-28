<?php
class ShiftStatus
{
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftObjectsByIdUser = [];
    // public $numNeeded;
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