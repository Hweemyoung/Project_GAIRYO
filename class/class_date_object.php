<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/class/class_member_object.php";

class DateObjectsHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->id_user = $master_handler->id_user;
        $this->dbh = $master_handler->dbh;
        $this->arrayDateObjects = [];
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->config_handler = $config_handler;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->arrayShiftTimes = $config_handler->arrayShiftTimes;
        $this->arrayLangsByPart = $config_handler->arrayLangsByPart;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
    }

    public function setArrayDateObjects($arrayShiftObjectsByDate)
    {
        if (count($arrayShiftObjectsByDate)) {
            foreach ($arrayShiftObjectsByDate as $date => $arrShiftObjects) {
                $this->arrayDateObjects[$date] = new DateObject($date, $arrShiftObjects, $this->config_handler);
            }
        }
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
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser]);
        $stmt->closeCursor();
        foreach ($arrayShiftObjectsByDate as $date => $arrShiftObjects) {
            $this->arrayDateObjects[$date] = new DateObject($date, $arrShiftObjects, $this->config_handler);
        }
    }
}

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
        $this->arrayLangsByPart = $config_handler->arrayLangsByPart;
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

    private function setArrayShiftObjectsByShift($arrayShiftObjectsOfDate)
    {
        if (count($arrayShiftObjectsOfDate)) {
            // Initialize array
            $this->arrayShiftObjectsByShift = [];
            foreach ($arrayShiftObjectsOfDate as $shiftObject) {
                $this->pushArrayShiftObjectByShift($shiftObject);
                $shiftObject->memberObject->shiftObjectsByPart[$shiftObject->shiftPart][$shiftObject->shift] = $shiftObject;
            }
        }
    }

    public function setEnoughLangsByPart()
    {
        // Initialize arrays   
        $this->enoughLangsByPart = [];
        $this->arrBalancesByPart = [];
        // echo $this->date .'<br>';

        if (count($this->arrayNumLangsByPart)) {
            foreach ($this->arrayLangsByPart as $part => $arrLangs) {
                $partEnough = true;
                foreach ($arrLangs as $lang => $numNeeded) {
                    if ($numNeeded === NULL) {
                        continue;
                    }
                    if (!isset($this->arrayNumLangsByPart[$part][$lang])) {
                        $balance = -$numNeeded;
                        $this->enoughLangsByPart[$part] = false;
                    } else {
                        $balance = $this->arrayNumLangsByPart[$part][$lang] - $numNeeded;
                    }
                    // Negative value means not enough
                    $this->arrBalancesByPart[$part][$lang] = $balance;
                    if ($balance < 0) {
                        $partEnough = false;
                    }
                }
                $this->enoughLangsByPart[$part] = $partEnough;
            }
        }
        $partEnough = NULL;
    }
}

class ShiftObject
{
    public $id_user;
    public $date_shift;
    public $shift;
    public $memberObject;
    public static $arrayShiftsByPart;
    function __construct($arrayShiftsByPart, $arrayMemberObjectsByIdUser)
    {
        $this->setShiftPart($arrayShiftsByPart);
        $this->setMemberObj($arrayMemberObjectsByIdUser);
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
    public function set_Ym()
    {
        $this->YM = date('Y m', $this->date_shift);
    }

    public function set_d()
    {
        $this->d = date('d', $this->date_shift);
    }
}
