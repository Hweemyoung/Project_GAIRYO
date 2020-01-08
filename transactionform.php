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
    <link rel="stylesheet" href="./css/transactionform.css">
</head>
<body>
<?php
require './transactionform_header.php';
if (!$signedin) {
    require './common_nav_signedout.php';
    require './common_main_signedout.php';
} else {
    require './common_nav_signedin.php';
    require './transactionform_signedin.php';
}
require './common_footer.php';
?>
</body>