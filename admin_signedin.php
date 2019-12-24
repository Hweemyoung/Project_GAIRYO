<?php
$shiftA = array('time-start' => '07:40', 'time-end' => '12:00');
$shiftB = array('time-start' => '08:00', 'time-end' => '13:30');
$shiftH = array('time-start' => '08:00', 'time-end' => '13:00');
$shiftC = array('time-start' => '12:30', 'time-end' => '18:00');
$shiftD = array('time-start' => '13:30', 'time-end' => '18:00');
$shifts = array('A' => $shiftA, 'B' => $shiftB, 'H' => $shiftH, 'C' => $shiftC, 'D' => $shiftD);

$sql = 'SELECT date_shift, shift, num_request, id_shift FROM shifts_assigned ORDER BY date_shift DESC WHERE id_user = :id_user LIMIT 1';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$result = $stmt->fetchAll();
echo var_dump($result);
if ($result[0]["under_request"] !== '0') {
    // If there are pending requests regarding this shift
    $sql = 'SELECT id_to, id_request FROM requests_pending WHERE id_shift = :id_shift AND `status` = 2';
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':id_shift', $id_shift);
    $id_shift = $result[0]["id_shift"];
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_UNIQUE);
    foreach($result)
}
?>

<main>
    <div class="container px-1">
        <section id="section-shift">
            <div class="card" id="card-shift">
                <div class="card-header d-flex align-middle">
                    <a href="#shift-content" class="card-link mr-auto" data-toggle="collapse">
                        <?php
                        echo 'Next: ' . date('M j(D)', strtotime($result[0]["date_shift"])) . ' ' . $result[0]["shift"];
                        // Like 'Next: Jan 23(Thu) A'
                        ?>
                    </a>
                    <i class="fas fa-exclamation-triangle text-danger"></i>
                    <i class="fas fa-exclamation-triangle text-warning"></i>
                </div>

                <div class="collapse show" id="shift-content">
                    <div class="card-body">
                        <h5 class="mb-0 text-center">
                            <?php
                            echo date('Y/n/j(D)', strtotime($result[0]["date_shift"])) . ' ' . $result[0]["shift"];
                            ?>
                        </h5>
                        <!-- row 1 -->
                        <div class="row no-gutters pb-2">
                            <div class="col-6 px-1 text-center d-flex">
                                <div id="div-your-shift">
                                    <h1 class="display-3">
                                        <?php
                                        echo $result[0]["shift"];
                                        // Like 'B'
                                        ?>
                                    </h1>
                                    <p class="">
                                        <?php
                                        echo $shifts[$result[0]["shift"]]['time-start'] . '~' . $shifts[$result[0]["shift"]]['time-end'];
                                        // Like '08:00~13:00'
                                        ?>
                                    </p>
                                    <div class="d-block d-md-flex" id="div-warnings">
                                        <!-- Put warning -->
                                        <div class="dropdown mx-md-1">
                                            <div class="div-warning bg-danger text-light p-1" data-toggle="dropdown">
                                                <!-- <div class="pseudo"></div> -->
                                                <div class="">
                                                    <p>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Being PUT to:<br>
                                                    </p>
                                                    <p>
                                                        Another Member
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-header">PUT to ANOTHER MEMBER</div>
                                                <a href="#" class="dropdown-item">
                                                    Pull back
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item">
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Call warning -->
                                        <div class="dropdown mx-md-1">
                                            <div class="div-warning bg-danger text-light p-1" data-toggle="dropdown">
                                                <div class="">
                                                    <p>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Being CALLED by:<br>
                                                    </p>
                                                    <p>
                                                        Another Member
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-header">CALL by ANOTHER MEMBER</div>
                                                <a href="#" class="dropdown-item">
                                                    Accept
                                                </a>
                                                <a href="#" class="dropdown-item">
                                                    Decline
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item">
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                        <!-- Advertising warning -->
                                        <div class="dropdown mx-md-1">
                                            <div class="div-warning bg-warning text-dark p-1" data-toggle="dropdown">
                                                <div class="">
                                                    <p>
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        Being Advertised<br>
                                                    </p>
                                                </div>
                                            </div>
                                            <div class="dropdown-menu">
                                                <div class="dropdown-header">ADVERTISING</div>
                                                <a href="#" class="dropdown-item">
                                                    Pull back
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a href="#" class="dropdown-item">
                                                    Details
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- row 2 -->
                            <div class="col-6 px-0">
                                <div id="shift-member-table">
                                    <div class="row">
                                        <div class="col-md-6">
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
                                        <div class="col-md-6">
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
                                                <div class="px-0 col-2 d-flex">
                                                    <p>D</p>
                                                </div>
                                                <div class="px-0 col-10">
                                                    <ul class="list-group">
                                                        <li class="list-group-item text-info">
                                                            <div class="dropdown">
                                                                <a data-toggle="dropdown">
                                                                    Member(Toshi)
                                                                </a>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    <div class="dropdown-header">Member</div>
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
                                                                    <div class="dropdown-header">Member</div>
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
            <div class="row">
                <div class="col-md-6">

                    <div class="div-list-title d-flex">
                        <h5 class="mx-auto">Requests</h5>
                        <a href="#"><i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action list-group-item-info">
                            <span>上段固定アイテム</span>
                            <span>23 Oct 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <span>普通のお知らせ1</span>
                            <div class="badge badge-sm badge-primary">new</div>
                            <span>2 Dec 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action list-group-item-warning">
                            <span>重要なお知らせ</span>
                            <span>1 Dec 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <span>普通のお知らせ2</span>
                            <span>30 Nov 2019</span>
                        </a>
                        <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                        <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                    </div>
                </div>
                <div class="col-md-6">

                    <div class="div-list-title d-flex">
                        <h5 class="mx-auto">Notices</h5>
                        <a href="#"><i class="fas fa-angle-right"></i></a>
                    </div>
                    <div class="list-group">
                        <a href="#" class="list-group-item list-group-item-action list-group-item-info">
                            <span>上段固定アイテム</span>
                            <span>23 Oct 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <span>普通のお知らせ1</span>
                            <div class="badge badge-sm badge-primary">new</div>
                            <span>2 Dec 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action list-group-item-warning">
                            <span>重要なお知らせ</span>
                            <span>1 Dec 2019</span>
                        </a>
                        <a href="#" class="list-group-item list-group-item-action">
                            <span>普通のお知らせ2</span>
                            <span>30 Nov 2019</span>
                        </a>
                        <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                        <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                    </div>
                </div>
            </div>
        </section>
        <hr>
        <section id="section-history">
        </section>
    </div>
</main>