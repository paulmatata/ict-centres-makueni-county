<?php

session_start();

include '../includes/db.php';

$username = trim($_POST['username']);

$password = $_POST['password'];

// 1. Prepare the statement
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username=? AND status='active'");

// 2. Bind the username variable to the '?' placeholder
mysqli_stmt_bind_param($stmt, "s", $username); // Assuming your variable is called $username

// 3. Execute the statement
mysqli_stmt_execute($stmt);

// 4. Get the result set
$result = mysqli_stmt_get_result($stmt);

// 5. Now your existing code will work flawlessly
if (mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    // password_verify logic

    if(password_verify($password, $admin['password'])){

        $_SESSION['admin_id'] = $admin['id'];

        $_SESSION['admin_name'] = $admin['username'];
        
        $_SESSION['centre_id'] =
$admin['centre_id'];

$_SESSION['admin_role'] =
$admin['role'];
$_SESSION['must_change_password'] =
$admin['must_change_password'];
if($admin['must_change_password'] == 1){

    header("Location: change_admin_password.php");

    exit();

}


$admin_id = $admin['id'];

$ip = $_SERVER['REMOTE_ADDR'];

$log_stmt = mysqli_prepare($conn,
"INSERT INTO admin_sessions(
admin_id,
login_time,
ip_address,
activity,
logout_time
)

VALUES(
?,
NOW(),
?,
'Admin Logged In',
NULL
)"
);
mysqli_stmt_bind_param($log_stmt, "is", $admin_id, $ip);
mysqli_stmt_execute($log_stmt);
 header("Location: admin_dashboard.php");


        exit();

    }else{

        $_SESSION['error'] = "Invalid password.";
        header("Location: admin_login.php");
        exit();

    }

}else{

    $_SESSION['error'] = "Admin account not found.";
    header("Location: admin_login.php");
    exit();

}

?>
