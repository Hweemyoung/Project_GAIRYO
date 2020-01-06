<?php
function getDateTimeOfDayFromWeek($Y, $W, $day = 'Sun')
{
    // find first day of the year
    $firstDate = strtotime("$day Jan $Y");
    // calculate how much weeks to add
    $weeksOffset = $W - date('W', $firstDate);
    // calculate searched day
    $dateTimeOfDay = new DateTime(strtotime("+$weeksOffset week", $firstDate));
    return $dateTimeOfDay;
}

function groupArrayByKey($array, $key)
{
    $arrayGrouped = array();
    foreach ($array as $element) {
        $arrayGrouped[$element[$key]][] = $element;
    }
    return $arrayGrouped
}

// Set year
if (!isset($_GET["Y"])) {
    $Y = date('Y', time());
} else {
    $Y = $_GET["Y"];
}

// Hyperparameters
$YLowerBound = 2020;

// Find max page and max year
$sql = 'SELECT date_shift FROM shifts_assigned ORDER BY date_shift DESC LIMIT 1';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$dateLast = $stmt->fetchAll();
$dateLast = new DateTime($dateLast[0]["date_shift"]);
$pageMax = date('W', $dateLast->getTimestamp());
$YMax = date('Y', $dateLast->getTimestamp());
$pageUpperBound = date('W', strtotime("$Y-12-31"));

// Set current page
if (!isset($_GET["page"])) {
    $currentPage = $pageMax;
} else if ($_GET["page"] > $pageMax) {
    // RAISE ERROR
} else {
    $currentPage = $_GET["page"];
}

// Load shifts
$dateStart = getDateTimeOfDayFromWeek($Y, $currentPage, 'Sun')->format('M j (D)');
$dateEnd = getDateTimeOfDayFromWeek($Y, $currentPage, 'Sat')->format('M j (D)');
$sql = strtr('SELECT date_shift, id_user, shift FROM shifts_assigned WHERE date_shift >= $dateStart AND date_shift <= $dateEnd ORDER BY date_shift DESC', array('$dateStart' => $dateStart, '$dateEnd' => $dateEnd));
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayShiftsByDate = $stmt->fetchAll(PDO::FETCH_UNIQUE);
foreach (array_keys($arrayShiftsByDate) as $date){
    $arrayShiftsByDate[$date] = groupArrayByKey($arrayShiftsByDate[$date], "shift");
}
// $arrayShiftsByDate: Grouped firstly by date, secondly by shift
var_dump($stmt->errorInfo());
var_dump($arrayShiftsByDate);

