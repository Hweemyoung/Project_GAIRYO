<?php
$shifts = ['A', 'B', 'H', 'C', 'D', 'O'];
$days = range(1, 31);
$combined = array();
for ($i = 0; $i < count($days); $i++) {
    for ($j = 0; $j < count($shifts); $j++) {
        array_push($combined, $days[$i] . $shifts[$j]);
    }
}
try {
    $host = 'localhost';
$DBName = 'gairyo';
$userName = 'root';
$pw = '9957';
$dbh = new PDO("mysql:host=$host;dbname=$DBName", "$userName", "$pw", array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
} catch (PDOException $e){
    echo 'Connection failed: ' . $e->getMessage();
}

for ($i = 0; $i < count($combined); $i++) {
    $colname = $combined[$i];
    echo $colname;
    $sql = 'ALTER TABLE shifts_submitted ADD '.$colname.' boolean NOT NULL DEFAULT FALSE';
    $stmt = $dbh->prepare($sql);
    $result = $stmt->execute();
    echo '<br>';
    echo var_dump($stmt->errorInfo());
}

// $dbh = new PDO('mysql:host=localhost;dbname=opentutorials', 'root', '111111', array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
// switch($_GET['mode']){
//     case 'insert':
//         $stmt = $dbh->prepare("INSERT INTO topic (title, description, created) VALUES (:title, :description, now())");
//         $stmt->bindParam(':title',$title);
//         $stmt->bindParam(':description',$description);
 
//         $title = $_POST['title'];
//         $description = $_POST['description'];
//         $stmt->execute();
//         header("Location: list.php"); 
//         break;
//     case 'delete':
//         $stmt = $dbh->prepare('DELETE FROM topic WHERE id = :id');
//         $stmt->bindParam(':id', $id);
 
//         $id = $_POST['id'];
//         $stmt->execute();
//         header("Location: list.php"); 
//         break;
//     case 'modify':
//         $stmt = $dbh->prepare('UPDATE topic SET title = :title, description = :description WHERE id = :id');
//         $stmt->bindParam(':title', $title);
//         $stmt->bindParam(':description', $description);
//         $stmt->bindParam(':id', $id);
 
//         $title = $_POST['title'];
//         $description = $_POST['description'];
//         $id = $_POST['id'];
//         $stmt->execute();
//         header("Location: list.php?id={$_POST['id']}");
//         break;
// }
