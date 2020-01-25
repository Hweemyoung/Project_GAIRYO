<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_user_oriented_request.php";
class CommonNavHandler
{
    function genHref(array $params){
        if ($params['status'] == 2){
            return strtr('
            ./transactions.php#$idTrans', array('$idTrans'=>$params['idTrans']));
        } elseif($params['status'] == 1) {
            return strtr('./shifts.php#$idRequest', array('$idRequest'=>$params['idRequest']));
        } else {
            return '';
        }
    }
}

$common_nav_handler = new CommonNavHandler();

// Get requests
$sql = 'SELECT id_transaction, id_request, id_from, id_to, id_shift, id_created, time_proceeded, agreed_from, agreed_to, checked_from, checked_to, `status` FROM requests_pending WHERE (id_from=:id_user OR id_to=:id_user) AND (id_from IS NOT NULL AND id_to IS NOT NULL) ORDER BY time_proceeded DESC LIMIT 5';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt->closeCursor();
// var_dump($requests); OK
// From array to object
for ($i = 0; $i < count($requests); $i++) {
    $requests[$i] = new userOrientedRequest($id_user, $requests[$i], $arrayMemberObjectsByIdUser, $dbh);
}
// echo '$requests = ';
// echo var_dump($requests);
// echo '<br>';
?>

<section id="section-nav">
    <nav class="navbar navbar-expand-sm bg-light fixed-top">
        <!-- logo -->
        <a href="#" class="navbar-brand order-sm-1 d-flex">
            <img class="d-none d-md-block mr-md-3" src="<?=$config_handler->http_host?>/data/png/logo_travel_color_large.png" alt="imgLogo">
            <p class="d-none d-sm-block mr-md-2 small">外国人旅行センター</p>
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
                    <?php
                    $numNew = 0;
                    for ($i = 0; $i < count($requests); $i++) {
                        if (!intval($requests[$i]->checkedUser)) {
                            $numNew++;
                        }
                    }
                    echo $numNew;
                    ?>
                </span>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Requests</div>
                    <?php
                    for ($i = 0; $i < count($requests); $i++) {
                        $request = $requests[$i];
                        $href = $common_nav_handler->genHref(array('status'=>$request->status, 'idTrans'=>$request->idTrans, 'idRequest'=>$request->idRequest));
                        echo strtr('<a href="$href" class="dropdown-item">$scriptNotice</a>', array('$href' => $href, '$scriptNotice'=>$requests[$i]->scriptNotice));
                    }
                    ?>
                    <div class="dropdown-divider"></div>
                    <a href="<?=$config_handler->http_host?>/transactions.php" class="dropdown-item">More</a>
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
                    <div class="dropdown-divider"></div>
                    <a id="dropdown-item-sign" href="#" class="dropdown-item" title="Sign In"><i class="fas fa-sign-in-alt"></i></a>
                </div>
            </li>
        </ul>
        <!-- nav-menu toggler -->
        <button class="navbar-toggler btn" data-toggle="collapse" data-target="#navMenu">
            <!-- <img src="<?=$config_handler->http_host?>/data/png/list-2x.png" alt="navbar-toggler-icon"> -->
            <i class="fas fa-bars"></i>
        </button>
        <!-- menu -->
        <div class="collapse navbar-collapse order-sm-2" id="navMenu">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="<?=$config_handler->http_host?>/admin.php" class="nav-link">Overview</a></li>
                <li class="nav-item"><a href="<?=$config_handler->http_host?>/shifts.php" class="nav-link">Shifts</a></li>
                <li class="nav-item"><a href="<?=$config_handler->http_host?>/transactions.php" class="nav-link">Transactions</a></li>
                <li class="nav-item"><a href="<?=$config_handler->http_host?>/logs.php" class="nav-link">Logs</a></li>
                <li class="nav-item"><a href="<?=$config_handler->http_host?>/forms.php" class="nav-link">Board</a></li>
            </ul>
        </div>
    </nav>
</section>