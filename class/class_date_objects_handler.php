<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_shift_object.php";
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