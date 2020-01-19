<?php
$signedin = false;
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
require_once "$homedir/class/class_alert_handler.php";
$alert_handler = new AlertHandler(__FILE__);
var_dump($alert_handler);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php
    require './common_head.php';
    ?>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="./css/admin.css">
</head>

<body>
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
    <script src="./js/common.js"></script>
    <script src="./js/alerthandler.js"></script>
    <script>
        const _alertArray = <?= json_encode($alert_handler->getAlertArray()) ?>;
        const alertHandler = new AlertHandler(<?= json_encode($alert_handler->getAlertArray()) ?>);
    </script>
</body>

<body>
    <?php

    // <section id="section-nav">
    //     <nav class="navbar navbar-expand-sm bg-light fixed-top">
    //         <!-- logo -->
    //         <a href="#" class="navbar-brand order-sm-1 d-flex">
    //             <img class="d-none d-md-block mr-md-4" src="./data/png/logo_travel_color_large.png" alt="imgLogo">
    //             <p class="d-none d-sm-block mr-md-4">外国人旅行センター</p>
    //             <p class="d-sm-none">外旅</p>
    //         </a>
    //         <!-- Navbar -->
    //         <ul class="px-0 ml-auto mr-2 my-0 order-sm-3" id="navbar">
    //             <li class="nav-item dropdown no-arrow">
    //                 <a href="" class="nav-link dropdown-toggle text-light" role="button" data-toggle="dropdown">
    //                     <span class="badge badge-sm badge-danger">
    //                         <i class="fas fa-exchange-alt"></i>
    //                     </span>
    //                 </a>
    //                 <span class="badge badge-sm badge-danger">3</span>
    //                 <div class="dropdown-menu dropdown-menu-right">
    //                     <div class="dropdown-header">Requests</div>
    //                     <a href="#" class="dropdown-item">Request 1</a>
    //                     <a href="#" class="dropdown-item">Request 2</a>
    //                     <div class="dropdown-divider"></div>
    //                     <a href="#" class="dropdown-item">Action</a>
    //                 </div>
    //             </li>
    //             <li class="nav-item dropdown no-arrow">
    //                 <a href="" class="nav-link dropdown-toggle text-light" role="button" data-toggle="dropdown">
    //                     <span class="badge badge-sm badge-warning">
    //                         <i class="fas fa-bell fa-fw"></i>
    //                     </span>
    //                 </a>
    //                 <span class="badge badge-sm badge-warning">3</span>
    //                 <div class="dropdown-menu dropdown-menu-right">
    //                     <div class="dropdown-header">Notices</div>
    //                     <a href="#" class="dropdown-item">Notice 1</a>
    //                     <a href="#" class="dropdown-item">Notice 2</a>
    //                     <div class="dropdown-divider"></div>
    //                     <a href="#" class="dropdown-item">Action</a>
    //                 </div>
    //             </li>
    //             <!-- Account -->
    //             <li id="li-account" class="nav-item dropdown no-arrow">
    //                 <a href="" id="btn-account" class="nav-link dropdown-toggle" role="button" data-toggle="dropdown">
    //                     <span class="badge badge-sm badge-secondary">
    //                         <i id="i-sync" class="fas fa-sync"></i>
    //                     </span>
    //                 </a>
    //                 <div id="dropdown-account" class="dropdown-menu dropdown-menu-right d-none">
    //                     <div class="dropdown-header">Not Signed In</div>
    //                     <a href="#" class="dropdown-item">Notice 1</a>
    //                     <a href="#" class="dropdown-item">Notice 2</a>
    //                     <div class="dropdown-divider"></div>
    //                     <a id="dropdown-item-sign" href="#" class="dropdown-item" title="Sign In"><i class="fas fa-sign-in-alt"></i></a>
    //                 </div>
    //             </li>
    //         </ul>
    //         <!-- nav-menu toggler -->
    //         <button class="navbar-toggler btn" data-toggle="collapse" data-target="#navMenu">
    //             <!-- <img src="./data/png/list-2x.png" alt="navbar-toggler-icon"> -->
    //             <i class="fas fa-bars"></i>
    //         </button>
    //         <!-- menu -->
    //         <div class="collapse navbar-collapse order-sm-2" id="navMenu">
    //             <ul class="navbar-nav">
    //                 <li class="nav-item"><a href="./admin.html" class="nav-link">Overview</a></li>
    //                 <li class="nav-item"><a href="./shifts.html" class="nav-link">Shifts</a></li>
    //                 <li class="nav-item"><a href="#" class="nav-link">History</a></li>
    //                 <li class="nav-item"><a href="#" class="nav-link">News</a></li>
    //                 <li class="nav-item"><a href="./forms.html" class="nav-link">Forms</a></li>
    //             </ul>
    //         </div>
    //     </nav>
    // </section