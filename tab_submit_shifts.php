<?php

function getArrayRecords($Y, $m)
{
    global $dbh, $id_user;
    $now = new DateTime($Y . '-' . $m . '-01');
    $Ym = strval($now->format('Ym'));
    $sql = "SELECT * FROM shifts_submitted WHERE id_user = $id_user AND m = :Ym";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':Ym', $Ym);
    $stmt->execute();
    $arrayRecords = ($stmt->fetchAll(PDO::FETCH_ASSOC));
    if (count($arrayRecords)) {
        unset($arrayRecords[0]["id_user"]);
        unset($arrayRecords[0]["m"]);
        return $arrayRecords;
    } else {
        return array();
    }
}
$arrayRecords = getArrayRecords($Y, $m);

?>

<div id="div-form">
    <a class="a-popover" data-toggle="popover" data-content="Users can both submit and modify application form for shifts of next months. DB saves and modifies all applications from users, which will be used distributing shifts after submit-deadline." data-trigger="hover" data-placement="bottom">Submit&Modify</a>
    <h2 class="my-2"><?php if ($arrayRecords){echo 'Modify';} else {echo 'Submit';}?> application form</h2>
    <form action="./process/submitshifts.php?mode=<?php if ($arrayRecords){echo 'modify';} else {echo 'submit';}?>" method="POST" id="form-application">
        <?php echo strtr('
        <input type="hidden" name="id_user" value="$id_user">
        <input type="hidden" name="Ym" value="$Ym">
        ', array('$id_user' => $id_user, '$Ym' => $Y.$m)); ?>
        <!-- .row.no-gutters>.col-sm-6>h3+hr+table.table.table-hover>(thead>tr>th{Date}+th{Day}+th{Shift})+tbody>tr>td*2+td>.form-group>form-check-inline>label.form-check-label>input.form-check-input[type="checkbox"
                        name value] -->
        <div class="row no-gutters">
            <div class="col-md-6 col-month">
                <h3></h3>
                <hr>
                <table class="table table-hover text-center">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td>
                                <div class="form-group">
                                    <div class="form-check-inline">
                                        <label class="form-check-label">
                                            <input type="checkbox" class="form-check-input" name="" value="">
                                        </label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal fade" id="modal-confirm">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title"><?php if ($arrayRecords){echo 'Modify To:';} else {echo 'Confirm';}?></h3>
                    </div>
                    <div class="modal-body">
                        <table class="table table-sm table-hover text-center">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Shift</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-danger" type="button" title="Back" data-dismiss="modal"><i class="fas fa-undo"></i></button>
                        <?php if ($arrayRecords){echo '
                        <button class="btn btn-warning" type="submit" title="Modify"><i class="fas fa-screwdriver"></i></button>
                        ';} else {echo '
                        <button class="btn btn-primary" type="submit" title="Confirm"><i class="fas fa-file-export"></i></button>
                        ';}?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<div id="div-buttons" class="text-right p-1">
    <button id="btn-clear" class="btn btn-primary" title="Clear"><i class="fas fa-eraser"></i></button>
    <button id="btn-confirm" class="btn btn-primary" title="Final check" data-toggle="modal"><i class="fas fa-check"></i></button>
</div>
<script>
    var arrayRecords = <?=json_encode($arrayRecords)?>;
</script>
<script src="<?=$config_handler->http_host?>/js/constants.js"></script>
<script src="<?=$config_handler->http_host?>/js/submitform.js"></script>