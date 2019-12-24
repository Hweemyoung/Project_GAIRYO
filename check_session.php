<?php
function check_signedin(){
    session_name('sess_gairyo');
    session_start();
    // POST is when someone signs in.
    if (!isset($_SESSION['id_google'])) {
        // No id_google variable in sess
        if (isset($_POST['id_google'])) {
            $_SESSION['id_google'] = $_POST['id_google'];
            return true;
        } else {
            return false;
        }
    } else {
        // id_google exists in sess
        if (isset($_POST['id_google'])){
            if ($_SESSION['id_google'] === $_POST['id_google']){
                return true;
            } else {
                // Sign in ID is different. Something has gone wrong. Change id_google in sess.
                $_SESSION['id_google'] = $_POST['id_google'];
                return true;
            }
        } else {
            return true;
        }
    }
}
$signedin = check_signedin();
if ($signedin){
    $id_google = $_SESSION['id_google'];
    // PDO Object
    $dbh = new PDO('mysql:host=localhost;dbname=gairyo', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
    // Select all valid ids
    $sql = 'SELECT id_google, members.* FROM members WHERE `status` = 1';
    $stmt = $dbh->prepare($sql);
    $stmt->execute();
    $arrayMembers = $stmt->fetchAll(PDO::FETCH_UNIQUE);
    if (!array_key_exists($id_google, $arrayMembers)){
        // Something is really wrong...
    } else {
        $id_user = $arrayMembers[$id_google]["id_user"];
    }
}
?>