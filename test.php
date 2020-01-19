<?php
$homepath = '/var/www/html/gairyo_temp';
session_save_path("$homepath/sess");
session_name('sess_gairyo');
session_start();
echo $_COOKIE['sess_gairyo'];
var_dump(session_id());
if(isset($_COOKIE['sess_gairyo'])){
} else {
}
// session_start();
// echo session_id();
// var_dump($_SESSION);
// var_dump($_COOKIE);
?>