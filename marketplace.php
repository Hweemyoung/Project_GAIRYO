<?php
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
require "$homedir/class/class_date_object.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/bs4-timeline.css">
</head>

<body>
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
</body>

