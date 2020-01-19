<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/class/class_member_object.php";

class DateObjectsHandler
{
    public function __construct($master_handler, $arrayShiftTimes)
    {
        $this->id_user = $master_handler->id_user;
        $this->dbh = $master_handler->dbh;
        $this->arrayDateObjects = [];
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftTimes = $arrayShiftTimes;
    }

    public function arrayPushDateObjects(array $params)
    {
        $array_conditions = [];
        if (count($params) === 0) {
            $array_conditions = [1];
        } else {
            foreach (array_keys($params) as $key) {
                if ($key === 'date_start') {
                    array_push($array_conditions, "date_shift>='" . $params[$key] . "'");
                } elseif ($key === 'date_end') {
                    array_push($array_conditions, "date_shift<='" . $params[$key] . "'");
                } elseif ($key === 'date') {
                    array_push($array_conditions, "date_shift='" . $params[0] . "'");
                } else {
                    echo "Key not understood.";
                    exit;
                }
            }
        }
        $sql = "SELECT date_shift, id_user, shift FROM shifts_assigned WHERE " . '(' . implode('AND', $array_conditions) . ')' . " ORDER BY date_shift ASC;";
        $stmt = $this->dbh->query($sql);
        // var_dump($stmt->errorInfo());
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject');
        $stmt->closeCursor();
        foreach (array_keys($arrayShiftObjectsByDate) as $date) {
            foreach ($arrayShiftObjectsByDate[$date] as $shiftObject) {
                $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
            }
            $this->arrayDateObjects[$date] = new DateObject($date, $arrayShiftObjectsByDate[$date]);
        }
    }
}

class DateObject
{
    public $date;
    public $arrayShiftObjectsByShift;
    public $arrayNumLangsByPart;
    public $arrayLangsByPart = [['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL], ['cn' => 2, 'kr' => NULL, 'th' => NULL, 'my' => NULL, 'ru' => NULL, 'fr' => NULL, 'de' => NULL, 'other' => NULL]];
    function __construct($date, $arrayShiftObjectsOfDate, $arrayLangsByPart)
    {
        $this->date = $date;
        $this->arrayLangsByPart = $arrayLangsByPart;
        $this->arrayNumLangsByPart = [[], []];
        $this->arrayShiftObjectsByShift = [];
        $this->setArrayShiftObjectByShift($arrayShiftObjectsOfDate);
        $this->setNumArrayLangsByPart($arrayShiftObjectsOfDate);
    }
    private function setNumArrayLangsByPart($arrayShiftObjectsOfDate)
    {
        foreach ($arrayShiftObjectsOfDate as $shiftObject) {
            foreach (array_keys($this->arrayLangsByPart[0]) as $lang) {
                if (isset($this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang])) {
                    
                    $this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] += $shiftObject->memberObject->$lang;
                } else {
                    $this->arrayNumLangsByPart[$shiftObject->shiftPart][$lang] = 1;
                }
            }
        }
    }

    private function setArrayShiftObjectByShift($arrayShiftObjectsOfDate)
    {
        foreach ($arrayShiftObjectsOfDate as $shiftObject) {
            $this->arrayShiftObjectsByShift[$shiftObject->shift][] = $shiftObject;
        }
    }
}

class ShiftObject
{
    public $id_user;
    public $date_shift;
    public $shift;
    public $shiftPart;
    public $memberObject;
    public static $shiftParts = [['A', 'B', 'H'], ['C', 'D']];
    function __construct()
    {
    }
    public function setMemberObj($arrayMemberObjectsByIdUser)
    {
        $this->memberObject = $arrayMemberObjectsByIdUser[$this->id_user];
        for($i=0; $i<count(self::$shiftParts); $i++){
            if(in_array($this->shift, self::$shiftParts)){
                $this->shiftPart = $i;
                break;
            }
        }
    }
    public function setShiftPart(){
        for($i=0; $i<count(self::$shiftParts); $i++){
            if(in_array($this->shift, self::$shiftParts[$i])){
                $this->shiftPart = $i;
                break;
            }
        }
    }
    public function set_Ym(){
        $this->YM = date('Y m', $this->date_shift);
    }

    public function set_d(){
        $this->d = date('d', $this->date_shift);
    }
}