<?php

include 'includes/db.php';

include 'includes/header.php';

if(isset($_POST['reset'])){

    $username =
    trim($_POST['username']);

    $email =
    trim($_POST['email']);

    $phone =
    trim($_POST['phone']);

    $new_password =
    $_POST['new_password'];

    $confirm_password =
    $_POST['confirm_password'];

    $check_stmt = mysqli_prepare($conn,

    "SELECT * FROM students

    WHERE username=?

    AND email=?

    AND phone=?"

    );
    mysqli_stmt_bind_param($check_stmt, "sss", $username, $email, $phone);
    mysqli_stmt_execute($check_stmt);
    $check = mysqli_stmt_get_result($check_stmt);

    if(mysqli_num_rows($check) > 0){

        if($new_password !== $confirm_password){

            $error =
            "Passwords do not match";

        }else{

            $hashed =
            password_hash(
                $new_password,
                PASSWORD_DEFAULT
            );

            $update_stmt = mysqli_prepare($conn,

            "UPDATE students

            SET password=?

            WHERE username=?"

            );
            mysqli_stmt_bind_param($update_stmt, "ss", $hashed, $username);
            mysqli_stmt_execute($update_stmt);

            $success =
            "Password reset successfully";

        }

    }else{

        $error =
        "Student details not found";

    }

}

?>

<div class="container py-5">

<div class="row justify-content-center">

<div class="col-lg-5">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="text-center mb-4">

<h3>
Forgot Password
</h3>

<p class="text-muted">

Recover your account

</p>

</div>

<?php

if(isset($error)){

echo "<div class='alert alert-danger'>$error</div>";

}

if(isset($success)){

echo "<div class='alert alert-success'>$success</div>";

}

?>

<form method="POST">

<div class="mb-3">

<label>Username</label>

<input
type="text"
name="username"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Phone Number</label>

<input
type="text"
name="phone"
class="form-control"
required>

</div>

<div class="mb-3">

<label>New Password</label>

<input
type="password"
name="new_password"
class="form-control"
required>

</div>

<div class="mb-4">

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
name="reset"
class="btn btn-primary w-100">

Reset Password

</button>

<div class="text-center mt-3">

<a href="login.php">

Back to Login

</a>

</div>

</form>

</div>

</div>

</div>

</div>

<?php include 'includes/footer.php'; ?>