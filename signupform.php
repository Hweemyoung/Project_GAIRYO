<?php
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
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
    <?php
    if (!$signedin) {
        require './common_nav_signedout.php';
        require './signupform_main.php';
    } else {
        header('Location: ./admin.php');
    }
    require './common_footer.php';
    ?>
    <!-- Custom JS -->
    <script src="./js/signupform.js"></script>
</body>
</html>