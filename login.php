<?php
session_start();
include 'includes/header.php';
?>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-5">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="text-center mb-4">

<img src="assets/images/makueni-logo.png" width="80">

<h3 class="fw-bold mt-3">Student Login</h3>

</div>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
<?php endif; ?>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
<?php endif; ?>

<form action="login_process.php" method="POST">

<div class="mb-3">
<label>Username</label>
<input type="text" name="username" class="form-control form-control-lg rounded-3" required>
</div>

<div class="mb-3">
<label>Password</label>
<input type="password" name="password" class="form-control form-control-lg rounded-3" required>
</div>

<button class="btn btn-primary btn-lg w-100 rounded-3">
Login
</button>

</form>
<div class="text-center mt-4">
<a href="forgot_password.php">
Forgot Password?
</a>
</div>
<div class="text-center mt-4">
<a href="register.php">
Create New Account
</a>
</div>
<div class="text-center mt-3">
<a href="index.php" class="btn btn-secondary btn-lg rounded-pill px-4 mt-4">
<i class="bi bi-house-door"></i> Home
</a>
</div>
</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>