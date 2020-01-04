<?php
function echoTr($arrayShifts)
{
    foreach($arrayShifts as $shift){

        echo '
            <tr>';
        echo strtr('
                <td>$date_shift</td>
                <td>$shift</td>',
                array('$date_shift'=>$shift["date_shift"], '$shift'=>$shift["shift"]));
        echo strtr('
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="$urlDailyMembers">See daily members</a>
                            <a class="dropdown-item" href="#">Put</a>
                            <a class="dropdown-item" href="#">Advertise</a>
                        </div>
                    </div>
                </td>
        ', array('$urlDailyMembers'=> './transactions.php?'));
        echo '
            </tr>'
    }
}

$sql = 'SELECT * FROM shifts_assigned WHERE id_user=:id_user AND `status`=1 ORDER BY date_shift ASC';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$arrayShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="tab-pane fade" id="tab-content1">
    <h5 class="text-center">Shifts Assigned</h5>
    <table class="table table-striped">
        <tbody>
            <?php

            ?>
            <tr>
                <td>2020/1/15</td>
                <td>H</td>
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#">See daily members</a>
                            <a class="dropdown-item" href="#">Put</a>
                            <a class="dropdown-item" href="#">Advertise</a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2020/1/13</td>
                <td>D</td>
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#">See daily members</a>
                            <a class="dropdown-item" href="#">Put</a>
                            <a class="dropdown-item" href="#">Advertise</a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2020/1/7</td>
                <td>B</td>
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#">See daily members</a>
                            <a class="dropdown-item" href="#">Put</a>
                            <a class="dropdown-item" href="#">Advertise</a>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>2020/1/2</td>
                <td>B</td>
                <td>
                    <div class="dropdown">
                        <a href="#" data-toggle="dropdown"><i class="fas fa-ellipsis-h"></i></a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="#">See daily members</a>
                            <a class="dropdown-item" href="#">Put</a>
                            <a class="dropdown-item" href="#">Advertise</a>
                        </div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>