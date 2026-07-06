<?php
ob_start();
$host = "localhost";
$user = "root";
$pass = "";
$db = "makueni_digital_hub";
$port = 3306;
//$host = getenv('DB_HOST');//Localhost";
//$user = getenv('DB_USER');//"root";
//$pass = getenv('DB_PASS');//"";
//$db = getenv('DB_NAME');
//$port = getenv('DB_PORT') ?: 3306;

$conn = mysqli_connect(
    $host,
    $user,
    $pass,
    $db,
     $port,
);

if(!$conn){
    die("Database Connection Failed");
}

?>
