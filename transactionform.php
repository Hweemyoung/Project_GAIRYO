<?php
$signedin = false;
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require "$homedir/common_head.php";
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= $config_handler->http_host ?>/css/transactionform.css">
</head>

<body>
    <div class="container px-1">
        <div class="row">
            <div class="col-sm-3 d-none d-sm-block"></div>
            <div class="col-sm-9">
                <?php
                require "$homedir/transactionform_header.php";
                if (!$signedin) {
                    require "$homedir/common_nav_signedout.php";
                    require "$homedir/common_main_signedout.php";
                } else {
                    require "$homedir/common_nav_signedin.php";
                    require "$homedir/transactionform_signedin.php";
                }
                require "$homedir/common_footer.php";
                ?>
            </div>
        </div>
    </div>
</body>