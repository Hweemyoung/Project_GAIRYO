<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_request_object.php";
require_once "$homedir/class/class_date_object.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/utils.php";
require_once "$homedir/class/class_martket_item_handler.php";

$market_item_handler = new MarketItemHandler($master_handler, $config_handler);
?>

<main>
    <a class="a-popover" data-toggle="popover" data-content="Very intuitive and user-friendly design." data-trigger="hover" data-placement="bottom">Market Timeline</a>
    <div class="bs4-timeline px-1 py-1 py-sm-2 py-md-4">
        <div id="div-btns-put-call-my-shifts" class="row no-gutters d-flex justify-content-between">
            <div class="col-md-4 my-1"><a href="<?= utils\genHref($config_handler->http_host, 'shifts.php', $master_handler->arrPseudoUser + ['f' => 5, 's' => 0]) ?>" class="btn d-block"><i class="fas fa-plus-square"></i> <strong>Put my shift</strong></a></div>
            <div class="col-md-4 my-1"><a href="<?= utils\genHref($config_handler->http_host, 'callshift.php', $master_handler->arrPseudoUser) ?>" class="btn d-block"><i class="fas fa-plus-square"></i> <strong>Call for shifts</strong></a></div>
        </div>
        <?php
        $market_item_handler->echoMarketTimeline();
        ?>
    </div>
    <div class="modal fade" id="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title">Confirm Purchase</h3>
                </div>
                <div class="modal-body">
                    <table class="table table-responsive-sm text-center">
                        <thead>
                            <tr>
                                <th>From</th>
                                <th>Month</th>
                                <th>Day</th>
                                <th>Shift</th>
                                <th>To</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-modal"></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-danger" type="button" title="Back" data-dismiss="modal"><i class="fas fa-undo"></i></button>
                    <form id="form" action="<?= $config_handler->http_host ?>/process/market_purchase.php" method="GET">
                        <input id="input-id-request" type="hidden" name="id_request">
                        <input id="input-mode" type="hidden" name="mode">
                        <button class="btn btn-primary" type="submit" title="Confirm"><i class="fas fa-file-export"></i></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= $config_handler->http_host ?>/js/constants.js"></script>
    <script src="<?= $config_handler->http_host ?>/js/marketplace.js"></script>
    <script>
        const market_item_handler = new MarketItemHandler(
            <?= json_encode($master_handler->arrayMemberObjectsByIdUser[$master_handler->id_user], 0, 1024) ?>,
            <?= json_encode($market_item_handler->date_objects_handler->arrayDateObjects, 0, 1024) ?>,
            <?= json_encode($market_item_handler->arrPutRequestsByIdShift, 0, 1024) ?>,
            <?= json_encode($market_item_handler->arrCallRequestsByDate, 0, 1024) ?>,
            <?= json_encode($market_item_handler->date_objects_put_handler->arrayDateObjects, 0, 1024) ?>,
            <?= json_encode($market_item_handler->date_objects_call_handler->arrayDateObjects, 0, 1024) ?>,
            <?= json_encode($market_item_handler->arrayShiftsByPart, 0, 1024) ?>,
            '<?= $config_handler->http_host ?>',
            _constants);
    </script>
</main>