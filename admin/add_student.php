<?php
include '../includes/db.php';

include '../includes/admin_auth.php';

include '../includes/admin_header.php';
include 'includes/admin_sidebar.php'; 

$centre_id = $_SESSION['centre_id'];

if(isset($_POST['submit'])){

    $fullname = trim($_POST['fullname']);

    $username = trim($_POST['username']);

    $email = trim($_POST['email']);

    $phone = trim($_POST['phone']);

    $password_raw = $_POST['password'];

    $confirm_password =
    $_POST['confirm_password'];

    if($password_raw !== $confirm_password){

        $error = "Passwords do not match";

    }else{

        $check_stmt = mysqli_prepare($conn, "SELECT * FROM students WHERE username=?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        $check = mysqli_stmt_get_result($check_stmt);

        if(mysqli_num_rows($check) > 0){

            $error = "Username already exists";

        }else{

            $password =
            password_hash(
                $password_raw,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO students(

                fullname,
                username,
                email,
                phone,
                password,
                centre_id

            )

            VALUES(?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssi", $fullname, $username, $email, $phone, $password, $centre_id);
            mysqli_stmt_execute($stmt);

            $success =
            "Student Added Successfully";

        }

    }

}

?>
<div class="admin-content">
<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<h3 class="mb-4">
Add Student
</h3>

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

<label>Full Name</label>

<input
type="text"
name="fullname"
class="form-control"
required>

</div>

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

<label>Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="mb-3">

<label>Confirm Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<button
name="submit"
class="btn btn-primary">

Create Student

</button>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</form>

</div>

</div>
</div>
<?php include '../includes/admin_footer.php'; ?>
