<?php
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
require_once "$homedir/class/class_date_object.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/bs4_timeline.css">
</head>

<body>
    <div class="container px-1">
    <div class="row">
        <div class="col-sm-3 d-none d-sm-block"></div>
        <div class="col-sm-9">
                <?php
                require './marketplace_header.php';
                if (!$signedin) {
                    require './common_nav_signedout.php';
                    require './common_main_signedout.php';
                } else {
                    require './common_nav_signedin.php';
                    require './marketplace_signedin.php';
                }
                require './common_footer.php';
                ?>
            </div>
        </div>
    </div>
</body>