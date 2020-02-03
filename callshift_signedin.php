<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/config.php";
require_once "$homedir/class/class_shift_caller.php";
require_once "$homedir/utils.php";
$shift_caller = new ShiftCaller($master_handler, $config_handler);
?>

<header>Call Shift</header>
<main>
    <a class="a-popover" data-toggle="popover" data-content="Here the user can call for specific shift with a single click! Any candidates callable that overlaps a shift the user already has are automatically disabled." data-trigger="hover" data-placement="bottom">Callable shifts</a>
    <section id="section-call-list">
        <div class="row d-flex justify-content-center">
            <div class="col-8">
                <ul class="list-group">
                    <?php
                    foreach ($shift_caller->arrayDateObjects->arrayDateObjects as $date => $dateShiftsFilterer) {
                    ?>

                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= $date ?></span>
                            <div class="btn-group">
                                <?php foreach ($dateShiftsFilterer->arrShiftAvailableByShift as $shift => $available) { ?>
                                    <a href="#modal" class="btn<?php if (!$available) { ?> disabled<?php } ?>" data-toggle="modal"><?= $shift ?></a>
                                <?php } ?>
                            </div>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title">Calling for a shift</h1>
                </div>
                <div class="modal-body">
                    <table class="table table-responsive-sm text-center">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Day</th>
                                <th>Shift</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-modal"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <form id="form" action="<?= utils\genHref($config_handler->http_host, 'process/upload_transaction.php', []) ?>" method="get">
                        <input type="hidden" name="mode" value="call">
                        <input type="hidden" name="id_to" value="<?= $master_handler->id_user ?>">
                        <input id="input-date-shift" type="hidden" name="date_shift">
                        <input id="input-shift" type="hidden" name="shift">
                        <button class="btn btn-danger" type="button" title="Back" data-dismiss="modal"><i class="fas fa-undo"></i></button>
                        <button class="btn btn-primary" type="submit" title="Confirm"><i class="fas fa-file-export"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<script></script>