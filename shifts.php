<?php
$signedin = false;
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_alert_handler.php";
require_once "$homedir/config.php";

$alertHandler = new AlertHandler(__FILE__, $master_handler, $config_handler);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/shifts.css">
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/custom_schedule.css">
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/submitform.css">
</head>

<body>
    <div class="container px-1">
        <div class="row">
            <div class="col-sm-3 col-md-2 d-none d-sm-block"></div>
            <div class="col-sm-9 col-md-8">
                <?php
                if (!$master_handler->signedin) {
                    require './common_nav_signedout.php';
                    require './common_main_signedout.php';
                } else {
                    require './common_nav_signedin.php';
                    require './shifts_signedin.php';
                }
                require './common_footer.php';
                ?>
            </div>
            <div class="col-sm-3 col-md-8 d-none d-sm-block"></div>
        </div>
    </div>
    <script src="<?= $config_handler->http_host ?>/js/alerthandler.js"></script>
    <script>
        const _alertArray = <?= json_encode($alertHandler->getAlertArray()) ?>;
        const alertHandler = new AlertHandler(<?= json_encode($alertHandler->getAlertArray()) ?>);
    </script>
</body>