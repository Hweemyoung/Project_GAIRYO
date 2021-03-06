<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/class/class_date_objects_handler.php";
require_once "$homedir/utils.php";

class DailyMembersHandler extends DateObjectsHandler
{
    public $id_user;
    public $dbh;
    public $Y;
    public $currentPage;
    public $YLowerBound;
    public $dayStart;
    public $dayEnd;
    public $arrayShiftTimes;
    public $dateStart;
    public $dateEnd;
    public $arrayDateObjects;
    public $arrayPartNames;

    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->id_user = $master_handler->id_user;
        $this->dbh = $master_handler->dbh;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->config_handler = $config_handler;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->YLowerBound = $config_handler->YLowerBound;
        $this->dayStart = $config_handler->dayStart;
        $this->dayEnd = $config_handler->dayEnd;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->arrayShiftTimes = $config_handler->arrayShiftTimes;
        $this->arrayPartNames = $config_handler->arrayPartNames;
        $this->arrayDateObjects = [];
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
        // $this->setArrayMemberObjectsByIdUser();
        $sql = "SELECT date_shift, date_shift, id_user, shift FROM shifts_assigned WHERE date_shift >= ? AND date_shift <= ? AND id_user>0 ORDER BY date_shift ASC";
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute(array($this->dateStart, $this->dateEnd));
        // var_dump($stmt->errorInfo());
        // var_dump($this->arrayMemberObjectsByIdUser);
        $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]);
        $this->setArrayDateObjects($arrayShiftObjectsByDate);
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

    // public function setArrayDateObjects()
    // {
    //     $sql = "SELECT date_shift, id_user, shift FROM shifts_assigned WHERE date_shift >= ? AND date_shift <= ? ORDER BY date_shift ASC";
    //     $stmt = $this->dbh->prepare($sql);
    //     $stmt->execute(array($this->dateStart, $this->dateEnd));
    //     // var_dump($stmt->errorInfo());
    //     $arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject');
    //     foreach (array_keys($arrayShiftObjectsByDate) as $date) {
    //         foreach ($arrayShiftObjectsByDate[$date] as $shiftObject) {
    //             $shiftObject->setMemberObj($this->arrayMemberObjectsByIdUser);
    //             $shiftObject->setShiftPart($this->arrayShiftsByPart);
    //         }
    //         $this->arrayDateObjects[$date] = new DateObject($date, $arrayShiftObjectsByDate[$date], $this->arrayLangsByPart);
    //     }
    // }

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
            $YPrev = $this->Y - 1;
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + ['Y' => $YPrev]);
            echo "
                        <a href='$href' class='dropdown-item'>$YPrev</a>";
        }
        // $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser);
        echo "
                        <a href='#' class='dropdown-item active'>$this->Y</a>";
        if (($this->Y + 1) <= $this->YMax) {
            $YNext = $this->Y + 1;
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + ['Y' => $YNext]);
            echo "
                        <a href='$href' class='dropdown-item'>$YNext</a>";
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
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + ['Y' => $this->Y, 'page' => 1]);
        }
        echo "
                    <li class='page-item $disabled'><a class='page-link' href='$href'><i class='fas fa-angle-double-left'></i></a></li>
        ";
        if ($this->currentPage > 3) {
            $disabled = '';
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + ['Y' => $this->Y, 'page' => $this->currentPage - 3]);
        } else {
            $disabled = 'disabled';
            $href = '';
        }
        echo "
                    <li class='page-item $disabled'><a class='page-link' href='$href'><i class='fas fa-angle-left'></i></a></li>";
        for ($i = 2; $i > 0; $i--) {
            if (($this->currentPage - $i) > 0) {
                $page = $this->currentPage - $i;
                $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + array('Y' => $this->Y, 'page' => $page));
                echo "
                    <li class='page-item'><a class='page-link' href='$href'>$page</a></li>";
            }
        }
        echo "
                    <li class='page-item active'><a class='page-link' href='#'>$this->currentPage</a></li>";
        for ($i = 1; $i <= 2; $i++) {
            $page = $this->currentPage + $i;
            if ($page <= $this->pageUpperBound) {
                if ($page <= $this->pageMax) {
                    $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + array('Y' => $this->Y, 'page' => $page));
                    echo "
                    <li class='page-item'><a class='page-link' href='$href'>$page</a></li>
                ";
                } else {
                    echo "
                    <li class='page-item disabled'><a class='page-link' href='#'>$page</a></li>
                ";
                }
            } else {
                break;
            }
        }
        if ($this->currentPage < $this->pageMax - 2) {
            $disabled = '';
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + array('Y' => $this->Y, 'page' => $this->currentPage + 3));
        } else {
            $disabled = 'disabled';
            $href = '';
        }
        echo "
                    <li class='page-item $disabled'><a class='page-link' href='$href'><i class='fas fa-angle-right'></i></a></li>";
        if ($this->currentPage == $this->pageMax) {
            $disabled = 'disabled';
            $href = '';
        } else {
            $disabled = '';
            $href = utils\genHref($this->config_handler->http_host, 'shifts.php', $this->master_handler->arrPseudoUser + array('Y' => $this->Y, 'page' => $this->pageMax));
        }
        echo "
                    <li class='page-item $disabled'><a class='page-link' href='$href'><i class='fas fa-angle-double-right'></i></a></li>
        ";
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
        foreach ($this->arrayDateObjects as $date => $dateObject) {
            // var_dump($dateObject->arrayNumLangs);
            $currentDateTime = new DateTime($date);
            // var_dump($currentDateTime);
            $headerTitle = $currentDateTime->format('M j (D)');
            $id_card = $currentDateTime->format('M_j');
            if (!isset($_GET["date"])) {
                $show = '';
            } elseif ($_GET["date"] !== $id_card) {
                $show = '';
            } else {
                $show = 'show';
            }
            $w = $currentDateTime->format('w');
            echo "
            <div class='card' id='$id_card'>
        ";
            echo strtr('
                <div class="card-header"><a href="#day$w" class="card-link" data-toggle="collapse">$headerTitle</a></div>
        ', array('$w' => $w, '$headerTitle' => $headerTitle));
            echo "
                <div class='collapse $show' data-parent='#accordion' id='day$w'>
                    <div class='card-body'>
                        <div class='row no-gutters'>
                            <!-- col left -->
                            <div class='col-md-8 col-left'>
                                <a style='left: -10rem' class='a-popover' data-toggle='popover' data-content='Cozy and neat schedule design provides users quick grasp of assigned coleagues' data-trigger='hover' data-placement='bottom'>Beautiful schedule</a>
                                <div class='div-schedule'>
                                    <!-- timeline -->
                                    <div class='timeline'>
                                        <ul type='none'>
                                        </ul>
                                    </div>
                                    <div class='div-columns'>
        ";
            for ($i = 0; $i < count($matchShiftsAndColumns); $i++) {
                echo '
                                        <div class="column">
            ';
                // var_dump($arrayShiftTimes);
                foreach ($matchShiftsAndColumns[$i] as $shift) {
                    echo strtr('
                                            <button class="btn $btnColor" time-start="$timeStart" time-end="$timeEnd" data-toggle="modal" data-target="#modal-A">
                                                <h5>$shift</h5>
                                                <ul type="none">
                ', array('$btnColor' => $this->arrayShiftTimes[$shift]['btn-color'], '$timeStart' => $this->arrayShiftTimes[$shift]['time-start'], '$timeEnd' => $this->arrayShiftTimes[$shift]['time-end'], '$shift' => $shift));
                    if (isset($dateObject->arrayShiftObjectsByShift[$shift])) {
                        foreach ($dateObject->arrayShiftObjectsByShift[$shift] as $shiftObject) {
                            // var_dump($arrayShift);
                            if ($this->id_user !== $shiftObject->memberObject->id_user) {
                                $nickname = $shiftObject->memberObject->nickname;
                                $classTextColor = '';
                                $classBgColor = '';
                            } else {
                                $nickname = 'YOU';
                                $classTextColor = 'text-light';
                                $classBgColor = 'bg-primary';
                            }
                            echo "
                                                    <li class='$classBgColor $classTextColor'>$nickname</li>";
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
                            <div class="col-md-4 col-right">
                                <div class="row">
                                <div class="col-12">
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
                if (isset($dateObject->arrayShiftObjectsByShift[$shift])) {
                    foreach ($dateObject->arrayShiftObjectsByShift[$shift] as $shiftObject) {
                        if ($this->id_user !== $shiftObject->memberObject->id_user) {
                            $nickname = $shiftObject->memberObject->nickname;
                            $active = '';
                            echo strtr('
                                                <li class="list-group-item $active">
                                                    <div class="dropdown" data-toggle="dropdown">
                                                        $nickname
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            <div class="dropdown-header">$nickname</div>
                                                            <a class="dropdown-item" href="#">Call this shift</a>
                                                            <a class="dropdown-item" href="#">Send message</a>
                                                        </div>
                                                    </div>
                                                </li>
                    ', array('$active' => $active, '$nickname' => $nickname));
                        } else {
                            $nickname = 'YOU';
                            $active = 'active';
                            echo strtr('
                                                <li class="list-group-item $active">
                                                    $nickname
                                                </li>
                    ', array('$active' => $active, '$nickname' => $nickname));
                        }
                    }
                }
                echo '
                                            </ul>
                                        </div>'; // .col-10
                echo '
                                    </div>'; // .row
            }
            echo '
                                </div>
                                </div>
                                </div>'; // .shift-member-table .col-12 .row
            echo '
                                <a class="a-popover" data-toggle="popover" data-content="Loads member\'s specific information from DB and summarises them for every date." data-trigger="hover" data-placement="bottom">Summary</a>
                                <div class="row">
                                    <div class="col-12">';
            foreach (array_keys($dateObject->arrayNumLangsByPart) as $idxPart) {
                // var_dump(array_keys($dateObject->arrayNumLangsByPart));
                $partName = $this->arrayPartNames[$idxPart];
                echo "
                                    <div class='row no-gutters'><div class='div-grid-lang col-2'><p>$partName</p></div><div class='col-10 d-flex justify-content-center flex-wrap'>";
                foreach ($dateObject->arrayNumLangsByPart[$idxPart] as $lang => $num) {
                    if (isset($dateObject->arrayLangsByPart[$idxPart][$lang])){
                        $numLangNeeded = $dateObject->arrayLangsByPart[$idxPart][$lang];
                        // echo "For date $dateObject->date, part $idxPart $lang needs $numLangNeeded";
                        // $numLangNeeded === NULL doesn't matter
                        if ($num === $numLangNeeded) {
                            $classTextColor = 'text-warning';
                        } elseif ($num < $numLangNeeded) {
                            $classTextColor = 'text-danger';
                        } else {
                            $classTextColor = 'text-light';
                        }
                    } else {
                        // No numNeeded
                        $classTextColor = 'text-light';
                    }
                    echo "
                                        <div class='div-country-flag m-1'>
                                            <img src='./data/png/icon-button-$lang.png'>
                                            <div class='div-num-lang text-center $classTextColor'>$num</div>
                                        </div>
                                            ";
                }
                echo "
                                    </div></div>"; // .col-10 .row
            }
            echo '
                                    </div>
                                </div>'; // .col-12 .row

            echo '
                            </div>
                        </div>
                    </div>
                </div>
            </div>'; // .col-md-4 .row .card-body .collapse .card
        }

        // Close Accordion
        echo '
        </div>';
    }
}

$daily_member_handler = new DailyMembersHandler($master_handler, $config_handler);
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
<a class="a-popover" data-toggle="popover" data-content="Choose year and n-th week to search shifts!" data-trigger="hover" data-placement="bottom">SEARCH BAR</a>
<?php $daily_member_handler->echoSearchBar() ?>
<!-- Accordion -->
<a class="a-popover" data-toggle="popover" data-content="Custom schedule design provides users quick grasp of assigned coleagues" data-trigger="hover" data-placement="bottom">Shifts on Date</a>
<?php $daily_member_handler->echoAccordion() ?>
<script src="<?= $config_handler->http_host ?>/js/custom_schedule.js"></script>
<script>
    // text color of card-headers
    $('#accordion .card-header a').addClass('text-dark')
</script>