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
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->date_objects_handler = new DateObjectsHandler($master_handler, $config_handler);
        $this->load_market_items();
    }
    private function load_market_items()
    {
        $sql = "SELECT date_shift, id_transaction, id_to, shift FROM requests_pending WHERE `status`=2 AND id_from=NULL ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $arrayCallObjectsByDate = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        $sql = "SELECT date_shift, id_shift, id_transaction, id_to, shift FROM requests_pending WHERE `status`=2 AND id_to=NULL ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $arrayPutObjectsByDate = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();

        $stmt = $this->querySql($sql);
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->arrayShiftsByPart]);
        $stmt->closeCursor();
        foreach ($arrayShiftObjectsByDate as $arrayShiftObjects) {
            foreach ($arrayShiftObjects as $shiftObject) {
                $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
            }
        }
        $this->date_objects_handler->setArrayDateObjects($arrayShiftObjectsByDate);
    }

    public function echoMarketTimeline()
    {
        echo '<div class="bs4-timeline">';
        foreach ($this->date_objects_handler->arrayDateObjects as $dateObject) {
            $this->echoSection($dateObject);
        }
        echo '</div>';
    }

    private function echoSection($dateObject)
    {
        $dateTime = DateTime::createFromFormat('Y-m-d', $dateObject->date);
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
        foreach ($dateObject->arrayShiftObjectsByShift as $shiftObject) {
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

<div class="bs4-timeline"></div>
<script src="./js/marketplace.js"></script>
<script>
    const market_item_handler = new MarketItemHandler(<?= json_encode($market_item_handler) ?>);
</script>