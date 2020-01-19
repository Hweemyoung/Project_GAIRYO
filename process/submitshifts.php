<?php
$homedir = '/var/www/html/gairyo_temp';
require "$homedir/check_session.php";
function submitShifts($id_user, $Ym){
    global $dbh;
    $columns = 'id_user, m';
    $fields = "$id_user, '$Ym'";
    foreach($_POST as $name){
        $columns = $columns . ',' . $name;
        $fields = $fields . ',' . '1';
    }
    $sql = "INSERT INTO shifts_submitted ($columns) VALUES ($fields)";
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    var_dump($stmt->errorInfo());
}

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
    submitShifts($id_user, $Ym, $_POST);
} else if ($_GET["mode"] === 'submit'){
    submitShifts($id_user, $Ym, $_POST);
}

header('Location: '. './shifts.php');