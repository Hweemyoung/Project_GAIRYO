<?php
$shiftA = array('time-start' => '07:40', 'time-end' => '12:00');
$shiftB = array('time-start' => '08:00', 'time-end' => '13:30');
$shiftH = array('time-start' => '08:00', 'time-end' => '13:00');
$shiftC = array('time-start' => '12:30', 'time-end' => '18:00');
$shiftD = array('time-start' => '13:30', 'time-end' => '18:00');
$shifts = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);

function getNicksAndAd($nextShift, $arrayMemberObjectsByIdUser)
{
    global $dbh;
    if (count($nextShift)) {
        if ($nextShift[0]["under_request"] == '1') {
            $arrayNicknamesTo = array();
            $advertising = false;

            // If there are pending requests regarding this shift
            $sql = 'SELECT id_to, requests_pending.* FROM requests_pending WHERE id_shift=:id_shift AND (id_from IS NOT NULL AND id_to IS NOT NULL) AND `status` = 2';
            $stmt = $dbh->prepare($sql);
            $stmt->bindParam(':id_shift', $id_shift);
            $id_shift = $nextShift[0]["id_shift"];
            $stmt->execute();
            // var_dump($stmt->errorInfo()); OK
            $requestsOnNextShift = $stmt->fetchAll(PDO::FETCH_UNIQUE);
            // var_dump($requestsOnNextShift); OK
            foreach (array_keys($requestsOnNextShift) as $id_to) {
                if ($id_to == NULL) {
                    $advertising = true;
                } else {
                    array_push($arrayNicknamesTo, $arrayMemberObjectsByIdUser[$id_to]->nickname);
                }
            }
            // $arrayNicknamesTo = array('0'=>'nickname0', '1'=>'nickname1', ...)
            $nicksAndAd = array("arrayNicknamesTo" => $arrayNicknamesTo, "advertising" => $advertising);
            return $nicksAndAd;
        } else {
            return false;
        }
    }
}

function echoRequestWarning($nicksAndAd)
{
    if ($nicksAndAd["arrayNicknamesTo"]) {
        // If requests exists
        echo '
            <!-- Request warning -->
                <div class="dropdown mx-md-1">
                    <div class="div-warning bg-danger text-light p-1" data-toggle="dropdown">
                        <!-- <div class="pseudo"></div> -->
                        <div class="">
                            <p>
                                <i class="fas fa-exclamation-triangle"></i>
                                Under request to:<br>
                            </p>
                            <p>';
        foreach ($nicksAndAd["arrayNicknamesTo"] as $nickname) {
            echo "{$nickname}<br>";
        }
        echo '
                            </p>
                        </div>
                    </div>
                    <div class="dropdown-menu">
                        <div class="dropdown-header">REQUESTS</div>
                        <div class="dropdown-divider"></div>
                        <a href="#" class="dropdown-item">
                            Details
                        </a>
                    </div>
                </div>';
    }
}

function echoAdvertisingWarning($nicksAndAd)
{
    if ($nicksAndAd["advertising"]) {
        echo '
        <!-- Advertising warning -->
        <div class="dropdown mx-md-1">
            <div class="div-warning bg-warning text-dark p-1" data-toggle="dropdown">
                <div class="">
                    <p>
                        <i class="fas fa-exclamation-triangle"></i>
                        On Market<br>
                    </p>
                </div>
            </div>
            <div class="dropdown-menu">
                <div class="dropdown-header">On Market</div>
                <div class="dropdown-divider"></div>
                <a href="#" class="dropdown-item">
                    Details
                </a>
            </div>
        </div>';
    }
}

function echoShiftMemberElements($arrayMemberObjectsByIdUser, $arrayShiftMembers)
{
    foreach (array_keys($arrayShiftMembers) as $shift) {
        echo strtr(
            '
        <div class="row">
            <div class="col-2 d-flex">
                <p>$shift</p>
            </div>
            <div class="col-10">
                <ul class="list-group">',
            array('$shift' => $shift)
        );
        foreach ($arrayShiftMembers[$shift] as $member) {
            // var_dump(intval($member["id_user"]));
            $nickname = $arrayMemberObjectsByIdUser[$member["id_user"]]->nickname;
            echo strtr(
                '
                    <li class="list-group-item">
                        <div class="dropdown" data-toggle="dropdown">
                            $nickname
                            <div class="dropdown-menu dropdown-menu-right">
                                <div class="dropdown-header">$nickname</div>
                                <a class="dropdown-item" href="#">Create request</a>
                            </div>
                        </div>
                    </li>',
                array('$nickname' => $nickname)
            );
        }
        echo '
                </ul>
            </div>
        </div>
        ';
    }
}

