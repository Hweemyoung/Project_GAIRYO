<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/class/class_user_oriented_request.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/utils.php";

class TransactionsLister extends DBHandler
{
    public function __construct($master_handler, $config_handler)
    {
        $this->master_handler = $master_handler;
        $this->dbh = $master_handler->dbh;
        $this->id_user = $master_handler->id_user;
        $this->config_handler = $config_handler;
        $this->http_host = $config_handler->http_host;
        $this->sleepSeconds = $config_handler->sleepSeconds;
        $this->arrayMemberObjectsByIdUser = $master_handler->arrayMemberObjectsByIdUser;
        $this->arrayShiftsByPart = $config_handler->arrayShiftsByPart;
        $this->url = 'process/register_agree.php';
        $this->process();
    }

    public function process()
    {
        $this->beginTransactionIfNotIn();
        // Requests
        $this->loadRequests();
        // Market item
        $this->loadMarketItem();
    }

    private function loadRequests()
    {
        $sql = "SELECT id_transaction FROM requests_pending WHERE `status`=2 AND (id_from=$this->id_user OR id_to=$this->id_user) AND (id_from IS NOT NULL AND id_to IS NOT NULL);";
        $stmt = $this->querySql($sql);
        $results = $stmt->fetchAll(PDO::FETCH_GROUP);
        $stmt->closeCursor();
        $sqlConditions = $this->genSqlConditions(array_keys($results), 'id_transaction', 'OR');
        $sql = "SELECT id_transaction, id_transaction, id_request, id_from, id_to, id_shift, id_created, time_proceeded, agreed_from, agreed_to, checked_from, checked_to, `status` FROM requests_pending WHERE " . $sqlConditions . ' AND `status`=2';
        $stmt = $this->querySql($sql);
        $this->arrayRequestsByIdTrans = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
        $stmt->closeCursor();
        // Every arrayRequest to Object
        $this->arrayRequestsByIdTrans = $this->prepArrayIdShiftsByIdTrans($this->id_user, $this->arrayRequestsByIdTrans, $this->arrayMemberObjectsByIdUser, $this->dbh);
    }

    private function prepArrayIdShiftsByIdTrans($id_user, $arrayRequestsByIdTrans, $arrayMemberObjectsByIdUser, $dbh)
    {
        foreach (array_keys($arrayRequestsByIdTrans) as $idTrans) {
            for ($i = 0; $i < count($arrayRequestsByIdTrans[$idTrans]); $i++) {
                $arrayRequestsByIdTrans[$idTrans][$i] = new userOrientedRequest($id_user, $arrayRequestsByIdTrans[$idTrans][$i], $arrayMemberObjectsByIdUser, $dbh);
            }
        }
        return $arrayRequestsByIdTrans;
    }

    public function echoTrsTrans()
    {
        if (count($this->arrayRequestsByIdTrans) === 0) {
            echo "
        <tr><td class='align-middle' colspan=7>No Transactions Found</td></tr>
        ";
        } else {
            foreach (array_keys($this->arrayRequestsByIdTrans) as $idTrans) {
                $disabled = '';
                foreach ($this->arrayRequestsByIdTrans[$idTrans] as $requestObject) {
                    if ($requestObject->agreedUser) {
                        $disabled = 'disabled';
                        break;
                    }
                }
                $numRequests = count($this->arrayRequestsByIdTrans[$idTrans]);
                for ($i = 0; $i < $numRequests; $i++) {
                    $requestObject = $this->arrayRequestsByIdTrans[$idTrans][$i];
                    if ($requestObject->position === '3rd') {
                        $classBg = '';
                    } else {
                        // $classBg = 'font-weight-bold';
                        $classBg = 'mark';
                    }
                    $classTextColor = utils\getClassTextColorForDay($requestObject->dateTime->format('D'));
                    echo "
        <tr id='{$requestObject->idTrans}'>";
                    if ($i === 0) {
                        echo "
            <td class='align-middle' rowspan='$numRequests'>$idTrans</td>";
                    }

                    $nicknameFrom = $requestObject->nicknameFrom;
                    $nicknameTo = $requestObject->nicknameTo;
                    $dateShift = $requestObject->dateTime->format('M j (D)');
                    $shift = $requestObject->shift;
                    $nicknameCreated = $requestObject->nicknameCreated;
                    $idUser = $requestObject->idUser;
                    echo "
            <td class='align-middle $classBg'>$nicknameFrom</td>
            <td class='align-middle $classBg $classTextColor'>$dateShift</td>
            <td class='align-middle $classBg'>$shift</td>
            <td class='align-middle $classBg'>$nicknameTo</td>";
                    if ($i === 0) {
                        // $hrefDecline = $hrefGen->getHref('decline', $idUser, $idTrans);
                        // $hrefAgree = $hrefGen->getHref('agree', $idUser, $idTrans);
                        $hrefDecline = utils\genHref($this->http_host, $this->url, ['mode' => 'decline', 'id_user' => $idUser, 'id_transaction' => $idTrans]);
                        $hrefAgree = utils\genHref($this->http_host, $this->url, ['mode' => 'agree', 'id_user' => $idUser, 'id_transaction' => $idTrans]);
                        echo "
            <td class='align-middle' rowspan='$numRequests'>$nicknameCreated</td>
            <td class='align-middle' rowspan='$numRequests'>
                <div class='div-buttons'>
                    <a href=$hrefDecline class='btn btn-danger m-1' title='Decline'><i class='fas fa-ban'></i></a>
                    <a href=$hrefAgree class='btn btn-success m-1 $disabled' title='Agree'><i class='fas fa-handshake'></i></a>
                </div>
            </td>
                ";
                        echo "
        </tr>
            ";
                    }
                }
            }
        }
    }

