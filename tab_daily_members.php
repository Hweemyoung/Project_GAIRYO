<?php
function echoSearchBar($arrayShifts)
{
}

function getFirstAndLastDate($Y, $W){
    // find first mоnday of the year
    $firstSun = strtotime("sun jan $Y");
    // calculate how much weeks to add
    $weeksOffset = $W - date('W', $firstSun);
    // calculate searched monday
    $sunOfWeek = strtotime("+$weeksOffset week", $firstSun);
    return $sunOfWeek;
}

// Set year
if (!isset($_GET["Y"])){
    $Y = date('Y', time());
}

// Find last week of shifts
$sql = 'SELECT date_shift FROM shifts_assigned ORDER BY date_shift DESC LIMIT 1';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$dateLast = $stmt->fetchAll();
$dateLast = new DateTime($pageCurrent[0]["date_shift"]);
$pageCurrent = intval(date('W', $pageCurrent->getTimestamp()));
$dateFirst

$sql = strtr('SELECT * FROM shifts_assigned WHERE date_shift LIKE $Y% AND date_shift ORDER BY date_shift DESC', array('$Y'=>$Y));
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($arrayShifts);OK

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
    <div class="div-search jumbotron bg-light mb-2 p-2">
        <div class="div-search-year">
            <div class="dropdown text-center">
                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-toggle="dropdown">2020</button>
                <div class="dropdown-menu">
                    <a href="#" class="dropdown-item">2019</a>
                    <a href="#" class="dropdown-item active">2020</a>
                    <a href="#" class="dropdown-item disabled">2021</a>
                </div>
            </div>
        </div>
        <div class="div-search-week">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-double-left"></i></a></li>
                <li class="page-item"><a class="page-link" href="#"><i class="fas fa-angle-left"></i></a></li>
                <li class="page-item"><a class="page-link" href="#" title="Mar 1~7">12</a></li>
                <li class="page-item"><a class="page-link" href="#" title="Mar 8~14">13</a></li>
                <li class="page-item active"><a class="page-link" href="#" title="Mar 15~21">14</a>
                </li>
                <li class="page-item disabled"><a class="page-link" href="#" title="Mar 22~28">15</a></li>
                <li class="page-item disabled"><a class="page-link" href="#" title="Mar 29~Apr 4">16</a></li>
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-right"></i></a></li>
                <li class="page-item disabled"><a class="page-link" href="#"><i class="fas fa-angle-double-right"></i></a></li>
            </ul>
        </div>
    </div>
    <!-- Accordion -->
    <div id="accordion">
        <div class="card">
            <div class="card-header"><a href="#day1" class="card-link" data-toggle="collapse">Monday</a></div>
            <div class="collapse" data-parent="#accordion" id="day1">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day2" class="card-link" data-toggle="collapse">Tuesday</a></div>
            <div class="collapse" data-parent="#accordion" id="day2">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day3" class="card-link" data-toggle="collapse">Wednesday</a></div>
            <div class="collapse" data-parent="#accordion" id="day3">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day4" class="card-link" data-toggle="collapse">Thurday</a></div>
            <div class="collapse show" data-parent="#accordion" id="day4">
                <div class="card-body">
                    <div class="row no-gutters">
                        <!-- col left -->
                        <div class="col-md-8" id="schedule">
                            <div class="div-schedule">
                                <!-- timeline -->
                                <div class="timeline">
                                    <ul type="none">
                                    </ul>
                                </div>
                                <div class="div-columns">
                                    <div class="column" id="column1">
                                        <a class="btn btn-info" time-start="07:30" time-end="12:00" data-toggle="modal">
                                            <h5>A</h5>
                                            <ul type="none">
                                                <li>Member1</li>
                                                <li>Member2</li>
                                                <li>Member3</li>
                                                <li>Member4</li>
                                            </ul>
                                        </a>
                                        <a class="btn btn-secondary" time-start="12:30" time-end="18:00" data-toggle="modal">
                                            <h5>C</h5>
                                            <ul type="none">
                                                <li>Member1</li>
                                                <li>Member2</li>
                                                <li>Member3</li>
                                                <li>Member4</li>
                                            </ul>
                                        </a>
                                    </div>
                                    <div class="column" id="column2">
                                        <a class="btn btn-success" time-start="13:30" time-end="18:00" data-toggle="modal">
                                            <h5>D</h5>
                                            <ul type="none">
                                                <li>Member1</li>
                                                <li>Member2</li>
                                                <li>Member3</li>
                                                <li>Member4</li>
                                            </ul>
                                        </a>
                                        <a class="btn btn-dark text-light" time-start="08:00" time-end="13:00" data-toggle="modal">
                                            <h5>H</h5>
                                            <ul type="none">
                                                <li>Member1</li>
                                                <li>Member2</li>
                                                <li>Member3</li>
                                                <li>Member4</li>
                                            </ul>
                                        </a>
                                    </div>
                                    <div class="column" id="column3">
                                        <a class="btn btn-warning" time-start="08:00" time-end="13:30" data-toggle="modal">
                                            <h5>B</h5>
                                            <ul type="none">
                                                <li>Member1</li>
                                                <li>Member2</li>
                                                <li>Member3</li>
                                                <li>Member4</li>
                                            </ul>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <script src="./js/custom_schedule.js"></script>
                        </div>
                        <!-- col right -->
                        <div class="col-md-4">
                            <div class="shift-member-table">
                                <div class="row">
                                    <div class="col-2 d-flex">
                                        <p>A</p>
                                    </div>
                                    <div class="col-10">
                                        <ul class="list-group">
                                            <li class="list-group-item px-1">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-2 d-flex">
                                        <p>H</p>
                                    </div>
                                    <div class="col-10">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item text-info">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member(Toshi)
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-2 d-flex">
                                        <p>B</p>
                                    </div>
                                    <div class="col-10">
                                        <ul class="list-group">
                                            <li class="list-group-item active">YOU</li>
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-2 d-flex">
                                        <p>C</p>
                                    </div>
                                    <div class="col-10">
                                        <ul class="list-group">
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-2 d-flex">
                                        <p>D</p>
                                    </div>
                                    <div class="col-10">
                                        <ul class="list-group">
                                            <li class="list-group-item text-info">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member(Toshi)
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                            <li class="list-group-item">
                                                <div class="dropdown">
                                                    <a data-toggle="dropdown">
                                                        Member
                                                    </a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <div class="dropdown-header">Member
                                                        </div>
                                                        <a class="dropdown-item" href="#">Call this
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Put your
                                                            shift</a>
                                                        <a class="dropdown-item" href="#">Send
                                                            message</a>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day5" class="card-link" data-toggle="collapse">Friday</a>
            </div>
            <div class="collapse" data-parent="#accordion" id="day5">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day6" class="card-link" data-toggle="collapse">Saturday</a></div>
            <div class="collapse" data-parent="#accordion" id="day6">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header"><a href="#day7" class="card-link" data-toggle="collapse">Sunday</a>
            </div>
            <div class="collapse" data-parent="#accordion" id="day7">
                <div class="card-body">
                    <div class="row no-gutters">
                        <div class="col-md-8">
                            <div class="div-schedule"></div>
                        </div>
                        <div class="col-md-4">
                            <div class="shift-member-table"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        // text color of card-headers
        $('#accordion .card-header a').addClass('text-dark')
    </script>
</div>