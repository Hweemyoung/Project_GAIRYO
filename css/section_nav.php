<?php
$id_user = 2;
global $dbh;
$dbh = new PDO('mysql:host=localhost;dbname=gairyo', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
function scriptRequest($request, $id_user)
{
    if ($request["id_from"] === $id_user) {
        $counterpart = $dbh . $request["id_to"];
        $script = 'Your ' . $request["date_shift"] . ' ' . $request["shift"] . ' to ' . $request["id_to"];
    } else {
        $script = $request["id_from"] . '\'s ' . $request["date_shift"] . ' ' . $request["shift"] . ' to you';
    }
    if ($request["time_proceeded"]) {
        switch ($request["status"]) {
            case 0:
                $script = 'Denied: ' . $script;
                break;
            case 1:
                $script = 'Accepted: ' . $script;
                break;
        }
    } else {
        $script = 'Awaiting agreement(s): ' . $script;
    }
    echo $script;
}

$sql = 'SELECT * FROM requests_pending WHERE id_from=:user_id or id_to=:user_id ORDER BY time_proceeded DESC, time_created DESC LIMIT 5';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_id = 2;
$stmt->execute();
$requests = $stmt->fetchAll();

echo '<br>';
?>

<section id="section-nav">
    <nav class="navbar navbar-expand-sm bg-light fixed-top">
        <!-- logo -->
        <a href="#" class="navbar-brand order-sm-1 d-flex">
            <img class="d-none d-md-block mr-md-4" src="./data/png/logo_travel_color_large.png" alt="imgLogo">
            <p class="d-none d-sm-block mr-md-4">外国人旅行センター</p>
            <p class="d-sm-none">外旅</p>
        </a>
        <!-- Navbar -->
        <ul class="px-0 ml-auto mr-2 my-0 order-sm-3" id="navbar">
            <li class="nav-item dropdown no-arrow">
                <a href="" class="nav-link dropdown-toggle text-light" role="button" data-toggle="dropdown">
                    <span class="badge badge-sm badge-danger">
                        <i class="fas fa-exchange-alt"></i>
                    </span>
                </a>
                <span class="badge badge-sm badge-danger">
                    <? echo count($requests); ?>
                </span>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Requests</div>
                    <a href="#" class="dropdown-item">Request 1</a>
                    <a href="#" class="dropdown-item">Request 2</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">Action</a>
                </div>
            </li>
            <li class="nav-item dropdown no-arrow">
                <a href="" class="nav-link dropdown-toggle text-light" role="button" data-toggle="dropdown">
                    <span class="badge badge-sm badge-warning">
                        <i class="fas fa-bell fa-fw"></i>
                    </span>
                </a>
                <span class="badge badge-sm badge-warning">3</span>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Notices</div>
                    <a href="#" class="dropdown-item">Notice 1</a>
                    <a href="#" class="dropdown-item">Notice 2</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">Action</a>
                </div>
            </li>
            <!-- Account -->
            <li id="li-account" class="nav-item dropdown no-arrow">
                <a href="" id="btn-account" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown">
                    <span class="badge badge-sm badge-secondary">
                        <i id="i-sync" class="fas fa-sync"></i>
                    </span>
                </a>
                <div id="dropdown-account" class="dropdown-menu dropdown-menu-right d-none">
                    <div class="dropdown-header">Not Signed In</div>
                    <a href="#" class="dropdown-item">Notice 1</a>
                    <a href="#" class="dropdown-item">Notice 2</a>
                    <div class="dropdown-divider"></div>
                    <a id="dropdown-item-sign" href="#" class="dropdown-item" title="Sign In"><i class="fas fa-sign-in-alt"></i></a>
                </div>
            </li>
        </ul>
        <!-- nav-menu toggler -->
        <button class="navbar-toggler btn" data-toggle="collapse" data-target="#navMenu">
            <!-- <img src="./data/png/list-2x.png" alt="navbar-toggler-icon"> -->
            <i class="fas fa-bars"></i>
        </button>
        <!-- menu -->
        <div class="collapse navbar-collapse order-sm-2" id="navMenu">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="./admin.html" class="nav-link">Overview</a></li>
                <li class="nav-item"><a href="./shifts.html" class="nav-link">Shifts</a></li>
                <li class="nav-item"><a href="#" class="nav-link">History</a></li>
                <li class="nav-item"><a href="#" class="nav-link">News</a></li>
                <li class="nav-item"><a href="./forms.html" class="nav-link">Forms</a></li>
            </ul>
        </div>
    </nav>

</section>