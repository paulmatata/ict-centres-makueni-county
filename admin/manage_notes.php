<?php
include '../includes/db.php';

include '../includes/admin_auth.php';

include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';
$centre_id = $_SESSION['centre_id'];

$role = $_SESSION['admin_role'];

if($role == 'super_admin'){

    $sql = "SELECT notes.*, ict_centres.centre_name

    FROM notes

    LEFT JOIN ict_centres

    ON notes.centre_id =
    ict_centres.id";

    $stmt = mysqli_prepare($conn, $sql);

}else{

    $sql = "SELECT notes.*, ict_centres.centre_name

    FROM notes

    LEFT JOIN ict_centres

    ON notes.centre_id =
    ict_centres.id

    WHERE notes.centre_id=?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $centre_id);

}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

?>
<div claass="admin-content">
<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Manage Notes
</h3>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<?php if (isset($_GET['deleted']) && $_GET['deleted'] === 'success'): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        Note deleted successfully.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>ID</th>
<th>Title</th>
<th>ICT Centre</th>
<th>Uploaded</th>
<th>Download</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($note =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>
<?php echo $note['id']; ?>
</td>

<td>
<?php echo $note['title']; ?>
</td>

<td>
<?php echo $note['centre_name']; ?>
</td>

<td>
<?php echo $note['created_at']; ?>
</td>

<td>

<a
href="../assets/uploads/notes/<?php
echo $note['file_name'];
?>"

class="btn btn-sm btn-primary"
download>

Download

</a>

</td>

<td>

<a
href="delete_note.php?id=<?php
echo $note['id'];
?>"

class="btn btn-sm btn-danger"
onclick="return confirm('Delete this note? This cannot be undone.');">

Delete

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>
    </div>
<?php include '../includes/admin_footer.php'; ?>