function echoSearchBar($Y, $YLowerBound, $YMax, $currentPage, $pageUpperBound, $pageMax)
{
    echo '
        <!-- Search bar -->
        <div class="div-search jumbotron bg-light mb-2 p-2">
    ';
    echo '
            <div class="div-search-year">
                <div class="dropdown text-center">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">2020</button>
                    <div class="dropdown-menu">';
    if (($Y - 1) >= $YLowerBound){
        echo strtr('
                        <a href="#" class="dropdown-item">$YPrev</a>
    ', array('$YPrev'=>($Y - 1)));
    }
    echo strtr('
                        <a href="#" class="dropdown-item active">$Y</a>;
    ', array('$Y'=>$Y));
    if (($Y + 1) <= $YMax){
        echo strtr('
                        <a href="#" class="dropdown-item">$YNext</a>
        ', array('$YNext'=>($Y + 1)));
    }
    echo '
                    </div>
                </div>
            </div>
        ';
//     <!-- Search bar -->
//     <div class="div-search jumbotron bg-light mb-2 p-2">
//         <div class="div-search-year">
//             <div class="dropdown text-center">
//                 <button class="btn btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">2020</button>
//                 <div class="dropdown-menu">
//                     <a href="#" class="dropdown-item">2019</a>
//                     <a href="#" class="dropdown-item active">2020</a>
//                     <a href="#" class="dropdown-item disabled">2021</a>
//                 </div>
//             </div>
//         </div>
    echo '
            <div class="div-search-week">
                <ul class="pagination pagination-sm justify-content-center">
    ';
    echo '
                    <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-double-left"></i></a></li>
                    <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-left"></i></a></li>
    ';
    for ($i=2; $i>0; $i--){
        if (($currentPage - $i) > 0){
            $page = $currentPage - $i;
            echo strtr('
                    <li class="page-item"><a class="page-link" href="$url">$page</a></li>
            ', array('url'=>"./shifts.php?Y=$Y&page=$page", '$page'=>$page));
        }
    }
    echo strtr('
                    <li class="page-item active"><a class="page-link" href="#">$currentPage</a></li>
    
    ', array('$currentPage'=>$currentPage));
    for ($i=1; $i<=2; $i++){
        $page = $currentPage + $i;
        if ($page <= $pageUpperBound){
            if ($page <= $pageMax){
                echo strtr('
                    <li class="page-item"><a class="page-link" href="$url">$page</a></li>
                ', array('url'=>"./shifts.php?Y=$Y&page=$page", '$page'=>$page));
            } else {
                echo strtr('
                    <li class="page-item disabled"><a class="page-link" href="$url">$page</a></li>
                ', array('url'=>"./shifts.php?Y=$Y&page=$page", '$page'=>$page));
            }
        } else {
            break;
        }
    }
    echo '
                    <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>
                    <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-double-right"></i></a></li>
    ';
//         <div class="div-search-week">
//             <ul class="pagination pagination-sm justify-content-center">
                // <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-double-left"></i></a></li>
                // <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-left"></i></a></li>
                // <li class="page-item"><a class="page-link" href="#" title="Mar 1~7">12</a></li>
                // <li class="page-item"><a class="page-link" href="#" title="Mar 8~14">13</a></li>
                // <li class="page-item active"><a class="page-link" href="#" title="Mar 15~21">14</a></li>
//                 <li class="page-item disabled"><a class="page-link" href="#" title="Mar 22~28">15</a></li>
//                 <li class="page-item disabled"><a class="page-link" href="#" title="Mar 29~Apr 4">16</a></li>
                // <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>
                // <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-double-right"></i></a></li>

    echo '
                </ul>
            </div>
        </div>
        ';
//             </ul>
//         </div>
//     </div>
}

function echoAccordion($arrayShiftsByDate){
    $shiftA = array('time-start' => '07:40', 'time-end' => '12:00');
    $shiftB = array('time-start' => '08:00', 'time-end' => '13:30');
    $shiftH = array('time-start' => '08:00', 'time-end' => '13:00');
    $shiftC = array('time-start' => '12:30', 'time-end' => '18:00');
    $shiftD = array('time-start' => '13:30', 'time-end' => '18:00');
    $arrayShiftTimes = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);
    global $arrayMembersByIdUser;
    global $id_user;

    echo '
        <div id="accordion">
    ';
    foreach(array_keys($arrayShiftsByDate) as $date){
        $currentDateTime = new DateTime($date);
        $headerTitle = $currentDateTime->format('M j (D)');
        echo '
            <div class="card">
        ';
        echo strtr('
            <div class="card-header"><a href="$date" class="card-link" data-toggle="collapse">$headerTitle</a></div>
        ', array('$date'=>$date, '$headerTitle'=>$headerTitle));
        echo strtr('
        
            <div class="collapse show" data-parent="#accordion" id="$date">
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
            ', array('$date'=>$date));
        $matchShiftsAndColumns = array(array('A', 'C'), array('H', 'D'), array('B'));
        for($i=0; $i<count($matchShiftsAndColumns), $i++){
            echo strtr('
                <div class="column" id="column$idx">
            ', array('$i'=>($i+1)));
            foreach ($matchShiftsAndColumns[$i] as $shift) {
                echo '
                    <a class="btn btn-info" time-start="$timeStart" time-end="$timeEnd" data-toggle="modal">
                        <h5>$shift</h5>
                        <ul type="none">
                ', array('$timeStart'=>$arrayShiftTimes[$shift]['time-start'], '$timeEnd'=>$arrayShiftTimes[$shift]['time-end'], '$shift'=>$shift);
                foreach ($arrayShiftsByDate[$date][$shift] as $arrayShift){
                    $nickname = $arrayMembersByIdUser[$arrayShift["id_user"]]['nickname'];
                    echo "
                            <li>$nickname</li>";
                }
                echo '
                        </ul>
                    </a>';
            }
            echo '
                </div>';
        }
        echo '
            </div>';
        echo '
            <!-- col right -->
            <div class="col-md-4">
                <div class="shift-member-table">';
        foreach(array_keys($arrayShiftTimes) as $shift){
            echo '
                    <div class="row">
            ';
            echo strtr('
                        <div class="col-2 d-flex"><p>$shift</p></div>
            ', array('$shift'=>$shift));
            echo '
                        <div class="col-10">
                            <ul class="list-group">';
            foreach($arrayShiftsByDate[$date][$shift] as $arrayShift){
                if ($id_user !== $arrayShift["id_user"]){
                    $nickname = $arrayMembersByIdUser[$arrayShift["id_user"]]["nickname"];
                } else {
                    $nickname = 'YOU';
                }
                echo '
                                <li class="list-group-item">
                                    <div class="dropdown">';
                echo strtr('
                                        <a data-toggle="dropdown">$nickname</a>
                ', array('$nickname'=>$nickname));
                echo strtr('
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <div class="dropdown-header">$nickname</div>
                                            <a class="dropdown-item" href="#">Call this shift</a>
                                            <a class="dropdown-item" href="#">Send message</a>
                                        </div>
                                    </div>
                                </li>
                ', array('$nickname'=>$nickname));
            }
            echo '
                            </ul>
                        </div>';
            echo '
                    </div>';
        }
        echo '
                </div>
            </div>';
    }
    
    // Close Accordion
    echo'
        </div>';
}

?>
<div class="tab-pane fade" id="tab-content2">
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
    <?php echoSearchBar($Y, $YLowerBound, $YMax, $currentPage, $pageUpperBound, $pageMax)?>
    <!-- Accordion -->
    <?php echoAccordion($arrayShiftsByDate)?>
    <script src="./js/custom_schedule.js"></script>
    <script>
        // text color of card-headers
        $('#accordion .card-header a').addClass('text-dark')
    </script>
</div>