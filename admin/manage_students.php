<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';
$centre_id = $_SESSION['centre_id'];

$role = $_SESSION['admin_role'];

// ---- Search + Pagination setup ----
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$per_page = 15;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) { $page = 1; }
$offset = ($page - 1) * $per_page;

// ---- Build WHERE clause dynamically (role restriction + email search) ----
$where = [];
$params = [];
$types = "";

if ($role != 'super_admin') {
    $where[] = "students.centre_id = ?";
    $params[] = $centre_id;
    $types .= "i";
}

if ($search !== '') {
    $where[] = "students.email LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$where_sql = count($where) > 0 ? "WHERE " . implode(" AND ", $where) : "";

// ---- Count total matching rows (for pagination) ----
$count_sql = "SELECT COUNT(*) AS total
FROM students
LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
$where_sql";

$count_stmt = mysqli_prepare($conn, $count_sql);
if (count($params) > 0) {
    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
}
mysqli_stmt_execute($count_stmt);
$count_result = mysqli_stmt_get_result($count_stmt);
$total_students = mysqli_fetch_assoc($count_result)['total'];
$total_pages = max(1, ceil($total_students / $per_page));

// ---- Main query: order by In Training, then Pending, then Completed, Removed last ----
$sql = "SELECT students.*, ict_centres.centre_name,
    CASE
        WHEN students.status = 'removed' THEN 4
        WHEN students.completion_status = 'completed' THEN 3
        WHEN students.training_start_date <= CURDATE() THEN 1
        ELSE 2
    END AS status_order
FROM students
LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
$where_sql
ORDER BY status_order ASC, students.training_start_date ASC
LIMIT ? OFFSET ?";

$stmt = mysqli_prepare($conn, $sql);

$limit_params = $params;
$limit_types = $types . "ii";
$limit_params[] = $per_page;
$limit_params[] = $offset;

mysqli_stmt_bind_param($stmt, $limit_types, ...$limit_params);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Row counter for the sequential number column (continues across pages)
$row_number = $offset + 1;

?>
<div class="admin-content">
<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">

<h3>
Manage Students
</h3>

<?php

if(isset($_SESSION['success'])){
    echo "<div class='alert alert-success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
    unset($_SESSION['success']);
}

if(isset($_SESSION['error'])){
    echo "<div class='alert alert-danger'>" . htmlspecialchars($_SESSION['error']) . "</div>";
    unset($_SESSION['error']);
}

if(isset($_GET['reset']) && $_GET['reset'] === 'success'){

echo "

<div class='alert alert-success'>

Student password reset successfully.
Temporary password is:

<strong>Makueni102</strong>

</div>

";

}

if(isset($_GET['reset']) && $_GET['reset'] === 'denied'){

echo "

<div class='alert alert-danger'>

You can only reset passwords for students in your own centre.

</div>

";

}

?>

<a href="admin_dashboard.php"
class="btn btn-secondary">

Back

</a>

</div>

<!-- Search bar -->
<form method="GET" class="row g-2 mb-4">
    <div class="col-sm-6 col-md-4">
        <input type="text" name="search" class="form-control"
               placeholder="Search by email..."
               value="<?php echo htmlspecialchars($search); ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Search</button>
    </div>
    <?php if ($search !== ''): ?>
    <div class="col-auto">
        <a href="manage_students.php" class="btn btn-outline-secondary">Clear</a>
    </div>
    <?php endif; ?>
</form>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>#</th>
<th>Name</th>
<th>Username</th>
<th>Email</th>
<th>Phone</th>
<th>ICT Centre</th>
<th>Status</th>
<th>Training Start</th>
<th>Completion</th>
<th>Training Status</th>
<th>Action </th>

</tr>

</thead>

<tbody>

<?php

if (mysqli_num_rows($result) === 0) {
    echo '<tr><td colspan="11" class="text-center text-muted py-4">No students found.</td></tr>';
}

while($student =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>
<?php echo $row_number++; ?>
</td>

<td>
<?php echo htmlspecialchars($student['fullname']); ?>
</td>

<td>
<?php echo htmlspecialchars($student['username']); ?>
</td>

<td>
<?php echo htmlspecialchars($student['email']); ?>
</td>

<td>
<?php echo htmlspecialchars($student['phone']); ?>
</td>

<td>
<?php echo htmlspecialchars($student['centre_name']); ?>
</td>

<td>
<?php echo htmlspecialchars($student['status']); ?>
</td>

<td>
    <?= date("d M Y", strtotime($student['training_start_date'])) ?>
</td>

<td>
<?php echo htmlspecialchars($student['completion_status']); ?>

</td>

<td>
<?php 
$today = date('Y-m-d'); 
$startDate = $student['training_start_date'];

// 1. Check the status row first for administrative removal
if ($student['status'] == 'removed'): ?>
    <span class="badge bg-secondary px-2.5 py-1.5 rounded">
        Removed
    </span>

<!--2. If not removed, check if they completed the course-->
<?php elseif ($student['completion_status'] == 'completed'): ?>
    <span class="badge bg-success px-2.5 py-1.5 rounded">
        Completed
    </span>

<!--3. Check if the training has already kicked off-->
<?php elseif ($today >= $startDate): ?>
    <span class="badge bg-info text-dark px-2.5 py-1.5 rounded">
        In Training
    </span>

<!-- 4. Fallback: The start date is still in the future-->
<?php else: ?>
    <span class="badge bg-danger px-2.5 py-1.5 rounded">
        Pending Training
    </span>
<?php endif; ?>
</td>

<td>

<a
href="reset_student_password.php?id=<?php
echo $student['id'];
?>"

class="btn btn-sm btn-warning">

Reset Password

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<!-- Pagination -->
<?php if ($total_pages > 1): ?>
<nav aria-label="Student pages">
    <ul class="pagination justify-content-center mt-3">

        <?php
        // Preserve the search term across page links
        $query_base = $search !== '' ? '&search=' . urlencode($search) : '';
        ?>

        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page - 1 . $query_base; ?>">Previous</a>
        </li>

        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i . $query_base; ?>"><?php echo $i; ?></a>
            </li>
        <?php endfor; ?>

        <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
            <a class="page-link" href="?page=<?php echo $page + 1 . $query_base; ?>">Next</a>
        </li>

    </ul>
</nav>
<?php endif; ?>

</div>

</div>
</div>
<?php include '../includes/admin_footer.php'; ?>
