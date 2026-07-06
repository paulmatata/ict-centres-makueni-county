<?php

session_start();

include '../includes/db.php';

$admin_id = $_SESSION['admin_id'];

$stmt = mysqli_prepare($conn,
"UPDATE admin_sessions

SET logout_time = NOW(),

activity='Admin Logged Out'

WHERE admin_id=?

ORDER BY id DESC

LIMIT 1"

);
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);

session_unset();

session_destroy();

header("Location: admin_login.php");

exit();

?>