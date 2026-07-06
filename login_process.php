<?php

session_start();

include 'includes/db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM students
WHERE username=?
AND status='active'";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if(mysqli_num_rows($result) > 0){

    $student = mysqli_fetch_assoc($result);

    if(password_verify($password, $student['password'])){

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['fullname'];

        header("Location: dashboard.php");
        exit();

    }else{

        $_SESSION['error'] = "Invalid password.";
        header("Location: login.php");
        exit();

    }

}else{

    $_SESSION['error'] = "Student account not found.";
    header("Location: login.php");
    exit();

}

?>