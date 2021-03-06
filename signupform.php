<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
$signedin = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
</head>
<header>Welcome to Odakyu Gairyo!</header>
<body>
    <div class="container px-1">
    <?php
    if (!$signedin) {
        require './common_nav_signedout.php';
        require './signupform_main.php';
    } else {
        $http_host = $_SERVER['HTTP_HOST'] . '/gairyo_temp';
        header("Location: $http_host/admin.php");
    }
    require './common_footer.php';
    ?>
    <!-- Custom JS -->
    <script src="<?=$config_handler->http_host?>/js/signupform.js"></script>
    </div>
</body>
</html>