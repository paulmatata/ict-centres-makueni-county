<?php
include '../includes/db.php';
include '../includes/admin_auth.php';
include '../includes/admin_header.php';
include 'auth_check.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin_id'])){

    header("Location: admin_login.php");

    exit();

}

$centre_id = $_SESSION['centre_id'];

$role = $_SESSION['admin_role'];

if($role == 'super_admin'){

    $student_query =
    mysqli_query(
        $conn,
        "SELECT * FROM students"
    );

}else{
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM students

        WHERE centre_id=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $centre_id);
    mysqli_stmt_execute($stmt);
    $student_query = mysqli_stmt_get_result($stmt);

}

$students =
mysqli_num_rows($student_query);
$admins =
mysqli_num_rows(
mysqli_query($conn,
"SELECT * FROM admins")
);

$centre_stmt = mysqli_prepare($conn, "SELECT * FROM ict_centres WHERE id=?");
mysqli_stmt_bind_param($centre_stmt, "i", $centre_id);
mysqli_stmt_execute($centre_stmt);
$centre = mysqli_fetch_assoc(mysqli_stmt_get_result($centre_stmt));

?>


<div class="container py-4">

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">
Admin Dashboard
</h2>

<p>
Welcome,
<?php echo $_SESSION['admin_name']; ?>
</p>

<p class="text-muted">
    <?php 
    if ($_SESSION['admin_role']== 'super_admin'){echo "All Centres (Super Admin)";}else{
    echo isset($centre['centre_name'])? $centre['centre_name'] : 'No Centre Assigned'; }
    ?>
</p>

</div>

<div class="d-flex align-items-center justify-content-end gap-2 float-end">
    
    <a href="change_admin_password.php" class="btn btn-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-key-fill me-1" viewBox="0 0 16 16">
            <path d="M3.5 11.5a.5.5 0 0 1 .5-.5h5.793L8.146 9.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L9.793 13H4a.5.5 0 0 1-.5-.5z"/>
            <path d="M10 4a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm-1 2.5V3a1.5 1.5 0 0 0-3 0v3.5h3z"/>
        </svg>
        Change Password
    </a>



<a href="logout.php"
class="btn btn-danger rounded-pill">

Logout

</a>

</div>
</div>

<div class="row g-4">

<!-- STUDENTS -->

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1 class="text-primary">

<?php echo $students; ?>

</h1>

<h5>Total Students</h5>

</div>

</div>


<!-- ADD STUDENT -->

<div class="col-6 col-lg-3">

<a href="add_student.php"
class="text-decoration-none btn btn-primary">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>👨‍🎓</h1>

<h5>Add Student</h5>

</div>

</a>

</div>

<!-- MANAGE STUDENTS -->

<div class="col-6 col-lg-3">

<a href="manage_students.php"
class="text-decoration-none btn btn-primary">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>⚙️</h1>

<h5>Manage Students</h5>

</div>

</a>

</div>
<!--Update Student Status -->
<div class="col-6 col-lg-3">
<a href="update_student_status.php"
   class="btn btn-success"
   onclick="return confirm('Update completion status for all students who have completed 5 weeks of training?');">
    <div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>⚙️</h1>
<h5>Update Student Status</h5>
</div>
</a>
</div>

<!--notes card -->
<div class="col-6 col-lg-3">

<a href="upload_notes.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>📤</h1>

<h5>Upload Notes</h5>

</div>

</a>

</div>

<div class="col-6 col-lg-3">

<a href="manage_notes.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>📚</h1>

<h5>Manage Notes</h5>

</div>

</a>

</div>

    <div class="col-6 col-lg-3">

<a href="reviews.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>⭐</h1>

<h5>Reviews</h5>

</div>

</a>

</div>


<div class="col-6 col-lg-3">

<a href="reports.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>📊</h1>

<h5>Student Reports</h5>

</div>

</a>

</div>

    <div class="col-6 col-lg-3">

<a href="centre_report.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>📈</h1>

<h5>Center Reports</h5>

</div>

</a>

</div>

<!-- The following sections are only visible to super_admin -->
<?php if($role == 'super_admin') {?>
<!-- ADMINS -->

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1 class="text-success">

<?php echo $admins; ?>

</h1>

<h5>Total Admins</h5>

</div>

</div>

<div class="col-6 col-lg-3">

<a href="add_admin.php"
class="text-decoration-none btn btn-danger">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>🛡️</h1>
<!--the space left to maintain container size, do not delete-->

<h5> Add Admin </h5>

</div>

</a>

</div>

<!-- MANAGE ADMINS -->

<div class="col-6 col-lg-3">

<a href="manage_admins.php"
class="text-decoration-none btn btn-dark">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>👥</h1>

<h5>Manage Admins</h5>

</div>

</a>

</div>
<?php } ?>

</div>

</div>

<?php include '../includes/admin_footer.php'; ?>
