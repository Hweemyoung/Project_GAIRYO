<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/utils.php";
require_once "$homedir/config.php";

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

$sql = 'SELECT id_shift, date_shift, shift FROM shifts_assigned WHERE id_user=:id_user ORDER BY date_shift DESC';
$stmt = $dbh->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$arrayShifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
// var_dump($arrayShifts);OK

?>

<a class="a-popover" data-toggle="popover" title="Notices" data-content="Lists all valid shifts of user. Can create transaction at once!" data-trigger="hover" data-placement="bottom">My Shifts</a>
<h2>My Shifts</h2>
<table class="table table-striped">
    <tbody>
        <?php
        foreach ($arrayShifts as $arrayShift) {
            echo '
                    <tr>
                ';
            $date = new DateTime($arrayShift["date_shift"]);
            $dateShift = $date->format('M j (D)');
            $shift = $arrayShift["shift"];
            $classTextColor = utils\getClassTextColorForDay($date->format('D'));
            echo "
                        <td class='$classTextColor'>$dateShift</td>";
            echo "
                        <td>$shift</td>
                ";
            $hrefDailyMembers = utils\genHref($config_handler->http_host, 'shifts.php', $master_handler->arrPseudoUser + ['Y' => $date->format('Y'), 'page' => intval($date->format('W')), 'date' => $date->format('M_j')]) . '#' . $date->format('M_j');
            $date->format('W');
            $hrefRequest = utils\genHref($config_handler->http_host, 'transactionform.php', $master_handler->arrPseudoUser + ['id_from' => $master_handler->id_user, 'month' => $date->format('Y_M'), 'day' => $date->format('j'), 'shift' => $shift]);
            $hrefMarket = utils\genHref($config_handler->http_host, 'process/upload_market_item.php', $master_handler->arrPseudoUser + ['mode'=> 'put', 'id_from' => $master_handler->id_user, 'id_shift' => $arrayShift['id_shift'], 'date_shift' => $arrayShift['date_shift'], 'shift' => $shift]);
            $arrNames = ['id_from', 'id_shift', 'date_shift', 'shift'];
            echo "
                        <td>
                            <div class='dropdown'>
                                <a href='#' data-toggle='dropdown'><i class='fas fa-ellipsis-h'></i></a>
                                <div class='dropdown-menu dropdown-menu-right'>
                                    <a class='dropdown-item' href='$hrefDailyMembers'>See daily members</a>
                                    <a class='dropdown-item' href='$hrefRequest'>Request</a>
                                    <a class='dropdown-item' href='$hrefMarket'>To Market</a>
                                </div>
                            </div>
                        </td>
                ";
            echo '
                    </tr>
                ';
        }
        ?>
    </tbody>
</table>