<?php
$signedin = false;
require './check_session.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
</head>

<body>
    <header>Welcome to Odakyu Gairyo!</header>
    <?php
    if (!$signedin) {
        require './common_nav_signedout.php';
        require './signupform_main.php';
    } else {
        header('./admin.php');
    }
    require './common_footer.php';
    ?>
</body>
</html>