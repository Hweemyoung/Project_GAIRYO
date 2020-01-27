<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/utils.php";

class MarketItemHandler extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->date_objects_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->date_objects_put_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->date_objects_call_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->setArrIdRequests();
        $this->setDateObjectsRequestedHandler();
        $this->setDateObjectsHandler();
    }

    private function setArrIdRequests()
    {
        // Put
        $sql = "SELECT id_shift, id_shift, id_from, id_to, id_request FROM requests_pending WHERE `status`=2 AND id_created<>$this->id_user AND (id_from IS NOT NULL AND id_to IS NULL) ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $this->arrIdPutRequestsByIdShift = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        // Call
        $sql = "SELECT date_shift, date_shift, shift, id_from, id_to, id_request FROM requests_pending WHERE `status`=2 AND id_created<>$this->id_user AND (id_from IS NULL AND id_to IS NOT NULL AND id_shift=NULL) ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $this->arrIdCallRequestsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        // Call: group by shift
        if (count($this->arrIdCallRequestsByDate)) {
            try {
                $this->arrIdCallRequestsByDate = utils\groupArrayByValue($this->arrIdCallRequestsByDate, 'shift');
            } catch (Exception $e) {
                echo "Caught exception: " . $e->getMessage();
                exit;
            }
        }
    }

    private function setDateObjectsRequestedHandler()
    {
        // Call after setArrIdRequests
        // Put: load all put shifts
        if (count($this->arrIdPutRequestsByIdShift)) {
            $sqlConditions = $this->genSqlConditions(array_keys($this->arrIdPutRequestsByIdShift), 'id_shift', 'OR');
            $sql = "SELECT date_shift, date_shift, id_user, shift, id_shift FROM shifts_assigned WHERE done=0 AND $sqlConditions;";
            $stmt = $this->querySql($sql);
            $arrShiftPutObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser]);
            $stmt->closeCursor();
            $this->date_objects_put_handler->setArrayDateObjects($arrShiftPutObjectsByDate);
            $arrDateShiftsPut = array_keys($arrShiftPutObjectsByDate);
        } else {
            $arrDateShiftsPut = [];
        }

        // Call: Load all shift candidates for this call
        if (count($this->arrIdCallRequestsByDate)) {
            $arrSqlConditions = [];
            foreach ($this->arrIdCallRequestsByDate as $date_shift => $arrCallRequestsByShifts) {
                $sqlConditions = $this->genSqlConditions(array_keys($arrCallRequestsByShifts), 'shift', 'OR');
                $arrSqlConditions[] = "(date_shift='$date_shift' AND $sqlConditions)";
            }
            $sqlConditions = '(' . implode(' OR ', $arrSqlConditions) . ')';
            // '((date_shift=234543 and (shift=B or shift=C)) or (...) or (...))'
            $sql = "SELECT date_shift, date_shift, id_user, shift, id_shift FROM shifts_assigned WHERE done=0 AND $sqlConditions;";
            echo 'Put sql:' .  $sql . '<br>';
            $stmt = $this->querySql($sql);
            $arrShiftCallObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser]);
            $this->date_objects_call_handler->setArrayDateObjects($arrShiftCallObjectsByDate);
            $arrDateShiftsCall = array_keys($arrShiftCallObjectsByDate);
        } else {
            $arrDateShiftsCall = [];
        }

        // Save date_shifts. This will be used for loading All ShiftObjects of dates
        // Merge arrDates
        $this->arrDateShifts = array_unique(array_merge($arrDateShiftsPut, $arrDateShiftsCall));
        ksort($this->arrDateShifts);
        // var_dump($this->arrDateShifts);
    }

    private function setDateObjectsHandler()
    {
        // Call after setDateObjectsRequestedHandler
        $sqlConditions = $this->genSqlConditions($this->arrDateShifts, 'date_shift', 'OR');
        $sql = "SELECT date_shift, date_shift, id_user, shift, id_shift FROM shifts_assigned WHERE done=0 AND $sqlConditions;";
        $stmt = $this->querySql($sql);
        $arrShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser]);
        $stmt->closeCursor();
        $this->date_objects_handler->setArrayDateObjects($arrShiftObjectsByDate);
    }

    public function echoMarketTimeline()
    {
        // var_dump(array_keys($this->date_objects_put_handler->arrayDateObjects));
        // echo '<br>';
        // var_dump(array_keys($this->date_objects_call_handler->arrayDateObjects));
        // echo '<br>';
        foreach ($this->arrDateShifts as $key => $date_shift) {
            if ($key % 2) {
                // $key % 2 === 0:left, 1:right
                $classFlexRowReverse = '';
            } else {
                $classFlexRowReverse = 'flex-row-reverse';
            }
            $objDateObjectsByMode = new stdClass();
            $objDateObjectsByMode->date_shift = $date_shift;
            $dateObjectPut = isset($this->date_objects_put_handler->arrayDateObjects[$date_shift]) ? $this->date_objects_put_handler->arrayDateObjects[$date_shift] : NULL;
            // var_dump($objDateObjectsByMode->dateObjectPut);
            // var_dump($this->date_objects_put_handler->arrayDateObjects[$date_shift]);
            $dateObjectCall = isset($this->date_objects_call_handler->arrayDateObjects[$date_shift]) ? $this->date_objects_call_handler->arrayDateObjects[$date_shift] : NULL;
            $objDateObjectsByMode->arrDateObjects = ['put' => $dateObjectPut, 'call' => $dateObjectCall];
            // var_dump($objDateObjectsByMode);
            $this->echoSection($objDateObjectsByMode, $classFlexRowReverse);
            if ($key === count($this->arrDateShifts) - 1) {
                break;
            }
            $this->echoPath($classFlexRowReverse);
        }
    }

    private function echoSection($objDateObjectsByMode, $classFlexRowReverse)
    {

        echo "
        <div id='$objDateObjectsByMode->date_shift' class='div-timeline-section'>
        ";
        echo "
            <div class='row no-gutters align-items-center how-it-works d-flex $classFlexRowReverse'>";
        $dateTime = DateTime::createFromFormat('Y-m-d', $objDateObjectsByMode->date_shift);
        $this->echoColDate($dateTime);
        // var_dump($objDateObjectsByMode);
        $this->echoColShifts($objDateObjectsByMode, $classFlexRowReverse);
        echo '
            </div>';

        echo '
        </div>'; // .div-timeline-section
    }

    private function echoPath($classFlexRowReverse)
    {
        if ($classFlexRowReverse === '') {
            $position = 'left';
            $counter = 'right';
        } else {
            $position = 'right';
            $counter = 'left';
        }
        echo "
            <div class='row timeline d-flex $classFlexRowReverse'>
                <div class='col-2'>
                    <div class='corner top-$counter'></div>
                </div>
                <div class='col-8'>
                    <hr />
                </div>
                <div class='col-2'>
                    <div class='corner $position-bottom'></div>
                </div>
            </div>
            ";
    }

    private function echoColShifts($objDateObjectsByMode, $classFlexRowReverse)
    {
        echo '
                <div class="col-6">';
        foreach ($objDateObjectsByMode->arrDateObjects as $mode => $dateObject) {
            if ($dateObject !== NULL) {
                echo "
                        <div class='row no-gutters'>
                            <div class='col-12 col-$mode d-flex $classFlexRowReverse'>";
                echo " 
                                <div class='btn-group' mode='$mode'>";
                foreach (array_keys($dateObject->arrayShiftObjectsByShift) as $shift) {
                    echo "
                                    <a href='#modal' class='btn btn-$shift' data-toggle='modal'>$shift</a>";
                }
                echo "
                                
                                </div>
                            </div>
                        </div>";
            }
        }
        echo '
                </div>';
    }

    private function echoColDate($dateTime)
    {
        $date = $dateTime->format('M j');
        $day = $dateTime->format('D');
        switch ($day) {
            case 'Sun':
                $classTextColor = 'text-danger';
                break;
            case 'Sat':
                $classTextColor = 'text-primary';
                break;
            default:
                $classTextColor = '';
        }
        echo "
                <div class='col-2 text-center bottom d-inline-flex justify-content-center align-items-center'>
                    <div class='circle'>
                        <div class='div-circle-text'>$date</div><div class='div-circle-text $classTextColor'>$day</div>
                    </div>
                </div>
        ";
    }
}
