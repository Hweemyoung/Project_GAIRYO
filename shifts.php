<?php
$signedin = false;
require 'check_session.php';

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/shifts.css">
    <link rel="stylesheet" href="./css/custom_schedule.css">
    <link rel="stylesheet" href="./css/submitform.css">
</head>
<body>
<?php
require './shifts_header.php';
if (!$signedin) {
    require './common_nav_signedout.php';
    require './common_main_signedout.php';
} else {
    require './common_nav_signedin.php';
    require './shifts_signedin.php';
}
require './common_footer.php';
?>
</body>

