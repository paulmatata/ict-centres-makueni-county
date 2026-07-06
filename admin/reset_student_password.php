<?php
include '../includes/db.php';
include 'includes/admin_auth.php';

$id = (int) $_GET['id'];

$role = $_SESSION['admin_role'];
$centre_id = $_SESSION['centre_id'];
$restrict = ($role != 'super_admin');

$new_password =
password_hash(
"Makueni102",
PASSWORD_DEFAULT
);

$sql = "UPDATE students

SET password=?

WHERE id=?" . ($restrict ? " AND centre_id=?" : "");

$stmt = mysqli_prepare($conn, $sql);

if ($restrict) {
    mysqli_stmt_bind_param($stmt, "sii", $new_password, $id, $centre_id);
} else {
    mysqli_stmt_bind_param($stmt, "si", $new_password, $id);
}

mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) === 0) {
    // Either the student doesn't exist, or belongs to a different centre
    header("Location: manage_students.php?reset=denied");
    exit();
}

header("Location: manage_students.php?reset=success");

exit();

?>
