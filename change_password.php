<?php

session_start();

include 'includes/db.php';

include 'includes/header.php';

$student_id =
$_SESSION['student_id'];

if(isset($_POST['change'])){

    $current =
    $_POST['current_password'];

    $new =
    $_POST['new_password'];

    $confirm =
    $_POST['confirm_password'];

    $get_stmt =
    mysqli_prepare($conn,

    "SELECT * FROM students
    WHERE id=?"

    );
    mysqli_stmt_bind_param($get_stmt, "i", $student_id);
    mysqli_stmt_execute($get_stmt);
    $get = mysqli_stmt_get_result($get_stmt);

    $student =
    mysqli_fetch_assoc($get);

    if(!password_verify(
        $current,
        $student['password']
    )){

        $error =
        "Current password incorrect";

    }elseif($new !== $confirm){

        $error =
        "Passwords do not match";

    }else{

        $password =
        password_hash(
            $new,
            PASSWORD_DEFAULT
        );

        $update_stmt = mysqli_prepare($conn,

        "UPDATE students

        SET password=?

        WHERE id=?"

        );
        mysqli_stmt_bind_param($update_stmt, "si", $password, $student_id);
        mysqli_stmt_execute($update_stmt);

        $success =
        "Password Changed Successfully";

    }

}

?>

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<h3 class="mb-4">
Change Password
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

<label>Current Password</label>

<input
type="password"
name="current_password"
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
name="change"
class="btn btn-primary">

Change Password

</button>

</form>

</div>

</div>

<?php include 'includes/footer.php'; ?>