<?php
function echoTr($arrayShifts)
{
    foreach ($arrayShifts as $shift) {

        echo '
            <tr>';
        echo strtr(
            '
                <td>$date_shift</td>
                <td>$shift</td>',
            array('$date_shift' => $shift["date_shift"], '$shift' => $shift["shift"])
        );
        echo '
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="$urlDailyMembers">See daily members</a>
                            <a class="dropdown-item" href="$urlRequest">Put</a>
                            <a class="dropdown-item" href="$urlAdvertise">Advertise</a>
                        </div>
                    </div>
                </td>
        ';
        echo '
            </tr>';
    }
}

$sql = 'SELECT * FROM shifts_assigned WHERE id_user=:id_user ORDER BY date_shift DESC';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$arrayShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($arrayShifts);OK

?>


<h5 class="text-center">Shifts Assigned</h5>
<table class="table table-striped">
    <tbody>
        <?php
        foreach ($arrayShifts as $arrayShift) {
            echo '
                    <tr>
                ';
            $date = new DateTime($arrayShift["date_shift"]);
            $date = $date->getTimestamp();
            $dateShift = date('M j (D) ', $date);
            $shift = $arrayShift["shift"];
            switch (intval(date('w', $date))) {
                case 0:
                    echo strtr('
                        <td class="text-danger">$dateShift</td>
                        ', array('$dateShift' => $dateShift));
                    break;
                case 6:
                    echo strtr('
                        <td class="text-primary">$dateShift</td>
                        ', array('$dateShift' => $dateShift));
                    break;
                default:
                    echo "
                        <td>$dateShift</td>
                        ";
                    break;
            }
            echo "
                        <td>$shift</td>
                ";
            echo '
                        <td>
                            <div class="dropdown">
                                <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="#">See daily members</a>
                                    <a class="dropdown-item" href="#">Request</a>
                                    <a class="dropdown-item" href="#">Advertise</a>
                                </div>
                            </div>
                        </td>
                ';
            echo '
                    </tr>
                ';
        }
        ?>
    </tbody>
</table>