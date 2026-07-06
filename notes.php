<?php

session_start();

include 'includes/db.php';

include 'includes/header.php';

if(!isset($_SESSION['student_id'])){

    header("Location: login.php");

    exit();

}

$student_id = $_SESSION['student_id'];

$stmt = mysqli_prepare($conn,

"SELECT * FROM students
WHERE id=?"

);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$get_student = mysqli_stmt_get_result($stmt);

$student =
mysqli_fetch_assoc($get_student);

$centre_id =
$student['centre_id'];

$notes_stmt = mysqli_prepare($conn, "SELECT * FROM notes

WHERE centre_id=?");
mysqli_stmt_bind_param($notes_stmt, "i", $centre_id);
mysqli_stmt_execute($notes_stmt);
$result = mysqli_stmt_get_result($notes_stmt);

?>

<div class="container py-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Training Notes
</h3>

<a href="dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<div class="row g-4">

<?php

while($note =
mysqli_fetch_assoc($result)){

?>

<div class="col-lg-4">

<div class="card border-0 shadow-sm h-100 rounded-4">

<div class="card-body">

<h5>

<?php
echo $note['title'];
?>

</h5>

<p>

<?php
echo $note['description'];
?>

</p>

<a
href="<?php
echo $note['file_name'];
?>"

class="btn btn-primary w-100"
download target="_blank">

Download PDF

</a>

</div>

</div>

</div>

<?php } ?>

</div>

</div>

<?php include 'includes/footer.php'; ?>
