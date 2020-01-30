<?php
class ShiftStatus
{
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftObjectsByIdUser = [];
    public $numMax; // Could be NULL
    public $numNeeded;// Could be NULL
    public $numApplicants;
    public $vacancy; // <1: vacant or unlimited int(1): full.
    public $ratioMin; // <1: insufficient int(1): fitted. Could be INF

    public function __construct($date, $shift, $shiftPart, $config_handler)
    {
        $this->shift = $shift;
        $this->shiftPart = $shiftPart;
        $this->config_handler = $config_handler;
        $this->init($date, $shift);
    }

    private function init($date, $shift)
    {
        // Not set if not exist.
        $this->numMax = $this->config_handler->getNumMaxByShift($date, $shift);
        $this->numNeeded = $this->config_handler->getNumNeededByShift($date, $shift);
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
        $this->setRatioMin();
    }

    private function setVacancy()
    {
        if ($this->numMax !== NULL){
            if (count($this->arrShiftObjectsByIdUser) === $this->numMax) {
                $this->vacancy = 1;
            } else {
                $this->vacancy = count($this->arrShiftObjectsByIdUser) / $this->numMax;
            }
        } else {
            $this->vacancy = 0;
        }
    }

    private function setRatioMin(){
        if ($this->numNeeded !== NULL){
            if (count($this->arrShiftObjectsByIdUser) >= $this->numNeeded) {
                $this->ratioMin = 1;
            } else {
                $this->ratioMin = count($this->arrShiftObjectsByIdUser) / $this->numNeeded;
            }
        } else {
            $this->ratioMin = INF;
        }
    }

    // For ShiftStatus, percentApp is not compared but Randomly chosen.
    // private function setPercentApp()
    // {
        // if (count($this->arrShiftAppObjectsByIdUser) === $this->numMax) {
            // $this->percentApp = 1;
        // } else {
            // $this->percentApp = count($this->arrShiftAppObjectsByIdUser) / $this->numMax;
        // }
    // }
}