function echoRequestsList($requests)
{
    echo '
        <div class="div-list-title d-flex">
            <h5 class="mx-auto">Requests</h5>
            <a href="#"><i class="fas fa-angle-right"></i></a>
        </div>
        <div class="list-group">';
    foreach ($requests as $request) {
        // $request: userOrientedRequest
        $scriptNotice = $request->scriptNotice;
        $timeProceeded = date('j M Y', strtotime($request->timeProceeded));

        if (!$request->checkedUser) {
            echo '
            <a href="#" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action list-group-item-info">';
        } else {
            echo '
            <a href="#" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action">';
        }
        echo strtr(
            '
            <span>$scriptNotice</span>',
            array('$scriptNotice' => $scriptNotice)
        );

        // if (!$request->checkedUser) {
        // echo '
        // <div class="badge badge-sm badge-primary">new</div>
        // ';
        // }

        echo strtr(
            '
            <span>$timeProceeded</span>
            </a>',
            array('$timeProceeded' => $timeProceeded)
        );
    }
    echo '
        </div>
    ';
    // <div class="div-list-title d-flex">
    // <h5 class="mx-auto">Requests</h5>
    // <a href="#"><i class="fas fa-angle-right"></i></a>
    // </div>
    // <div class="list-group">
    // <a href="#" class="list-group-item list-group-item-action list-group-item-info">
    // <span>上段固定アイテム</span>
    // <span>23 Oct 2019</span>
    // </a>
    // <a href="#" class="list-group-item list-group-item-action">
    // <span>普通のお知らせ1</span>
    // <div class="badge badge-sm badge-primary">new</div>
    // <span>2 Dec 2019</span>
    // </a>
}

function echoBoardList($arrayBoardItems)
{
    echo '
        <div class="div-list-title d-flex">
            <h5 class="mx-auto">Board</h5>
            <a href="#"><i class="fas fa-angle-right"></i></a>
        </div>
        <div class="list-group">';
    foreach ($arrayBoardItems as $boardItem) {
        $title = $boardItem["title"];
        $dateCreated = date('j M Y', strtotime($boardItem["date_created"]));

        if (!array_values($boardItem)[count($boardItem) - 1]) {
            echo '
            <a href="#" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action list-group-item-info">';
        } else {
            echo '
            <a href="#" class="list-group-item d-flex justify-content-between align-items-center list-group-item-action">';
        }
        echo strtr(
            '
            <span>$title</span>',
            array('$title' => $title)
        );

        // if (!$request->checkedUser) {
        // echo '
        // <div class="badge badge-sm badge-primary">new</div>
        // ';
        // }

        echo strtr(
            '
            <span>$dateCreated</span>
            </a>',
            array('$dateCreated' => $dateCreated)
        );
    }
    echo '
        </div>
    ';
}

function echoExclamations($nicksAndAd)
{
    if ($nicksAndAd) {
        if ($nicksAndAd["arrayNicknamesTo"]) {
            echo '
                <i class="fas fa-exclamation-triangle text-danger"></i> 
            ';
        }
        // <i class="fas fa-exclamation-triangle text-danger"></i>
        if ($nicksAndAd["advertising"]) {
            echo '
                <i class="fas fa-exclamation-triangle text-warning"></i>
            ';
        }
    }
    // <i class="fas fa-exclamation-triangle text-warning"></i>
}

// Get Next shift
$sql = 'SELECT * FROM shifts_assigned WHERE id_user = :id_user ORDER BY date_shift ASC LIMIT 1';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
// var_dump($stmt->errorInfo()); OK
$nextShift = $stmt->fetchAll();
// var_dump($nextShift);

// Get requests and advertisement
// var_dump($nextShift); OK
$nicksAndAd = getNicksAndAd($nextShift, $master_handler->arrayMemberObjectsByIdUser);
// var_dump($nicksAndAd); OK

// Get Shift members
if (count($nextShift)) {

    $date_shift = $nextShift[0]["date_shift"];
    // var_dump($date_shift); OK
    $sql = 'SELECT shift, shifts_assigned.* FROM shifts_assigned WHERE date_shift = :date_shift';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':date_shift', $date_shift);
    $stmt->execute();
    // var_dump($stmt->errorInfo()); OK
    $arrayShiftMembers = $stmt->fetchAll(PDO::FETCH_GROUP);
    // var_dump($arrayShiftMembers);OK
    // $arrayShiftMembers = array('A'=>array(0=>array('other columns'=>'field values'), 1=>...), 'B'=>array(...), ...)
}

