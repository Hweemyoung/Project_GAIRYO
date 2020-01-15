<?php
// Config
$shiftA = array('time-start' => '07:40', 'time-end' => '12:00', 'btn-color' => 'btn-info');
$shiftB = array('time-start' => '08:00', 'time-end' => '13:30', 'btn-color' => 'btn-secondary');
$shiftH = array('time-start' => '08:00', 'time-end' => '13:00', 'btn-color' => 'btn-success');
$shiftC = array('time-start' => '12:30', 'time-end' => '18:00', 'btn-color' => 'btn-dark text-light');
$shiftD = array('time-start' => '13:30', 'time-end' => '18:00', 'btn-color' => 'btn-warning');
$arrayShiftTimes = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);

// Hyperparameters
$arrayHparams = array('YLowerBound' => 2020, 'dayStart' => 'Mon', 'dayEnd' => 'Sun', 'arrayShiftTimes' => $arrayShiftTimes);

require_once './class/class_date_object.php';

class DailyMembersHandler
{
    public $id_user;
    public $dbh;
    public $Y;
    public $currentPage;
    public $YLowerBound = 2020;
    public $dayStart = 'Mon';
    public $dayEnd = 'Sun';
    public $arrayShiftTimes;
    public $dateStart;
    public $dateEnd;
    public $arrayDateObjects;

    public function __construct($master_handler, $arrayShiftTimes)
    {
        $this->id_user = $master_handler->id_user;
        $this->dbh = $master_handler->dbh;
        $this->arrayDateObjects = [];
        // $this->set_all($arrayShiftTimes);
        $this->arrayShiftTimes = $arrayShiftTimes;
        $this->setProps();
    }

    // public function set_all($_array){
    //     foreach(array_keys($_array) as $_prop){
    //         $this->_arrayProps[$_prop] = $_array[$_prop];
    //     }
    // }

    private function setProps()
    {
        $this->setYear();
        $this->setPageMaxAndMaxYear();
        $this->setPage();
        $this->setDateRange();
        $this->setArrayMemberObjectsByIdUser();
        $this->setArrayDateObjects();
    }

    private function setYear()
    {
        // Set year
        if (!isset($_GET["Y"])) {
            $this->Y = date('Y', time());
        } else {
            $this->Y = $_GET["Y"];
        }
    }
    private function setPage()
    {
        if (!isset($_GET["page"])) {
            $this->currentPage = $this->pageMax;
        } else if ($_GET["page"] > $this->pageMax) {
            echo 'Error: page larger than pageMax';
            exit;
        } else {
            $this->currentPage = $_GET["page"];
        }
    }

    private function setPageMaxAndMaxYear()
    {
        // Find max page and max year
        $sql = "SELECT date_shift FROM shifts_assigned WHERE date_shift LIKE '{$this->Y}%' ORDER BY date_shift DESC LIMIT 1";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute();
        $dateLast = new DateTime($stmt->fetchAll(PDO::FETCH_COLUMN)[0]);
        $this->pageMax = intval(date('W', $dateLast->getTimestamp()));
        $this->YMax = date('Y', $dateLast->getTimestamp());
        $this->pageUpperBound = date('W', strtotime("{$this->Y}-12-31"));
    }

    private function setDateRange()
    {
        $this->dateStart = ($this->getDateTimeOfDayFromWeek($this->Y, $this->currentPage, $this->dayStart))->format('Y-m-d');
        $this->dateEnd = ($this->getDateTimeOfDayFromWeek($this->Y, $this->currentPage, $this->dayEnd))->format('Y-m-d');
    }

    private function getDateTimeOfDayFromWeek($Y, $W, $day = 'Sun')
    {
        // find first day of the year
        $firstDate = strtotime("$day Jan $Y");
        // calculate how much weeks to add
        $weeksOffset = $W - date('W', $firstDate);
        // calculate searched day
        $dateTimeOfDay = new DateTime(date('Y-m-d', strtotime("+$weeksOffset week", $firstDate)));
        return $dateTimeOfDay;
    }

