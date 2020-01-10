<?php
class hrefGenerator{
    public function __construct($idUser, $idTrans)
    {
        $this->idUser = $idUser;
        $this->idTrans = $idTrans;
    }
    function getHref($mode){
        return strtr('
        ./register_agree.php?mode=decline&id_user=$idUser&id_transaction=$idTrans
        ',array('$idUser'=>$this->idUser, '$idTrans'=>$this->idTrans));
    }
}

function prepArrayIdShiftsByIdTrans($id_user, $arrayRequestsByIdTrans)
{
    foreach (array_keys($arrayRequestsByIdTrans) as $idTrans) {
        for ($i = 0; $i < count($arrayRequestsByIdTrans[$idTrans]); $i++) {
            $arrayRequestsByIdTrans[$idTrans][$i] = new userOrientedRequest($id_user, $arrayRequestsByIdTrans[$idTrans][$i]);
        }
    }
    return $arrayRequestsByIdTrans;
}

function echoTrsTrans($arrayRequestsByIdTrans)
{
    foreach (array_keys($arrayRequestsByIdTrans) as $idTrans) {
        $numRequests = count($arrayRequestsByIdTrans[$idTrans]);
        for ($i = 0; $i < $numRequests; $i++) {
            echo "
        <tr>";
            if ($i === 0){
                echo "
            <td rowspan='$numRequests'>$idTrans</td>";
            }
            $requestObject = $arrayRequestsByIdTrans[$idTrans][$i];
            $nicknameFrom = $requestObject->nicknameFrom;
            $nicknameTo = $requestObject->nicknameTo;
            $dateShift = $requestObject->dateShift;
            $shift = $requestObject->shift;
            $idUser = $requestObject->idUser;
            echo "
            <td>$nicknameFrom</td>
            <td>$dateShift</td>
            <td>$shift</td>
            <td>$nicknameTo</td>";
            if ($i === 0) {
                $hrefGen = new hrefGenerator($idUser, $idTrans);
                $hrefDecline = $hrefGen->getHref('decline');
                $hrefAgree = $hrefGen->getHref('agree');
                echo "
            <td rowspan='$numRequests'>
                <div class='div-buttons'>
                    <a href=$hrefDecline class='btn btn-danger' title='Decline'><i class='fas fa-ban'></i></a>
                    <a href=$hrefAgree class='btn btn-success' title='Agree'><i class='fas fa-handshake'></i></a>
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

$sql = "SELECT id_transaction, id_shift, id_created, time_proceeded, agreed_from, agreed_to, checked_from, checked_to, `status` FROM requests_pending WHERE id_from=$id_user OR id_to=$id_user;";
$stmt = $dbh->prepare($sql);
$stmt->execute();
$arrayRequestsByIdTrans = $stmt->fetchAll(PDO::FETCH_GROUP);
// Every arrayRequest to Object
$arrayRequestsByIdTrans = prepArrayIdShiftsByIdTrans($id_user, $arrayRequestsByIdTrans);

?>

<main>
    <div class="container px-1">
        <section id="section-form-choices">
            <div class="row text-center">
                <div class="col-6">
                    <a href="./transactionform.php" class="btn btn-primary">Create Requests</a>
                </div>
                <div class="col-6">
                    <a href="./marketplace.php" class="btn btn-primary">Marketplace</a>
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