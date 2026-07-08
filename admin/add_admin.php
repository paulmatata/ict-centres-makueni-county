<?php

include '../includes/db.php';

include '../includes/admin_auth.php';

include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';

$role = $_SESSION['admin_role'];

if($role != 'super_admin'){

    die("Access Denied");

}

$centres = mysqli_query(
$conn,
"SELECT * FROM ict_centres"
);

if(isset($_POST['submit'])){

    $username = trim($_POST['username']);

    $password_raw = $_POST['password'];

    $confirm_password =
    $_POST['confirm_password'];

    $centre_id = (int) $_POST['centre_id'];

    $admin_role = in_array($_POST['role'], ['centre_admin', 'super_admin'], true) ? $_POST['role'] : 'centre_admin';

    if($password_raw !== $confirm_password){

        $error = "Passwords do not match";

    }else{

        $check_stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE username=?");
        mysqli_stmt_bind_param($check_stmt, "s", $username);
        mysqli_stmt_execute($check_stmt);
        $check = mysqli_stmt_get_result($check_stmt);

        if(mysqli_num_rows($check) > 0){

            $error =
            "Admin username already exists";

        }else{

            $password =
            password_hash(
                $password_raw,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO admins(

                username,
                password,
                centre_id,
                role,
                status

            )

            VALUES(?, ?, ?, ?, 'active')";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssis", $username, $password, $centre_id, $admin_role);
            mysqli_stmt_execute($stmt);

            $success =
            "Admin Added Successfully";

        }

    }

}

?>
    <div class="admin-content">

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Add Admin
</h3>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

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

<div class="mb-3">

<label>Assign ICT Centre</label>

<select
name="centre_id"
class="form-control"
required>

<option value="">
Select ICT Centre
</option>

<?php

while($centre =
mysqli_fetch_assoc($centres)){

?>

<option
value="<?php echo $centre['id']; ?>">

<?php
echo $centre['centre_name'];
?>

</option>

<?php } ?>

</select>

</div>

<div class="mb-4">

<label>Admin Role</label>

<select
name="role"
class="form-control"
required>

<option value="centre_admin">

Centre Admin

</option>

<option value="super_admin">

Super Admin

</option>

</select>

</div>

<button
name="submit"
class="btn btn-primary">

Create Admin

</button>

</form>

</div>

</div>
    </div>

<?php include '../includes/admin_footer.php'; ?>
