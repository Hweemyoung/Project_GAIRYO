<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_date_objects_handler.php";
require_once "$homedir/class/class_date_shifts_filterer.php";
require_once "$homedir/config.php";

class ShiftCaller extends DateObjectsHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->id_user = $master_handler->id_user;
        $this->config_handler = $config_handler;
        $this->process();
    }

    private function process(){
        $this->loadShiftObjectsByDate();
        $this->setArrayDateObjects($this->arrShiftObjectsOthersByDate); // DateObjectsHandler method.
    }

    public function setArrayDateShiftsFilterer(){
        if(count($this->arrShiftObjectsOthersByDate)){
            foreach ($this->arrShiftObjectsOthersByDate as $date => $arrShiftObjects) {
                $this->arrayDateObjects[$date] = new DateShiftsFilterer($date, $arrShiftObjects, $this->master_handler, $this->config_handler);
            }
        }
    }

    private function loadShiftObjectsByDate(){
        // Load other's shifts
        $sql = "SELECT date_shift, id_user, date_shift, shift FROM shifts_assigned WHERE done=0 AND id_user<>$this->id_user;";
        $stmt = $this->master_handler->dbh->query($sql);
        $this->arrShiftObjectsOthersByDate = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]);
        $stmt->closeCursor();
    }
}
