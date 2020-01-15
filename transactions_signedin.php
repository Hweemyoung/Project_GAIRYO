<?php
class hrefGenerator
{
    public function __construct($idUser, $idTrans)
    {
        $this->idUser = $idUser;
        $this->idTrans = $idTrans;
    }
    function getHref($mode)
    {
        return strtr('
        ./register_agree.php?mode=$mode&id_user=$idUser&id_transaction=$idTrans
        ', array('$mode' => $mode, '$idUser' => $this->idUser, '$idTrans' => $this->idTrans));
    }
}

function prepArrayIdShiftsByIdTrans($id_user, $arrayRequestsByIdTrans, $arrayMemberObjectsByIdUser, $dbh)
{
    foreach (array_keys($arrayRequestsByIdTrans) as $idTrans) {
        for ($i = 0; $i < count($arrayRequestsByIdTrans[$idTrans]); $i++) {
            $arrayRequestsByIdTrans[$idTrans][$i] = new userOrientedRequest($id_user, $arrayRequestsByIdTrans[$idTrans][$i], $arrayMemberObjectsByIdUser, $dbh);
        }
    }
    return $arrayRequestsByIdTrans;
}

function genSqlConditions($arrayIdTrans)
{
    for ($i = 0; $i < count($arrayIdTrans); $i++) {
        $arrayIdTrans[$i] = 'id_transaction=' . $arrayIdTrans[$i];
    }
    return '(' . implode(' OR ', $arrayIdTrans) . ') AND `status`=2;';
}

function echoTrsTrans($arrayRequestsByIdTrans)
{
    if (count($arrayRequestsByIdTrans) === 0) {
        echo "
        <tr><td class='align-middle' colspan=7>No Transactions Found</td></tr>
        ";
    } else {
        foreach (array_keys($arrayRequestsByIdTrans) as $idTrans) {
            $disabled = '';
            foreach ($arrayRequestsByIdTrans[$idTrans] as $requestObject) {
                if ($requestObject->agreedUser) {
                    $disabled = 'disabled';
                    break;
                }
            }
            $numRequests = count($arrayRequestsByIdTrans[$idTrans]);
            for ($i = 0; $i < $numRequests; $i++) {
                $requestObject = $arrayRequestsByIdTrans[$idTrans][$i];
                if ($requestObject->position === '3rd') {
                    $classBg = '';
                } else {
                    // $classBg = 'font-weight-bold';
                    $classBg = 'mark';
                }
                switch($requestObject->dateTime->format('w')){
                    case '0':
                        // Sun
                        $classText = 'text-danger';
                        break;
                    case '6':
                        // Sat
                        $classText = 'text-primary';
                        break;
                    default:
                        $classText = '';
                }
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
            <td class='align-middle $classBg $classText'>$dateShift</td>
            <td class='align-middle $classBg'>$shift</td>
            <td class='align-middle $classBg'>$nicknameTo</td>";
                if ($i === 0) {
                    $hrefGen = new hrefGenerator($idUser, $idTrans);
                    $hrefDecline = $hrefGen->getHref('decline');
                    $hrefAgree = $hrefGen->getHref('agree');
                    echo "
            <td class='align-middle' rowspan='$numRequests'>$nicknameCreated</td>
            <td class='align-middle' rowspan='$numRequests'>
                <div class='div-buttons'>
                    <a href=$hrefDecline class='btn btn-danger' title='Decline'><i class='fas fa-ban'></i></a>
                    <a href=$hrefAgree class='btn btn-success $disabled' title='Agree'><i class='fas fa-handshake'></i></a>
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

$sql = "SELECT id_transaction FROM requests_pending WHERE `status`=2 AND (id_from=$id_user OR id_to=$id_user);";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$sqlConditions = genSqlConditions(array_keys($stmt->fetchAll(PDO::FETCH_GROUP)));
$sql = "SELECT id_transaction, id_transaction, id_request, id_from, id_to, id_shift, id_created, time_proceeded, agreed_from, agreed_to, checked_from, checked_to, `status` FROM requests_pending WHERE " . $sqlConditions;
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayRequestsByIdTrans = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
// Every arrayRequest to Object
$arrayRequestsByIdTrans = prepArrayIdShiftsByIdTrans($id_user, $arrayRequestsByIdTrans, $arrayMemberObjectsByIdUser, $dbh);

?>

<main>
    <div class="container px-1">
        <section id="section-form-choices">
            <div class="row text-center">
                <div class="col-md-6 my-1">
                    <a href="./transactionform.php" class="btn btn-primary d-block"><i class="fas fa-plus-square"> Create Transaction</i></a>
                </div>
                <div class="col-md-6 my-1">
                    <a href="./marketplace.php" class="btn btn-primary d-block"><i class="fas fa-search-dollar"> Marketplace</i></a>
                </div>
            </div>
        </section>
        <hr>
        <h2>Upcoming Transactions</h2>
        <section id="section-transactions-list">
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
                    echoTrsTrans($arrayRequestsByIdTrans);
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
    </div>
</main>