    private function setArrayMemberObjectsByIdUser()
    {
        $sql = 'SELECT id_user, members.* FROM members WHERE `status` = 1';
        $this->arrayMemberObjectsByIdUser = $this->dbh->query($sql)->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'MemberObject');
    }

    private function setArrayDateObjects()
    {
        $sql = "SELECT date_shift, id_user, shift FROM shifts_assigned WHERE date_shift >= ? AND date_shift <= ? ORDER BY date_shift ASC";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array($this->dateStart, $this->dateEnd));
        // var_dump($stmt->errorInfo());
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject');
        foreach (array_keys($arrayShiftObjectsByDate) as $date) {
            foreach ($arrayShiftObjectsByDate[$date] as $shiftObject) {
                $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
            }
            $this->arrayDateObjects[$date] = new DateObject($date, $arrayShiftObjectsByDate[$date]);
        }
    }

    private function genHref(array $params)
    {
        if (!isset($params['Y'])) {
            $params['Y'] = $this->Y;
        }
        $href = "./shifts.php";
        for ($i = 0; $i < count(array_keys($params)); $i++) {
            if ($i) {
                $href = $href . '&' . array_keys($params)[$i] . '=' . $params[array_keys($params)[$i]];
            } else {
                $href = $href . '?' . array_keys($params)[$i] . '=' . $params[array_keys($params)[$i]];
            }
        }
        return $href;
    }

    public function echoSearchBar()
    {
        echo '
        <!-- Search bar -->
        <div class="div-search jumbotron bg-light mb-2 p-2">
    ';
        echo "
            <div class='div-search-year'>
                <div class='dropdown text-center'>
                    <button class='btn btn-outline-primary dropdown-toggle' type='button' data-toggle='dropdown'>{$this->Y}</button>
                    <div class='dropdown-menu'>";
        if (($this->Y - 1) >= $this->YLowerBound) {
            $href = $this->genHref(array('Y' => ($this->Y - 1)));
            echo strtr('
                        <a href="$href" class="dropdown-item">$YPrev</a>
    ', array('$href' => $href, '$YPrev' => ($this->Y - 1)));
        }
        $href = $this->genHref(array());
        echo strtr('
                        <a href="$href" class="dropdown-item active">$Y</a>
    ', array('$href' => $href, '$Y' => $this->Y));
        if (($this->Y + 1) <= $this->YMax) {
            $href = $this->genHref(array('Y' => ($this->Y + 1)));
            echo strtr('
                        <a href="$href" class="dropdown-item">$YNext</a>
        ', array('$href' => $href, '$YNext' => ($this->Y + 1)));
        }
        echo '
                    </div>
                </div>
            </div>
        ';
        echo '
            <div class="div-search-week">
                <ul class="pagination pagination-sm justify-content-center">
    ';
        if ($this->currentPage == 1) {
            $disabled = 'disabled';
            $href = '';
        } else {
            $disabled = '';
            $href = $this->genHref(array('page' => 1));
        }
        echo strtr(
            '
                    <li class="page-item $disabled"><a class="page-link" href="$href"><i class="fas fa-angle-double-left"></i></a></li>
                    ',
            array('$disabled' => $disabled, '$href' => $href)
        );
        if ($this->currentPage > 3) {
            $disabled = '';
            $href = $this->genHref(array('page' => $this->currentPage - 3));
        } else {
            $disabled = 'disabled';
            $href = '';
        }
        echo strtr('
                    <li class="page-item $disabled"><a class="page-link" href="$href"><i class="fas fa-angle-left"></i></a></li>
    ', array('$disabled' => $disabled, '$href' => $href));
        for ($i = 2; $i > 0; $i--) {
            if (($this->currentPage - $i) > 0) {
                $page = $this->currentPage - $i;
                $href = $this->genHref(array('page' => $page));
                echo strtr('
                    <li class="page-item"><a class="page-link" href="$href">$page</a></li>
            ', array('$href' => $href, '$page' => $page));
            }
        }
        echo strtr('
                    <li class="page-item active"><a class="page-link" href="#">$currentPage</a></li>
    
    ', array('$currentPage' => $this->currentPage));
        for ($i = 1; $i <= 2; $i++) {
            $page = $this->currentPage + $i;
            if ($page <= $this->pageUpperBound) {
                if ($page <= $this->pageMax) {
                    $href = $this->genHref(array('page' => $page));
                    echo strtr('
                    <li class="page-item"><a class="page-link" href="$href">$page</a></li>
                ', array('$href' => $href, '$page' => $page));
                } else {
                    echo strtr('
                    <li class="page-item disabled"><a class="page-link" href="$href">$page</a></li>
                ', array('$href' => $href, '$page' => $page));
                }
            } else {
                break;
            }
        }
        if ($this->currentPage < $this->pageMax - 2) {
            $disabled = '';
            $href = $this->genHref(array('page' => $this->currentPage + 3));
        } else {
            $disabled = 'disabled';
            $href = '';
        }
        echo strtr('
                    <li class="page-item $disabled"><a class="page-link" href="$href"><i class="fas fa-angle-right"></i></a></li>
                    ', array('$disabled' => $disabled, '$href' => $href));
        if ($this->currentPage == $this->pageMax) {
            $disabled = 'disabled';
            $href = '';
        } else {
            $disabled = '';
            $href = $this->genHref(array('page' => $this->pageMax));
        }
        echo strtr('
                    <li class="page-item $disabled"><a class="page-link" href="$href"><i class="fas fa-angle-double-right"></i></a></li>
                    ', array('$disabled' => $disabled, '$href' => $href));
        echo '
                </ul>
            </div>
        </div>';
    }

    public function echoAccordion()
    {
        $matchShiftsAndColumns = array(array('A', 'C'), array('H', 'D'), array('B'));
        // var_dump($arrayMembersByIdUser);OK
        // echo $id_user;OK

        echo '
        <div id="accordion">';
        // var_dump($this->arrayDateObjects);
        foreach (array_keys($this->arrayDateObjects) as $date) {
            $currentDateTime = new DateTime($date);
            // var_dump($currentDateTime);
            $headerTitle = $currentDateTime->format('M j (D)');
            if (!isset($_GET["date"])) {
                $show = '';
            } elseif ($_GET["date"] !== $date) {
                $show = '';
            } else {
                $show = 'show';
            }
            $w = $currentDateTime->format('w');
            echo '
            <div class="card">
        ';
            echo strtr('
                <div class="card-header"><a href="#day$w" class="card-link" data-toggle="collapse">$headerTitle</a></div>
        ', array('$w' => $w, '$headerTitle' => $headerTitle));
            echo strtr('
                <div class="collapse $show" data-parent="#accordion" id="day$w">
                    <div class="card-body">
                        <div class="row no-gutters">
                            <!-- col left -->
                            <div class="col-md-8">
                                <div class="div-schedule">
                                    <!-- timeline -->
                                    <div class="timeline">
                                        <ul type="none">
                                        </ul>
                                    </div>
                                    <div class="div-columns">
            ', array('$w' => $w, '$show' => $show));
            for ($i = 0; $i < count($matchShiftsAndColumns); $i++) {
                echo '
                                        <div class="column">
            ';
                // var_dump($arrayShiftTimes);
                foreach ($matchShiftsAndColumns[$i] as $shift) {
                    echo strtr('
                                            <a class="btn $btnColor" time-start="$timeStart" time-end="$timeEnd" data-toggle="modal">
                                                <h5>$shift</h5>
                                                <ul type="none">
                ', array('$btnColor' => $this->arrayShiftTimes[$shift]['btn-color'], '$timeStart' => $this->arrayShiftTimes[$shift]['time-start'], '$timeEnd' => $this->arrayShiftTimes[$shift]['time-end'], '$shift' => $shift));
                    if (isset($this->arrayDateObjects[$date]->arrayShiftObjectsByShift[$shift])) {
                        foreach ($this->arrayDateObjects[$date]->arrayShiftObjectsByShift[$shift] as $shiftObject) {
                            // var_dump($arrayShift);
                            $nickname = $shiftObject->memberObject->nickname;
                            echo "
                                                    <li>$nickname</li>";
                        }
                    }
                    echo '
                                                </ul>
                                            </a>';
                }
                echo '
                                        </div>';
            }
            echo '
                                    </div>
                                </div>
                            </div>';
            echo '
                            <!-- col right -->
                            <div class="col-md-4">
                                <div class="shift-member-table">';
            foreach (array_keys($this->arrayShiftTimes) as $shift) {
                echo '
                                    <div class="row">
            ';
                echo strtr('
                                        <div class="col-2 d-flex"><p>$shift</p></div>
            ', array('$shift' => $shift));
                echo '
                                        <div class="col-10">
                                            <ul class="list-group">';
                if (isset($this->arrayDateObjects[$date]->arrayShiftObjectsByShift[$shift])) {
                    foreach ($this->arrayDateObjects[$date]->arrayShiftObjectsByShift[$shift] as $shiftObject) {
                        if ($this->id_user !== $shiftObject->memberObject->id_user) {
                            $nickname = $shiftObject->memberObject->nickname;
                            $active = '';
                        } else {
                            $nickname = 'YOU';
                            $active = 'active';
                        }
                        echo strtr('                <li class="list-group-item $active">
                                                    <div class="dropdown">
                                                        <a data-toggle="dropdown">$nickname</a>
                    ', array('$active' => $active, '$nickname' => $nickname));
                        echo strtr('
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <div class="dropdown-header">$nickname</div>
                                                            <a class="dropdown-item" href="#">Call this shift</a>
                                                            <a class="dropdown-item" href="#">Send message</a>
                                                        </div>
                                                    </div>
                                                </li>
                    ', array('$nickname' => $nickname));
                    }
                }
                echo '
                                            </ul>
                                        </div>';
                echo '
                                    </div>';
            }
            echo '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>';
        }

        // Close Accordion
        echo '
        </div>';
    }
}

$daily_member_handler = new DailyMembersHandler($master_handler, $arrayShiftTimes);
// var_dump(get_object_vars($daily_member_handler));
?>

<!-- Empty modal -->
<div class="modal fade" id="modal-A">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title"></div>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer"><button class="btn btn-danger" type="button" data-dismiss="modal">Close</button></div>
        </div>
    </div>
</div>
<!-- Search bar -->
<?php $daily_member_handler->echoSearchBar() ?>
<!-- Accordion -->
<?php $daily_member_handler->echoAccordion() ?>
<script src="./js/custom_schedule.js"></script>
<script>
    // text color of card-headers
    $('#accordion .card-header a').addClass('text-dark')
</script>