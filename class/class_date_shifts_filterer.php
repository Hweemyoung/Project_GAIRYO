<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_shift_object.php";
require_once "$homedir/config.php";

class DateShiftsFilterer extends DateObject
{
    // doens't allow call for shift in same shift part. User must remove the shift first.
    public $arrayShiftObjectsByShift;
    public $myShiftParts;

    public function __construct($date, $arrayShiftObjectsOfDate, $master_handler, $config_handler)
    {
        $this->date = $date;
        $this->master_handler = $master_handler;
        $this->id_user = $master_handler->id_user;
        $this->dbh = $master_handler->dbh;
        $this->config_handler = $config_handler;
        $this->arrayShiftObjectsOfDate = $arrayShiftObjectsOfDate;
        $this->process();
    }

    private function process()
    {
        $this->setArrShiftsOthers();
        $this->setMyShiftParts();
        $this->filterArrShiftObjectsByShift();
    }

    private function setArrShiftsOthers()
    {
        $this->arrShiftAvailableByShift = [];
        foreach ($this->arrayShiftObjectsOfDate as $shiftObject) {
            if (!isset($this->arrShiftAvailableByShift[$shiftObject->shift])) {
                // Set to true
                $this->arrShiftAvailableByShift[$shiftObject->shift] = true;
            }
        }
        $arr = array('A', 'B', 'H', 'C', 'D');
        uksort($this->arrShiftAvailableByShift, function ($a, $b) use ($arr) {
            $key_a = array_search($a, $arr);
            $key_b = array_search($b, $arr);
            return $key_a - $key_b;
        });
    }

    private function setMyShiftParts()
    {
        $this->myShiftParts = [];

        $sql = "SELECT id_user, date_shift, shift FROM shifts_assigned WHERE done=0 AND date_shift='$this->date' AND id_user=$this->id_user;";
        $stmt = $this->dbh->query($sql);
        $myShiftObjects = $stmt->fetchAll(PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]);
        $stmt->closeCursor();
        foreach ($myShiftObjects as $shiftObject) {
            $this->myShiftParts[$shiftObject->shiftPart] = NULL;
        }
    }

    private function filterArrShiftObjectsByShift()
    {
        foreach (array_keys($this->myShiftParts) as $shiftPart) {
            // echo "User has shift in part $shiftPart<br>";
            foreach ($this->config_handler->arrayShiftsByPart[$shiftPart] as $shift) {
                if (isset($this->arrShiftAvailableByShift[$shift])) {
                    // Set to false
                    // echo "arrShiftAvailableByShift has $shift and setting to unavailable.<br>";
                    $this->arrShiftAvailableByShift[$shift] = false;
                }
            }
        }
    }
}
