<?php
class ShiftStatus
{
    public $arrShiftAppObjectsByIdUser = [];
    public $arrShiftObjectsByIdUser = [];
    public $numMax; // Could be NULL
    public $numNeeded;// Could be NULL
    public $numApplicants;
    public $vacancy; // <1: insufficient int(1): full. Could be INF
    public $ratioMin; // <1: insufficient int(1): fitted. Could be INF

    public function __construct($shift, $shiftPart, $config_handler)
    {
        $this->shift = $shift;
        $this->shiftPart = $shiftPart;
        $this->config_handler = $config_handler;
        $this->init();
    }

    private function init()
    {
        // Not set if not exist.
        if (isset($this->config_handler->numMaxByShift[$this->shift])){
            $this->numMax = $this->config_handler->numMaxByShift[$this->shift];
        }
        if(isset($this->config_handler->numNeededByShift[$this->shift])){
            $this->numNeeded = $this->config_handler->numNeededByShift[$this->shift];
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
            $this->vacancy = INF;
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