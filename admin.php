<?php
$signedin = false;
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_alert_handler.php";
require_once "$homedir/config.php";
$alert_handler = new AlertHandler(__FILE__, $master_handler, $config_handler);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require "$homedir/common_head.php";
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/admin.css">
</head>

<body>
    <div class="container px-1">
        <div class="row">
            <div class="col-sm-3 d-none d-sm-block"></div>
            <div class="col-sm-9">
                <?php
                require './admin_header.php';
                if (!$signedin) {
                    require './common_nav_signedout.php';
                    require './common_main_signedout.php';
                } else {
                    require './common_nav_signedin.php';
                    require './admin_signedin.php';
                }
                require './common_footer.php';
                ?>
                <!-- Google Sign-In JavaScript client reference -->
                <!-- Load the Google APIs platform library -->
                <script src="https://apis.google.com/js/platform.js?onload=init" async defer></script>
                <!-- Custom JS -->
                <script src="<?= $config_handler->http_host ?>/js/common.js"></script>
                <script src="<?= $config_handler->http_host ?>/js/alerthandler.js"></script>
                <script>
                    const _alertArray = <?= json_encode($alert_handler->getAlertArray()) ?>;
                    const alertHandler = new AlertHandler(<?= json_encode($alert_handler->getAlertArray()) ?>);
                </script>
            </div>
        </div>
    </div>
</body>

</html>