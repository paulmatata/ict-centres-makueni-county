<?php

session_start();

include 'includes/db.php';

include 'includes/header.php';

if(!isset($_SESSION['student_id'])){

    header("Location: login.php");

    exit();

}

$student_id =
$_SESSION['student_id'];

$get_stmt = mysqli_prepare($conn,

"SELECT students.*, ict_centres.centre_name

FROM students

LEFT JOIN ict_centres

ON students.centre_id =
ict_centres.id

WHERE students.id=?"

);
mysqli_stmt_bind_param($get_stmt, "i", $student_id);
mysqli_stmt_execute($get_stmt);
$get = mysqli_stmt_get_result($get_stmt);

$student =
mysqli_fetch_assoc($get);

if(isset($_POST['update'])){

    $fullname =
    trim($_POST['fullname']);

    $email =
    trim($_POST['email']);

    $phone =
    trim($_POST['phone']);

    $centre_id =
    (int) $_POST['centre_id'];

    $update_stmt = mysqli_prepare($conn,

    "UPDATE students

    SET

    fullname=?,
    email=?,
    phone=?,
    centre_id=?

    WHERE id=?"

    );
    mysqli_stmt_bind_param($update_stmt, "sssii", $fullname, $email, $phone, $centre_id, $student_id);
    mysqli_stmt_execute($update_stmt);

    header("Location: profile.php");

    exit();

}

$centres =
mysqli_query(
$conn,
"SELECT * FROM ict_centres"
);

?>

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
My Profile
</h3>

<a href="dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<form method="POST">

<div class="mb-3">

<label>Full Name</label>

<input
type="text"
name="fullname"
class="form-control"
value="<?php
echo htmlspecialchars($student['fullname']);
?>"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
value="<?php
echo htmlspecialchars($student['email']);
?>"
required>

</div>

<div class="mb-3">

<label>Phone</label>

<input
type="text"
name="phone"
class="form-control"
value="<?php
echo htmlspecialchars($student['phone']);
?>"
required>

</div>

<div class="mb-4">

<label>ICT Centre</label>

<select
name="centre_id"
class="form-control">

<?php

while($centre =
mysqli_fetch_assoc($centres)){

?>

<option
value="<?php
echo $centre['id'];
?>"

<?php

if($student['centre_id']
== $centre['id']){

echo "selected";

}

?>>

<?php
echo $centre['centre_name'];
?>

</option>

<?php } ?>

</select>

</div>

<button
name="update"
class="btn btn-primary">

Update Profile

</button>

</form>

</div>

</div>

<?php include 'includes/footer.php'; ?>