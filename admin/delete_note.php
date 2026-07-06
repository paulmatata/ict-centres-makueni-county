<?php
include '../includes/db.php';
include '../includes/admin_auth.php';


$id = (int) $_GET['id'];

$get_stmt = mysqli_prepare($conn,

"SELECT * FROM notes
WHERE id=?"

);
mysqli_stmt_bind_param($get_stmt, "i", $id);
mysqli_stmt_execute($get_stmt);
$get = mysqli_stmt_get_result($get_stmt);

$note = mysqli_fetch_assoc($get);

$file =
"../assets/uploads/notes/" .
$note['file_name'];

if(file_exists($file)){

    unlink($file);

}

$del_stmt = mysqli_prepare($conn,

"DELETE FROM notes
WHERE id=?"

);
mysqli_stmt_bind_param($del_stmt, "i", $id);
mysqli_stmt_execute($del_stmt);

header("Location: manage_notes.php?deleted=success");

exit();

?>
