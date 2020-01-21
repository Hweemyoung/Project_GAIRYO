<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/config.php";

class ShiftsDistributor extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->http_host = $config_handler->http_host;
        $this->Ym = $config_handler->Ym;
        $this->arr_mshifts = $config_handler->set_arr_mshifts()->arr_mshifts;
        $this->init();
    }

    private function init()
    {
        $this->arrShiftObjectsByDate = [];
    }

    public function process()
    {
        $this->executeSql('START TRANSACTION;');
        $this->loadApplications();
        $this->setArrShiftObjectsByDate();
    }

    private function loadApplications()
    {
        $sql = "SELECT id_user, " . implode(', ', $this->arr_mshifts) . " FROM shifts_submitted WHERE Ym='$this->Ym'";
        $stmt = $this->querySql($sql);
        $this->arrMemberApplicationsByIdUser = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        // Some values (e.g. 31st) could be NULL
        $stmt->closeCursor();
        return $this;
    }

    private function setArrShiftObjectsByDate()
    {
        $this->arrShiftObjectsByDate = [];
        $reflectionShiftObject = new ReflectionClass('ShiftObject');
        foreach ($this->arr_mshifts as $mshift) {
            $shift = substr($mshift, -1); // 'B'
            $date = substr($this->Ym, 0, 4) . '-' . substr($this->Ym, -2, 2) . '-' . str_pad(substr($mshift, 0, -1), 2, '0'); // '2020-01-20'
            foreach (array_keys($this->arrMemberApplicationsByIdUser) as $id_user) {
                if ($this->arrMemberApplicationsByIdUser[$id_user][$mshift] === '1') {
                    // If person applied for this
                    $shiftObject = $reflectionShiftObject->newInstanceWithoutConstructor();
                    $shiftObject->id_user = $id_user;
                    $shiftObject->date_shift = $date;
                    $shiftObject->shift = $shift;
                    $shiftObject->__construct($this->arrayShiftsByPart);
                    $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
                    array_push($this->arrShiftObjectsByDate[$date][$id_user][], $shiftObject);
                }
            }
        }
    }

    private function chooseMemberToDistribute(){

    }

    private function distributeShift(){

    }

    private function distributeShiftsOfDate(){
        // Choose member
        $this->chooseMemberToDistribute();
        // Distribute date
        $this->distributeShift();
    }
}

class MemberApplications extends MemberObject
{
}
