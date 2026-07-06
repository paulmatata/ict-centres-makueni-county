<?php

include '../includes/db.php';
include '../includes/admin_auth.php';

$role = $_SESSION['admin_role'];

if($role != 'super_admin'){

    die("Access Denied");

}

$id = (int) $_GET['id'];

$status = in_array($_GET['status'], ['active', 'inactive'], true) ? $_GET['status'] : 'inactive';

$stmt = mysqli_prepare($conn,

"UPDATE admins

SET status=?

WHERE id=?"

);
mysqli_stmt_bind_param($stmt, "si", $status, $id);
mysqli_stmt_execute($stmt);

$action = ($status === 'active') ? 'activated' : 'deactivated';

header("Location: manage_admins.php?msg=" . $action);

exit();

?>
