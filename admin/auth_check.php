<?php
include '../includes/db.php';
if(session_status() == PHP_SESSION_NONE){

    session_start();

}

if(!isset($_SESSION['admin_id'])){

    header("Location: admin_login.php");

    exit();

}

if(isset($_SESSION['must_change_password']) 
&& $_SESSION['must_change_password'] == 1){

    header("Location: change_admin_password.php");

    exit();

}
?>
