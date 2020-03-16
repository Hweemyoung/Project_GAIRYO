<?php

class ShiftObject
{
    public $id_user;
    public $date_shift;
    public $shift;
    public $memberObject;
    public static $arrayShiftsByPart;
    function __construct($master_handler, $config_handler)
    {
        // echo 'ShiftObject::id_user:' .  $this->id_user .'<br>';
        $this->setShiftPart($config_handler->arrayShiftsByPart);
        $this->setMemberObj($master_handler->arrayMemberObjectsByIdUser);
        // var_dump($this);
        $this->setDateInfo();
        $this->workingMins = $config_handler->arrayShiftTimes[$this->shift]['workingMins'];
    }
    public function setMemberObj($arrayMemberObjectsByIdUser)
    {
        $this->memberObject = $arrayMemberObjectsByIdUser[$this->id_user];
    }
    public function setShiftPart($arrayShiftsByPart)
    {
        for ($i = 0; $i < count($arrayShiftsByPart); $i++) {
            if (in_array($this->shift, $arrayShiftsByPart[$i])) {
                $this->shiftPart = $i;
                break;
            }
        }
    }

    public function setCallable($callable){
        $this->callable = $callable;
    }

    public function setDateInfo(){
        $dateTime = new DateTime($this->date_shift);
        $this->W = $dateTime->format('W');
        $this->j = $dateTime->format('j');
    }

    public function set_Ym()
    {
        $this->YM = date('Y m', $this->date_shift);
    }

    public function set_d()
    {
        $this->d = date('d', $this->date_shift);
    }
}