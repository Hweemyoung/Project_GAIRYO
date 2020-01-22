<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_shifts_distributor.php";
require "$homedir/check_session.php";
require_once "$homedir/config.php";

$shift_distributor = new ShiftsDistributor($master_handler, $config_handler);