    private function setIdTransactionForShiftPutObjects($requestObjectsByIdShift)
    {
        foreach ($this->arrShiftPutObjectsByDate as $arrShiftPutObjects) {
            foreach ($arrShiftPutObjects as $shiftPutObject) {
                $shiftPutObject->id_transaction = $requestObjectsByIdShift[$shiftPutObject->id_shift]->id_transaction;
                // echo '$shiftPutObject->id_transaction:' . $shiftPutObject->id_transaction . '<br>';
            }
        }
    }

    private function loadMarketItem()
    {
        // Load market item
        // Put
        $sql = "SELECT id_shift, id_from, id_to, id_transaction FROM requests_pending WHERE `status`=2 AND (id_from=$this->id_user AND id_to IS NULL) ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $requestObjectsByIdShift = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        if (count($requestObjectsByIdShift)) {
            // Put: load all put shifts
            $sqlConditions = $this->genSqlConditions(array_keys($requestObjectsByIdShift), 'id_shift', 'OR');
            $sql = "SELECT date_shift, shift, id_user, id_shift FROM shifts_assigned WHERE done=0 AND $sqlConditions;";
            $stmt = $this->querySql($sql);
            $this->arrShiftPutObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$this->master_handler, $this->config_handler]);
            $stmt->closeCursor();
            // Set id_request to every ShiftObject
            $this->setIdTransactionForShiftPutObjects($requestObjectsByIdShift);
        } else {
            $this->arrShiftPutObjectsByDate = [];
        }

        // Call
        $sql = "SELECT date_shift, shift, id_transaction FROM requests_pending WHERE `status`=2 AND (id_from IS NULL AND id_to=$this->id_user) AND id_shift=NULL ORDER BY time_created ASC;";
        $stmt = $this->querySql($sql);
        $this->arrCallRequestsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'RequestObject');
        $stmt->closeCursor();
        // Call: group by shift
        if (count($this->arrCallRequestsByDate)) {
            try {
                $this->arrCallRequestsByDate = utils\groupArrayByValue($this->arrCallRequestsByDate, 'shift');
            } catch (Exception $e) {
                echo "Caught exception: " . $e->getMessage();
                exit;
            }
        }
    }

    // public function echoListGroupPuts()
    // {
    // if (!count($this->arrShiftPutObjectsByDate)) {
    // echo 'No put item!';
    // } else {
    // echo '
    // <ul class="list-group">';
    // foreach ($this->arrShiftPutObjectsByDate as $date_shift => $arrShiftPutObjects) {
    // $dateTime = DateTime::createFromFormat('Y-m-d', $date_shift);
    // $date = $dateTime->format('M j');
    // $day = $dateTime->format('D');
    // $classTextColor = utils\getClassTextColorForDay($day);
    // foreach ($arrShiftPutObjects as $shiftPutObject) {
    // $hrefDecline = utils\genHref($this->http_host, $this->url, ['mode' => 'decline', 'id_user' => $this->id_user, 'id_transaction' => $shiftPutObject->id_transaction]);
    // echo "
    // <li class='list-group-item d-flex justify-content-between align-items-center'>
    // <span><span class='$classTextColor'>$date ($day)</span> $shiftPutObject->shift</span>
    // <a href='$hrefDecline' class='btn btn-danger m-1' title='Decline'><i class='fas fa-ban'></i></a>
    // </li>";
    // }
    // }
    // echo '
    // </ul>';
    // }
    // }

    public function echoListGroup(string $mode, array $arrObjectsByDate)
    {
        $title = ucfirst($mode);
        $direction = ($mode === 'put') ? 'right' : 'left';
        $color = ($mode === 'put') ? 'success' : 'danger';
        echo "
        <div class='div-list-title d-flex justify-content-center'><h5 class='mr-1'>$title</h5><i class='fas fa-lg fa-hand-holding-heart'></i><i class='fas fa-lg fa-long-arrow-alt-$direction text-$color'></i></div>
        ";
        // $mode: 'put' or 'call'
        if (!count($arrObjectsByDate)) {
            echo "<div class='d-flex'><span class='mx-auto'>No $mode item!</span></div>";
        } else {
            echo '
        <ul class="list-group">';
            foreach ($arrObjectsByDate as $date_shift => $arrObjects) {
                $dateTime = DateTime::createFromFormat('Y-m-d', $date_shift);
                $date = $dateTime->format('M j');
                $day = $dateTime->format('D');
                $classTextColor = utils\getClassTextColorForDay($day);
                foreach ($arrObjects as $Object) {
                    $hrefDecline = utils\genHref($this->http_host, $this->url, ['mode' => 'decline', 'id_user' => $this->id_user, 'id_transaction' => $Object->id_transaction]);
                    echo "
            <li class='list-group-item d-flex justify-content-between align-items-center py-0'>
                <span><span class='$classTextColor'>$date ($day)</span> $Object->shift</span>
                <a href='$hrefDecline' class='btn btn-danger m-1' title='Decline'><i class='fas fa-ban'></i></a>
            </li>";
                }
            }
            echo '
        </ul>';
        }
    }
    // public function echoListGroupCalls()
    // {
    // if (!count($this->arrCallRequestsByDate)) {
    // echo 'No call item!';
    // } else {
    // echo '
    // <ul class="list-group list-group-flush">';
    // foreach ($this->arrCallRequestsByDate as $date_shift => $arrCallRequests) {
    // $dateTime = DateTime::createFromFormat('Y-m-d', $date_shift);
    // $date = $dateTime->format('M j');
    // $day = $dateTime->format('D');
    // $classTextColor = utils\getClassTextColorForDay($day);
    // foreach ($arrCallRequests as $callRequest) {
    // $hrefDecline = utils\genHref($this->http_host, $this->url, ['mode' => 'decline', 'id_user' => $this->id_user, 'id_transaction' => $callRequest->id_transaction]);
    // echo "
    // <li class='list-group-item d-flex justify-content-between align-items-center'>
    // <span><span class='$classTextColor'>$date ($day)</span> $callRequest->shift</span>
    // <a href='$hrefDecline' class='btn btn-danger m-1' title='Decline'><i class='fas fa-ban'></i></a>
    // </li>";
    // }
    // }
    // echo '
    // </ul>';
    // }
    // }
}

