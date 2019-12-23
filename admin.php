<?php
session_start();
if(!isset($_SESSION['id_google'])){
    $signedin = false;
} else {
    $signedin = true;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Deactivate zoom in mobile device -->
    <!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"> -->
    <style></style>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
    <!-- Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.0/css/bootstrap.min.css" integrity="sha384-SI27wrMjH3ZZ89r4o+fGIJtnzkAnFs3E4qz9DIYioCQ5l9Rd/7UAa8DHcaL8jkWt" crossorigin="anonymous">
    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.0/js/bootstrap.min.js" integrity="sha384-3qaqj0lc6sV/qpzrc1N5DC6i1VRn/HyX4qdPaiEFbn54VjQBEU341pvjz7Dv3n6P" crossorigin="anonymous"></script>

    <!-- Load the Google Platform Library -->
    <script src="https://apis.google.com/js/platform.js" async defer></script>

    <!-- Icon libraries -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.0/css/all.css" integrity="sha384-lZN37f5QGtY3VHgisS14W3ExzMWZxybE1SJSEsQp9S+oqd12jhcu+A56Ebc1zFSJ" crossorigin="anonymous">
    <!-- Custom styles -->
    <link rel="stylesheet" href="./css/common.css">
    <link rel="stylesheet" href="./css/admin.css">
</head>

<body>
    <?php
    if ($signedin){
        require ('./section_nav_signedin.php');
    } else {
        require ('./section_nav_signedout.php');
    }
    
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
    // </section>
    ?>
    <header></header>
    <main>
        <div class="container px-1">
            <section id="section-shift">
                <div class="card" id="card-shift">
                    <div class="card-header d-flex align-middle">
                        <a href="#shift-content" class="card-link mr-auto" data-toggle="collapse">
                            Upcoming: 1/23(木) B
                        </a>
                        <i class="fas fa-exclamation-triangle text-danger"></i>
                        <i class="fas fa-exclamation-triangle text-warning"></i>
                    </div>

                    <div class="collapse show" id="shift-content">
                        <div class="card-body">
                            <h5 class="mb-0 text-center">
                                2020/1/23(木)
                            </h5>
                            <!-- row 1 -->
                            <div class="row no-gutters pb-2">
                                <div class="col-6 px-1 text-center d-flex">
                                    <div id="div-your-shift">
                                        <h1 class="display-3">B</h1>
                                        <p class="">08:00~13:30</p>
                                        <div class="d-block d-md-flex" id="div-warnings">
                                            <!-- Put warning -->
                                            <div class="dropdown mx-md-1">
                                                <div class="div-warning bg-danger text-light p-1" data-toggle="dropdown">
                                                    <!-- <div class="pseudo"></div> -->
                                                    <div class="">
                                                        <p>
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Being PUT to:<br>
                                                        </p>
                                                        <p>
                                                            Another Member
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-header">PUT to ANOTHER MEMBER</div>
                                                    <a href="#" class="dropdown-item">
                                                        Pull back
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" class="dropdown-item">
                                                        Details
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- Call warning -->
                                            <div class="dropdown mx-md-1">
                                                <div class="div-warning bg-danger text-light p-1" data-toggle="dropdown">
                                                    <div class="">
                                                        <p>
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Being CALLED by:<br>
                                                        </p>
                                                        <p>
                                                            Another Member
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-header">CALL by ANOTHER MEMBER</div>
                                                    <a href="#" class="dropdown-item">
                                                        Accept
                                                    </a>
                                                    <a href="#" class="dropdown-item">
                                                        Decline
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" class="dropdown-item">
                                                        Details
                                                    </a>
                                                </div>
                                            </div>
                                            <!-- Advertising warning -->
                                            <div class="dropdown mx-md-1">
                                                <div class="div-warning bg-warning text-dark p-1" data-toggle="dropdown">
                                                    <div class="">
                                                        <p>
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            Being Advertised<br>
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="dropdown-menu">
                                                    <div class="dropdown-header">ADVERTISING</div>
                                                    <a href="#" class="dropdown-item">
                                                        Pull back
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a href="#" class="dropdown-item">
                                                        Details
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- row 2 -->
                                <div class="col-6 px-0">
                                    <div id="shift-member-table">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-2 d-flex">
                                                        <p>A</p>
                                                    </div>
                                                    <div class="col-10">
                                                        <ul class="list-group">
                                                            <li class="list-group-item px-1">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-2 d-flex">
                                                        <p>H</p>
                                                    </div>
                                                    <div class="col-10">
                                                        <ul class="list-group">
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item text-info">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member(Toshi)
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-2 d-flex">
                                                        <p>B</p>
                                                    </div>
                                                    <div class="col-10">
                                                        <ul class="list-group">
                                                            <li class="list-group-item active">YOU</li>
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-2 d-flex">
                                                        <p>C</p>
                                                    </div>
                                                    <div class="col-10">
                                                        <ul class="list-group">
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="px-0 col-2 d-flex">
                                                        <p>D</p>
                                                    </div>
                                                    <div class="px-0 col-10">
                                                        <ul class="list-group">
                                                            <li class="list-group-item text-info">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member(Toshi)
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                            <li class="list-group-item">
                                                                <div class="dropdown">
                                                                    <a data-toggle="dropdown">
                                                                        Member
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <div class="dropdown-header">Member</div>
                                                                        <a class="dropdown-item" href="#">Call this
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Put your
                                                                            shift</a>
                                                                        <a class="dropdown-item" href="#">Send
                                                                            message</a>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer p-2">
                        <div class="row no-gutters text-center">
                            <div class="col-4 px-0">
                                <button class="btn btn-sm btn-danger" type="button">Request</button>
                            </div>
                            <div class="col-4 px-0">
                                <button class="btn btn-sm btn-warning" type="button">Advertise</button>
                            </div>
                            <div class="col-4 px-0">
                                <button class="btn btn-sm btn-secondary" type="button">Details</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <hr>
            <section id="section-boards">
                <div class="row">
                    <div class="col-md-6">

                        <div class="div-list-title d-flex">
                            <h5 class="mx-auto">Requests</h5>
                            <a href="#"><i class="fas fa-angle-right"></i></a>
                        </div>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action list-group-item-info">
                                <span>上段固定アイテム</span>
                                <span>23 Oct 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>普通のお知らせ1</span>
                                <div class="badge badge-sm badge-primary">new</div>
                                <span>2 Dec 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action list-group-item-warning">
                                <span>重要なお知らせ</span>
                                <span>1 Dec 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>普通のお知らせ2</span>
                                <span>30 Nov 2019</span>
                            </a>
                            <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                            <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                        </div>
                    </div>
                    <div class="col-md-6">

                        <div class="div-list-title d-flex">
                            <h5 class="mx-auto">Notices</h5>
                            <a href="#"><i class="fas fa-angle-right"></i></a>
                        </div>
                        <div class="list-group">
                            <a href="#" class="list-group-item list-group-item-action list-group-item-info">
                                <span>上段固定アイテム</span>
                                <span>23 Oct 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>普通のお知らせ1</span>
                                <div class="badge badge-sm badge-primary">new</div>
                                <span>2 Dec 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action list-group-item-warning">
                                <span>重要なお知らせ</span>
                                <span>1 Dec 2019</span>
                            </a>
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>普通のお知らせ2</span>
                                <span>30 Nov 2019</span>
                            </a>
                            <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                            <!-- <a href="#" class="list-group-item">item <span>2 Dec 2019</span></a> -->
                        </div>
                    </div>
                </div>
            </section>
            <hr>
            <section id="section-history">
            </section>
        </div>
    </main>
    <footer></footer>
    <!-- Google Sign-In JavaScript client reference -->
    <!-- Load the Google APIs platform library -->
    <script src="https://apis.google.com/js/platform.js?onload=init" async defer></script>
    <!-- Custom JS -->
    <script src="./js/common.js"></script>
</body>

</html>