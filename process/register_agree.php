<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/class/class_requests_handler.php";
$requests_handler = new RequestsHandler($_GET["mode"], $_GET["id_user"], $_GET["id_transaction"], $master_handler, $config_handler, true);
