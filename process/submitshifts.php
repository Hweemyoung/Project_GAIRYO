<?php
$homedir = '/var/www/html/gairyo_temp';
require_once "$homedir/check_session.php";
require_once "$homedir/config.php";
require_once "$homedir/utils.php";
function submitShifts($master_handler, $id_user, $Ym){
    $columns = 'id_user, m';
    $fields = "$id_user, '$Ym'";
    foreach($_POST as $name){
        $columns = $columns . ',' . $name;
        $fields = $fields . ',' . '1';
    }
    $sql = "INSERT INTO shifts_submitted ($columns) VALUES ($fields)";
    echo $sql . '<br>';
    $stmt = $master_handler->dbh->prepare($sql);
    $stmt->execute();
    var_dump($stmt->errorInfo());
}
var_dump($_POST);
$id_user = $_POST["id_user"];
$Ym = $_POST["Ym"];
unset($_POST["id_user"]);
unset($_POST["Ym"]);

if ($_GET["mode"] === 'modify'){
    $sql = "DELETE FROM shifts_submitted WHERE id_user = $id_user AND m = :Ym";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':Ym', $Ym);
    $stmt->execute();
    var_dump($stmt->errorInfo());
    submitShifts($master_handler, $id_user, $Ym);
} else if ($_GET["mode"] === 'submit'){
    submitShifts($master_handler, $id_user, $Ym);
}
$href = utils\genHref($config_handler->http_host, 'shifts.php', $master_handler->arrPseudoUser);
header("Location: $href");