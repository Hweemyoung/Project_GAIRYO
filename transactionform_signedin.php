<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/config.php";
require_once "$homedir/class/class_date_objects_handler.php";
require_once "$homedir/utils.php";

$transactionform_handler = new DateObjectsHandler($master_handler, $config_handler);
$sql = "SELECT date_shift, id_user, shift FROM shifts_assigned WHERE done=0 ORDER BY date_shift ASC";
$stmt = $master_handler->dbh->query($sql);
$arrayShiftObjectsByDate = $stmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_CLASS, 'ShiftObject', [$master_handler, $config_handler]);
$stmt->closeCursor();
$transactionform_handler->setArrayDateObjects($arrayShiftObjectsByDate);
// var_dump($transactionform_handler->arrayDateObjects);

// $sql = "SELECT date_shift, id_user, shift, id_shift FROM shifts_assigned WHERE done = 0";
// $stmt = $master_handler->dbh->prepare($sql);
// $stmt->execute();
// var_dump($stmt->errorInfo());OK
// $arrayShiftsByDate = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
// ksort($arrayShiftsByDate);
$sql = "SELECT id_user, date_shift, shift FROM shifts_assigned WHERE done = 0 AND id_user>0";
$stmt = $master_handler->dbh->prepare($sql);
$stmt->execute();
// var_dump($stmt->errorInfo());OK
$arrayShiftsByIdUser = $stmt->fetchAll(PDO::FETCH_ASSOC | PDO::FETCH_GROUP);
ksort($arrayShiftsByIdUser);

// foreach(array_keys($arrayShiftsByIdUser) as $idUser){
// $arrayShiftsByIdUser[$idUser] = groupArrayByKey($arrayShiftsByIdUser[$idUser], 'date_shift');
// }

$arrayShifts = array('A', 'B', 'H', 'C', 'D');

?>

<main>
    <hr>
    <section>
        <a class="a-popover" data-toggle="popover" data-content="PHP loads requests that exists in DB and gives them to JS, checking everytime if requests trying to create is valid or not. If invalid, JS prevents users from confirming creation. Check out if it works!" data-trigger="hover" data-placement="bottom">User-oriented form</a>
        <form action="<?= utils\genHref($config_handler->http_host, 'process/upload_transaction.php', $master_handler->arrPseudoUser) ?>" method="POST">
            <input id="input-ids" type="hidden" name="formIDs" value="1">
            <div class="form-item" id="1">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <div class="form-group row no-gutters">
                            <div class="col-3 text-center div-label">
                                <label for="id_from">From</label>
                            </div>
                            <div class="col-9">
                                <select name="id_from" class="form-control px-1 select-id-from">
                                    <option value="0">Member</option>
                                    <?php
                                    foreach (array_keys($arrayShiftsByIdUser) as $idUser) {
                                        $nickname = $arrayMemberObjectsByIdUser[$idUser]->nickname;
                                        echo "
                                        <option value='$idUser'>$idUser $nickname</option>
                                            ";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row no-gutters">
                            <div class="col-4">
                                <select name="month" class="form-control px-1" disabled>
                                    <option value="0">Month</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="day" class="form-control px-1" disabled>
                                    <option value="0">Day</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select name="shift" id="shift" class="form-control px-1" disabled>
                                    <option value="0">Shift</option>
                                    <?php
                                    for ($i = 1; $i <= count($arrayShifts); $i++) {
                                        $shift = $arrayShifts[$i - 1];
                                        echo "
                                        <option value='$i'>$shift</option>    
                                            ";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group row no-gutters">
                            <div class="col-1 text-center div-label">
                                <label for="id_to">To</label>
                            </div>
                            <div class="col-7">
                                <select name="id_to" class="form-control px-1 select-id-to" disabled>
                                    <option value="0">Member</option>
                                    <?php
                                    foreach (array_keys($arrayShiftsByIdUser) as $idUser) {
                                        $nickname = $arrayMemberObjectsByIdUser[$idUser]->nickname;
                                        echo "
                                        <option value='$idUser'>$idUser $nickname</option>
                                            ";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-2 d-flex flex-column div-form-icons">
                                <i class="i-not-found fas fa-lg fa-clone text-warning mx-auto d-none"></i>
                                <i class="i-target-overlap fas fa-lg fa-compress-arrows-alt text-danger mx-auto d-none"></i>
                                <i class="i-shift-overlap fas fa-lg fa-share-alt-square text-info mx-auto d-none"></i>
                                <i class="i-lang-not-enough fas fa-lg fa-language text-danger mx-auto d-none"></i>
                            </div>
                            <div class="col-2">
                                <button class="btn btn-danger btn-delete ml-auto" title="Delete"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row no-gutters">
                    <div class="col-12">
                        <hr>
                    </div>
                </div>
            </div>
            <div class="modal fade" id="modal-confirm">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title">Confirm Requests</h3>
                        </div>
                        <div class="modal-body">
                            <table class="table table-sm table-hover text-center">
                                <thead>
                                    <tr>
                                        <th>From</th>
                                        <th>Month</th>
                                        <th>Day</th>
                                        <th>Shift</th>
                                        <th>To</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-danger" type="button" title="Back" data-dismiss="modal"><i class="fas fa-undo"></i></button>
                            <button class="btn btn-primary" type="submit" title="Confirm"><i class="fas fa-file-export"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="div-buttons" class="text-right">
            <i id="i-not-found" class="fas fa-lg fa-clone text-warning invisible"></i>
            <i id="i-target-overlap" class="fas fa-lg fa-compress-arrows-alt text-danger invisible"></i>
            <i id="i-shift-overlap" class="fas fa-lg fa-share-alt-square text-info invisible"></i>
            <i id="i-lang-not-enough" class="fas fa-lg fa-language text-danger invisible"></i>
            <button id="btn-add-item" class="btn btn-primary" title="Add"><i class="fas fa-plus"></i></button>
            <a id="btn-confirm" class="btn btn-primary disabled" href="#modal-confirm" data-toggle="modal" title="Final Check"><i class="fas fa-check"></i></a>
        </div>
    </section>

</main>
<footer></footer>
<script src="<?= $config_handler->http_host ?>/js/transactionform.js"></script>
<script src="<?= $config_handler->http_host ?>/js/form_auto_selector.js"></script>
<script>
    const formHandler = new FormHandler(<?= json_encode($arrayShiftsByIdUser) ?>, <?= json_encode($transactionform_handler) ?>);
    <?php if (isset($_GET['id_from']) && isset($_GET['month']) && isset($_GET['day']) && isset($_GET['shift'])) { ?>
        const form_auto_selector = new FormAutoSelector('<?= $_GET['id_from'] ?>', '<?= $_GET['month'] ?>', '<?= $_GET['day'] ?>', '<?= $_GET['shift'] ?>');
    <?php } ?>
</script>