// Get Board Items
$sql = strtr('SELECT id_board_item, title, content, date_created, checked_$id_user FROM board ORDER BY date_created DESC LIMIT 5', array('$id_user' => $id_user));
$stmt = $dbh->prepare($sql);
$stmt->execute();
// var_dump($stmt->errorInfo());OK
$arrayBoardItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($arrayBoardItems);OK

?>

<main>
    <section id="section-shift">
        <a class="a-popover" data-toggle="popover" title="Upcoming shift" data-content="Selects closest upcoming shift and coleagues from DB!" data-trigger="hover" data-placement="bottom">FEATURE</a>
        <h2>Upcoming Shift</h2>
        <div class="card" id="card-shift">
            <div class="card-header d-flex align-middle">
                <a href="#shift-content" class="card-link mr-auto" data-toggle="collapse">
                    <?php
                    if (count($nextShift)) {
                        $nextDate = date('M j (D)', strtotime($nextShift[0]["date_shift"]));
                        echo "Next: {$nextDate} {$nextShift[0]["shift"]}";
                        // Like 'Next: Jan 23(Thu) A'
                    } else {
                        echo "No upcoming shifts!";
                    }
                    ?>
                </a>
                <?php
                echoExclamations($nicksAndAd);
                ?>
            </div>

            <div class="collapse show" id="shift-content">
                <div class="card-body">
                    <?php if (count($nextShift)) { ?>
                        <h5 class="mb-0 text-center">
                            <?php
                            echo date('Y M j (D)', strtotime($nextShift[0]["date_shift"])) . ' ' . $nextShift[0]["shift"];
                            // Like: '2020/1/3(Mon) A'
                            ?>
                        </h5>
                        <!-- row 1 -->
                        <div class="row no-gutters pb-2">
                            <div class="col-6 px-1 text-center d-flex">
                                <div id="div-your-shift">
                                    <h1 class="display-3">
                                        <?php
                                        echo $nextShift[0]["shift"];
                                        // Like: B
                                        ?>
                                    </h1>
                                    <p class="">
                                        <?php
                                        echo $shifts[$nextShift[0]["shift"]]['time-start'] . '~' . $shifts[$nextShift[0]["shift"]]['time-end'];
                                        // Like '08:00~13:00'
                                        ?>
                                    </p>
                                    <div class="d-block d-md-flex" id="div-warnings">
                                        <?php
                                        // <!-- Request warning -->
                                        echoRequestWarning($nicksAndAd);
                                        //<!-- Advertising warning -->
                                        echoAdvertisingWarning($nicksAndAd);
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <!-- row 2 -->
                            <div class="col-6 px-0">
                                <div id="shift-member-table">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?php
                                            // var_dump($master_handler->arrayMemberObjectsByIdUser);
                                            echoShiftMemberElements($master_handler->arrayMemberObjectsByIdUser, array_slice($arrayShiftMembers, 0, 3));
                                            // 'A', 'H', 'B'
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php
                                            echoShiftMemberElements($master_handler->arrayMemberObjectsByIdUser, array_slice($arrayShiftMembers, 3));
                                            // 'C', 'D'
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="card-footer p-2">
                <div class="row no-gutters text-center">
                    <div class="col-4 px-0">
                        <button class="btn btn-sm btn-danger" type="button">Request</button>
                    </div>
                    <div class="col-4 px-0">
                        <button class="btn btn-sm btn-warning" type="button">Advertise</button>
                    </div>
                    <div class="col-4 px-0">
                        <button class="btn btn-sm btn-secondary" type="button">Details</button>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <hr>
    <section id="section-boards">
        <a class="a-popover" data-toggle="popover" title="Notices" data-content="New features are bg-colored. Loading this page handles DB, setting status of item to 'checked' status, and will lose bg-colors further." data-trigger="hover" data-placement="bottom">FEATURE</a>
        <div class="row">
            <div class="col-md-6">
                <?php
                echoRequestsList($requests);
                ?>
            </div>
            <div class="col-md-6">
                <?php
                echoBoardList($arrayBoardItems);
                ?>
            </div>
        </div>
    </section>
    <hr>
    <section id="section-history">
    </section>
</main>