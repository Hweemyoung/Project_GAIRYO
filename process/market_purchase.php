<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/class/class_db_handler.php";
require_once "$homedir/class/class_requests_handler.php";
require_once "$homedir/class/class_market_purchase_processor.php";
require_once "$homedir/config.php";

// User is purchasing 'mode' item.
$market_purchase_processor = new MarketPurchaseProcessor($_GET, $master_handler);
// Still in transaction.
// var_dump($master_handler->dbh->inTransaction());
var_dump($_GET);
// Then, load register_agree.php

$requests_handler = new RequestsHandler('agree', $market_purchase_processor->id_user, $market_purchase_processor->id_transaction, $master_handler, $config_handler, true);