<?php

session_start();

include '../includes/db.php';
include '../includes/admin_header.php';
?>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-5">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="text-center mb-4">

<img src="../assets/images/makueni-logo.png" width="80">

<h3 class="fw-bold mt-3">
Admin Login
</h3>

</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_GET['error']) && $_GET['error'] === 'account_not_found'): ?>
    <div class="alert alert-danger">Admin account not found. Please contact the super admin.</div>
<?php endif; ?>

<form action="admin_login_process.php" method="POST">

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control form-control-lg"
placeholder="admin" required>

</div>

<div class="mb-3">

<label>Password</label>

<input
type="password"
name="password"
class="form-control form-control-lg"
required>

</div>

<button class="btn btn-dark btn-lg w-100">

Login

</button>

</form>

<div class="text-center mt-3">

<a href="../index.php"
class="btn btn-outline-secondary rounded-pill">

← Back Home

</a>

</div>

</div>

</div>

</div>

</div>

<?php include '../includes/admin_footer.php'; ?>