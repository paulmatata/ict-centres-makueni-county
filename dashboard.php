<?php

session_start();

include 'includes/db.php';

if(!isset($_SESSION['student_id'])){
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$student_id = $_SESSION['student_id'];

$stmt = mysqli_prepare($conn, "SELECT completion_status, certificate_serial FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student_status = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$certificate_ready = !empty($student_status['certificate_serial']);

?>

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4 mb-4">

<h3>
Welcome,
<?php echo $_SESSION['student_name']; ?>
</h3>

<p>
Welcome to Makueni Digital Youth Hub.
</p>

</div>

<div class="row g-4">

<div class="col-6 col-lg-3">
<a href="training_letter.php"
class="text-decoration-none">
<div class="card dashboard-card border-0 shadow-sm text-center p-4">
<i class="bi bi-mortarboard dashboard-icon"></i>
<h5>Training Letter</h5>
</div>
</a>
</div>

<div class="col-6 col-lg-3">

<a href="certificate.php"
class="text-decoration-none">

<div class="card dashboard-card border-0 shadow-sm text-center p-4 position-relative">

<?php if ($certificate_ready): ?>
    <span class="badge bg-success position-absolute top-0 end-0 m-2">Ready</span>
<?php endif; ?>

<i class="bi bi-award dashboard-icon"></i>

<h5>
Certificate
</h5>

</div>

</a>

</div>

<div class="col-6 col-lg-3">
    <a href="profile.php"
    class="text-decoration-none">
<div class="card dashboard-card border-0 shadow-sm text-center p-4">
<i class="bi bi-person dashboard-icon"></i>
<h5>Profile</h5>
</div>
</a>
</div>

<div class="col-6 col-lg-3">

<a href="notes.php"
class="text-decoration-none">

<div class="card dashboard-card border-0 shadow-sm text-center p-4">

<i class="bi bi-book dashboard-icon"></i>

<h5>
Notes
</h5>

</div>

</a>

</div>

<div class="col-6 col-lg-3">

<a href="submit_review.php"
class="text-decoration-none">

<div class="card dashboard-card border-0 shadow-sm text-center p-4">

<i class="bi bi-star-fill dashboard-icon"></i>

<h5>
Rate Center
</h5>

</div>

</a>

</div>

<div class="col-6 col-lg-3">
<div class="card dashboard-card border-0 shadow-sm text-center p-4">
<i class="bi bi-box-arrow-right dashboard-icon"></i>
<h5>
<a href="logout.php" class="text-decoration-none text-dark">
Logout
</a>
</h5>
</div>
</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>