$transactions_lister = new TransactionsLister($master_handler, $config_handler);
?>

<main>

    <section id="section-transactions-list">
        <a class="a-popover" data-toggle="popover" data-content="Lists requests awaiting for agreements from concerned members. Here users can always agree or decline requests, as long as transaction hasn't been executed." data-trigger="hover" data-placement="bottom">Requests list</a>
        <div class="section-title row no-gutters mb-3">
            <div class="col-md-8 my-1">
                <h2>Upcoming Requests</h2>
            </div>
            <div class="col-md-4 my-1"><a href="<?= $config_handler->http_host ?>/transactionform.php" class="btn btn-primary d-block"><i class="fas fa-plus-square"></i> <strong>Create Requests</strong></a></div>
        </div>
        <table class="table table-responsive-md text-center">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>From</th>
                    <th>Date</th>
                    <th>Shift</th>
                    <th>To</th>
                    <th>Creater</th>
                    <th>Answer</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $transactions_lister->echoTrsTrans();
                ?>
                <!-- <tr>
                        <td rowspan="2">$idTrans</td>
                        <td>$nicknameFrom</td>
                        <td>$dateShift</td>
                        <td>$shift</td>
                        <td>$nicknameTo</td>
                        <td rowspan="2">
                            <div class="div-buttons">
                                <a href="" class="btn btn-danger" title="Decline"><i class="fas fa-ban"></i></a>
                                <a href="" class="btn btn-success" title="Agree"><i class="fas fa-handshake"></i></a>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>$nicknameFrom</td>
                        <td>$dateShift</td>
                        <td>$shift</td>
                        <td>$nicknameTo</td>
                    </tr> -->
            </tbody>
        </table>
    </section>
    <section id="section-market-items">
        <a class="a-popover" data-toggle="popover" data-content="Just like stock exchange, users can put or call their shifts in the market. Here users cannot designate specific user for product. For same product(i.e same date, same shift), first come, first served." data-trigger="hover" data-placement="bottom">Market item lists</a>
        <div class="section-title row no-gutters mb-3">
            <div class="col-md-8 my-1">
                <h2>Your Market Item</h2>
            </div>
            <div class="col-md-4 my-1"><a href="<?= $config_handler->http_host ?>/marketplace.php" class="btn btn-primary d-block"><i class="fas fa-search-dollar"></i> <strong>Go to Marketplace</strong></a></div>
        </div>
        <div class="row">
            <!-- Put -->
            <div class="col-sm-6 my-2">
                <?php $transactions_lister->echoListGroup('put', $transactions_lister->arrShiftPutObjectsByDate); ?>
            </div>
            <!-- Call -->
            <div class="col-sm-6 my-2">
                <?php $transactions_lister->echoListGroup('call', $transactions_lister->arrCallRequestsByDate); ?>
            </div>
        </div>
    </section>

</main>