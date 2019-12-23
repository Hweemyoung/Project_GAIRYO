<?php
class userOrientedRequest
{
    public function __construct($id_user, $arrayRequest)
    {
        $this->id_user = $id_user;
        $this->arrayRequest = $arrayRequest;
        $this->transaction_order = $arrayRequest["transaction_order"];
        global $arrayMembers;
        if (intval($arrayRequest["id_from"]) === $id_user) {
            $this->position = 'from';
            $this->agreed_user = $arrayRequest["agreed_from"];
            $this->checked_user = $arrayRequest["checked_from"];
            $this->counterpart = $arrayMembers[intval($arrayRequest["id_to"]) + 1];
            $this->script = 'Your ' . $arrayRequest["date_shift"] . ' ' . $arrayRequest["shift"] . ' to ' . $this->counterpart["nickname"];
        } else {
            $this->position = 'to';
            $this->agreed_user = $arrayRequest["agreed_to"];
            $this->checked_user = $arrayRequest["checked_to"];
            $this->counterpart = $arrayMembers[intval($arrayRequest["id_from"]) + 1];
            $this->script = $this->counterpart["nickname"] . '\'s ' . $arrayRequest["date_shift"] . ' ' . $arrayRequest["shift"] . ' to you';
        }

        // Notification script
        switch ($arrayRequest["status"]) {
            case '0':
                $this->scriptNotice = 'Denied: ' . $this->script;
                break;
            case '1':
                $this->scriptNotice = 'Accepted: ' . $this->script;
                break;
            case '2':
                $this->scriptNotice = 'Awaiting: ' . $this->script;
                break;
        }
    }
}

$dbh = new PDO('mysql:host=localhost;dbname=gairyo', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

// Get nicknames of whole members
// Column"id_user" = $id_u
$sql = 'SELECT nickname FROM members';
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
// echo var_dump($arrayMembers);
// echo '<br>';

// Get requests
$sql = 'SELECT * FROM requests_pending WHERE id_from=:user_id OR id_to=:user_id ORDER BY time_proceeded DESC LIMIT 5';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_id = 2;
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo var_dump($requests);
echo '<br>';
// From array to object
for ($i = 0; $i < count($requests); $i++) {
    $requests[$i] = new userOrientedRequest($id_user, $requests[$i]);
}
echo var_dump($requests);
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
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Not Signed In</div>
                </div>
            </li>
            <!-- <li class="nav-item dropdown no-arrow">
                <a href="" class="nav-link dropdown-toggle text-light" role="button" data-toggle="dropdown">
                    <span class="badge badge-sm badge-warning">
                        <i class="fas fa-bell fa-fw"></i>
                    </span>
                </a>
                <span class="badge badge-sm badge-warning">3
                </span>
                <div class="dropdown-menu dropdown-menu-right">
                    <div class="dropdown-header">Notices</div>
                    <a href="#" class="dropdown-item">Notice 1</a>
                    <a href="#" class="dropdown-item">Notice 2</a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">Action</a>
                </div>
            </li> -->
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
            <!-- <img src="./data/png/list-2x.png" alt="navbar-toggler-icon"> -->
            <i class="fas fa-bars"></i>
        </button>
        <!-- menu -->
        <div class="collapse navbar-collapse order-sm-2" id="navMenu">
            <ul class="navbar-nav">
                <li class="nav-item"><a href="./admin.php" class="nav-link">Overview</a></li>
                <li class="nav-item"><a href="./shifts.php" class="nav-link">Shifts</a></li>
                <li class="nav-item"><a href="./transactions.php" class="nav-link">Transactions</a></li>
                <li class="nav-item"><a href="./logs.php" class="nav-link">Logs</a></li>
                <li class="nav-item"><a href="./forms.php" class="nav-link">Board</a></li>
            </ul>
        </div>
    </nav>
</section>