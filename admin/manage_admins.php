<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

include '../includes/admin_header.php';

//reset admin password.


if (isset($_GET['action']) && $_GET['action'] === 'reset_password' && isset($_GET['id'])) {
    $target_admin_id = intval($_GET['id']);
    
    // 1. Create the new secure default hash for "Makueni102"
    $default_password_hash = password_hash("Makueni102", PASSWORD_DEFAULT);
    
    // 2. Prepare the update query to reset credentials and flip the mandatory change flag
    $reset_stmt = mysqli_prepare($conn, "UPDATE admins SET password = ?, must_change_password = 1 WHERE id = ?");
    mysqli_stmt_bind_param($reset_stmt, "si", $default_password_hash, $target_admin_id);
    
    if (mysqli_stmt_execute($reset_stmt)) {
        // Redirect back cleanly to refresh the URL parameters
        header("Location: manage_admins.php?msg=reset_success");
        exit();
    } else {
        header("Location: manage_admins.php?msg=reset_failed");
        exit();
    }
}

$role = $_SESSION['admin_role'];

if($role != 'super_admin'){

    die("Access Denied");

}

$sql = "SELECT admins.*, ict_centres.centre_name

FROM admins

LEFT JOIN ict_centres

ON admins.centre_id =
ict_centres.id";

$result = mysqli_query($conn, $sql);

?>

<div class="container py-4">
    
<!--Alert message for password reset success-->
<?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> The admin password has been reset to <code>Makueni102</code>. They will be forced to update it on their next login session.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'reset_failed'): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Error!</strong> Something went wrong while attempting to reset the password. Please try again.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'activated'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Success!</strong> Admin account activated.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'deactivated'): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <strong>Done.</strong> Admin account deactivated.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Manage Admins
</h3>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<div class="table-responsive">
<table class="table table-hover align-middle">

<thead>

<tr>

<th>ID</th>
<th>Username</th>
<th>ICT Centre</th>
<th>Role</th>
<th>Status</th>
<th>Actions</th>


</tr>

</thead>

<tbody>

<?php

while($admin =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>
<?php echo $admin['id']; ?>
</td>

<td>
<?php echo $admin['username']; ?>
</td>

<td>

<?php
echo $admin['centre_name'];
?>

</td>

<td>

<?php
echo $admin['role'];
?>

</td>

<td>

<?php
echo $admin['status'];
?>

</td>

<td>

<?php

if($admin['status'] == 'active'){

?>

<a href="toggle_admin.php?id=<?php
echo $admin['id'];
?>&status=inactive"

class="btn btn-sm btn-danger">

Deactivate

</a>

<?php }else{ ?>

<a href="toggle_admin.php?id=<?php
echo $admin['id'];
?>&status=active"

class="btn btn-sm btn-success">

Activate

</a>

<?php } ?>
<a href="manage_admins.php?action=reset_password&id=<?= $admin['id'] ?>" 
       class="btn btn-warning btn-sm rounded shadow-sm ms-1 fw-medium"
       onclick="return confirm('Are you sure you want to reset this admin\'s password to the default temporary option ?');">
        Reset Password
    </a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

<?php include '../includes/admin_footer.php'; ?>
