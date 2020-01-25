<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_db_handler.php";

class MarketItemHandler extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->date_objects_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->load_market_items();
        $this->setDateObjectsHandler();
    }

    private function load_market_items()
    {
        $sql = "SELECT id_shift, id_request FROM requests_pending WHERE `status`=2 AND id_to IS NULL ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $this->arrIdRequestsByIdShift = $stmt->fetchAll(PDO::FETCH_UNIQUE);
        $stmt->closeCursor();
    }

    private function setDateObjectsHandler()
    {
        // Call after load_market_items
        $sqlConditions = $this->genSqlConditions(array_keys($this->arrIdRequestsByIdShift), 'id_shift', 'OR');
        $sql = "SELECT date_shift, date_shift, id_user, shift, id_shift FROM shifts_assigned WHERE done=0 AND $sqlConditions;";
        $stmt = $this->querySql($sql);
        $arrShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart, $this->arrayMemberObjectsByIdUser]);
        $stmt->closeCursor();
        $this->date_objects_handler->setArrayDateObjects($arrShiftObjectsByDate);
    }

    public function echoMarketTimeline()
    {
        foreach (array_keys($this->date_objects_handler->arrayDateObjects) as $key => $date) {
            if ($key % 2) {
                // $key % 2 === 0:left, 1:right
                $classFlexRowReverse = '';
            } else {
                $classFlexRowReverse = 'flex-row-reverse';
            }
            $this->echoSection($this->date_objects_handler->arrayDateObjects[$date], $classFlexRowReverse);
            if ($key === count($this->date_objects_handler->arrayDateObjects) - 1) {
                break;
            }
            $this->echoPath($classFlexRowReverse);
        }
    }

    private function echoSection($dateObject, $classFlexRowReverse)
    {

        echo "
        <div class='div-timeline-section'>
        ";
        echo "
            <div class='row no-gutters align-items-center how-it-works d-flex $classFlexRowReverse'>";
        $dateTime = DateTime::createFromFormat('Y-m-d', $dateObject->date);
        $this->echoColDate($dateTime);
        $this->echoColShifts($dateObject, $classFlexRowReverse);
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

    private function echoColShifts($dateObject, $classFlexRowReverse)
    {
        echo '
                <div class="col-6">';
        echo "
                    <div class='row no-gutters'>
                        <div class='col-12 col-put d-flex $classFlexRowReverse'>";
        echo '              <div class="btn-group">';
        foreach (array_keys($dateObject->arrayShiftObjectsByShift) as $shift) {
            echo "
                                <a href='#modal' class='btn btn-$shift' data-toggle='modal'>$shift</a>";
        }
        echo "
                            
                            </div>
                        </div>
                    </div>";
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
$market_item_handler = new MarketItemHandler($master_handler, $config_handler);
// var_dump($market_item_handler->date_objects_handler->arrayDateObjects);
?>

<div class="container">
    <div class="bs4-timeline px-1 py-1 py-sm-2 py-md-4">
        <?php
        $market_item_handler->echoMarketTimeline();
        ?>
    </div>
    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title">.modal-title</h1>
                </div>
                <div class="modal-body">
                    <table class="table table-responsive-md text-center">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Month</th>
                                <th>Day</th>
                                <th>Shift</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>
                <div class="modal-footer"><button class="btn btn-danger" type="button" data-dismiss="modal">Close</button></div>
            </div>
        </div>
    </div>
</div>
<script src="./js/marketplace.js"></script>
<script>
const market_item_handler = new MarketItemHandler(<?=json_encode($master_handler->arrayMemberObjectsByIdUser[$master_handler->id_user])?>, <?=json_encode($market_item_handler->date_objects_handler->arrayDateObjects)?>, <?=json_encode($market_item_handler->arrIdRequestsByIdShift)?>, <?=json_encode(_arrDateObjectsRequested)?>);
</script>