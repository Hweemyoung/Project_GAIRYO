<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_date_object";

class MarketItemHandler extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->dbh = $master_handler->dbh;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->date_objects_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->load_market_items();
    }
    private function load_market_items()
    {
        $sql = "SELECT id_shift, id_transaction, id_from, id_shift FROM requests_pending WHERE `status`=2 AND id_to=NULL ORDER BY time_created DESC;";
        $stmt = $this->querySql($sql);
        $arrayShiftObjects = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        $sqlConditions = $this->genSqlConditions(array_keys($arrayShiftObjects), 'id_shift', 'OR');
        $sql = "SELECT date_shift, id_user, shift FROM shift_assigned WHERE $sqlConditions AND done=0 AND under_request=1;";
        $stmt = $this->querySql($sql);
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP|PDO::FETCH_CLASS, 'ShiftObject');
        $stmt->closeCursor();
        foreach($arrayShiftObjectsByDate as $arrayShiftObjects){
            foreach($arrayShiftObjects as $shiftObject){
                $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
            }
        }
        $this->date_objects_handler->setArrayDateObjects($arrayShiftObjectsByDate);
    }

    public function echoMarketTimeline(){
        echo '<div class="bs4-timeline">';
        foreach($this->date_objects_handler->arrayDateObjects as $dateObject){
            $this->echoSection($dateObject);
        }
        echo '</div>';
    }

    private function echoSection($dateObject){
        $dateTime = DateTime::createFromFormat('Y-m-d', $dateObject->date);
        $date = $dateTime->format('M j');
        $day = $dateTime->format('D');
        switch($day){
            case 'Sun':
                $classTextColor = 'text-danger';
                break;
            case 'Sat':
                $classTextColor = 'text-primary';
                break;
            default:
                $classTextColor = '';
        }
        echo '<div class="row align-items-center how-it-works d-flex">';
        echo "
                <div class='col-2 text-center bottom d-inline-flex justify-content-center align-items-center'>
                    <div class='circle'>
                        <p>$date</p><p class='$classTextColor'>$day</p>
                    </div>
                </div>
        ";
        echo '
                <div class="col-6">';
        echo "
                    <div class='row'>
                        <div class='col-12 col-put'>
                            <ul type='none'>";
        foreach($dateObject->arrayShiftObjectsByShift as $shiftObject){
            echo "
                                    <li>H: Member1</li>";
        }
        echo "
                            </ul>
                        </div>
                    </div>
        ";
        echo '</div>';
    }
}
?>

<div class="bs4-timeline">
    <!--first section-->
    <div class="row align-items-center how-it-works d-flex">
        <div class="col-2 text-center bottom d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Feb 28<br>土</p>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-12 col-put">
                    <ul type="none">
                        <li>H: Member1</li>
                        <li>D: Member3, Member4</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!--path between 1-2-->
    <div class="row timeline">
        <div class="col-2">
            <div class="corner top-right"></div>
        </div>
        <div class="col-8">
            <hr />
        </div>
        <div class="col-2">
            <div class="corner left-bottom"></div>
        </div>
    </div>
    <!--second section-->
    <div class="row align-items-center justify-content-end how-it-works d-flex">
        <div class="col-6 text-right">
            <div class="row">
                <div class="col-12 col-put">
                    <ul type="none">
                        <li>H: Member1</li>
                        <li>D: Member3, Member4</li>
                    </ul>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-2 text-center full d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Mar 2<br>月</p>
            </div>
        </div>
    </div>
    <!--path between 2-3-->
    <div class="row timeline">
        <div class="col-2">
            <div class="corner right-bottom"></div>
        </div>
        <div class="col-8">
            <hr />
        </div>
        <div class="col-2">
            <div class="corner top-left"></div>
        </div>
    </div>
    <!--third section-->
    <div class="row align-items-center how-it-works d-flex">
        <div class="col-2 text-center top d-inline-flex justify-content-center align-items-center">
            <div class="circle">
                <p>Mar 4<br>水</p>
            </div>
        </div>
        <div class="col-6">
            <div class="row">
                <div class="col-12 col-put">
                    <div class="btn-group">
                        <button class="btn" type="button">A</button>
                        <button class="btn" type="button">C</button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 col-call">
                    <ul type="none">
                        <li>B: Member2, Member5</li>
                        <li>A: Member3</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>