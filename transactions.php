<?php
$signedin = false;
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
require_once "$homedir/class/class_alert_handler.php";

$alertHandler = new AlertHandler(__FILE__);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/transactions.css">
</head>

<body>
    <?php
    require './transactions_header.php';
    if (!$signedin) {
        require './common_nav_signedout.php';
        require './common_main_signedout.php';
    } else {
        require './common_nav_signedin.php';
        require './transactions_signedin.php';
    }
    require './common_footer.php';
    ?>
    <script src="./js/alerthandler.js"></script>
    <script>
        const _alertArray = <?= json_encode($alertHandler->getAlertArray()) ?>;
        const alertHandler = new AlertHandler(<?= json_encode($alertHandler->getAlertArray()) ?>);
    </script>
</body>