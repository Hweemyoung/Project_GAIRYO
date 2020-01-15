<?php
class userOrientedRequest
{
    // This object is not for market item i.e. id_to cannot be NULL.
    public function __construct($id_user, $arrayRequest, $arrayMembersByIdUser, $dbh)
    {
        $this->idUser = $id_user;
        $this->arrayRequest = $arrayRequest;
        $this->idTrans = $arrayRequest["id_transaction"];
        $this->timeProceeded = $arrayRequest["time_proceeded"];
        $this->nicknameFrom = $arrayMembersByIdUser[$arrayRequest["id_from"]]["nickname"];
        $this->nicknameTo = $arrayMembersByIdUser[$arrayRequest["id_to"]]["nickname"];
        $this->nicknameCreated = $arrayMembersByIdUser[$arrayRequest["id_created"]]["nickname"];
        $sql = 'SELECT date_shift, shift FROM shifts_assigned WHERE id_shift=:id_shift';
        $this->idShift = $arrayRequest["id_shift"];
        $idShift = $this->idShift;
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':id_shift', $idShift, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll();
        $this->dateShift = $result[0]["date_shift"];
        $this->shift = $result[0]["shift"];
        $this->status = $arrayRequest["status"];
        if ($arrayRequest["id_from"] === $id_user) {
            $this->position = 'from';
            $this->nicknameFrom = 'YOU';
            $this->agreedUser = $arrayRequest["agreed_from"];
            $this->checkedUser = $arrayRequest["checked_from"];
            $this->script = 'Your ' . $this->dateShift . ' ' . $this->shift . ' to ' . $this->nicknameTo;
        } else if ($arrayRequest["id_to"] === $id_user) {
            $this->position = 'to';
            $this->nicknameTo = 'YOU';
            $this->agreedUser = $arrayRequest["agreed_to"];
            $this->checkedUser = $arrayRequest["checked_to"];
            $this->script = $this->nicknameFrom . '\'s ' . $this->dateShift . ' ' . $this->shift . ' to you';
        } else {
            $this->position = '3rd';
            $this->agreedUser = NULL;
            $this->checkedUser = NULL;
            $this->counterpart = NULL;
            $this->script = NULL;
        }

        // Notification script
        switch ($this->status) {
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

// Get requests
$sql = 'SELECT id_transaction, id_from, id_to, id_shift, id_created, time_proceeded, agreed_from, agreed_to, checked_from, checked_to, `status` FROM requests_pending WHERE id_from=:id_user OR id_to=:id_user ORDER BY time_proceeded DESC LIMIT 5';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($requests); OK
// From array to object

for ($i = 0; $i < count($requests); $i++) {
    // var_dump($requests[$i]);
    $requests[$i] = new userOrientedRequest($id_user, $requests[$i], $arrayMembersByIdUser, $dbh);
}
// echo '$requests = ';
// echo var_dump($requests);
// echo '<br>';
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
                        echo '<a href="./logs.php?id_transaction="' . $request->idTrans . '" class="dropdown-item">' . $requests[$i]->scriptNotice . '</a>';
                    }
                    ?>
                    <!-- Like <a href="./transactions.php?id_transaction=3" class="dropdown-item">Request 1</a> -->
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">More</a>
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