<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

include '../includes/admin_header.php';
include '../includes/admin_sidebar.php';

$role = $_SESSION['admin_role'];

$centre_id = $_SESSION['centre_id'];

$restrict = ($role != 'super_admin');

// Small helper: run a COUNT query, optionally restricted to the admin's centre
function count_students($conn, $where_clause, $restrict, $centre_id)
{
    $sql = "SELECT COUNT(*) AS total FROM students WHERE $where_clause"
        . ($restrict ? " AND centre_id = ?" : "");

    $stmt = mysqli_prepare($conn, $sql);

    if ($restrict) {
        mysqli_stmt_bind_param($stmt, "i", $centre_id);
    }

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return (int) mysqli_fetch_assoc($result)['total'];
}

$total_students = count_students($conn, "1=1", $restrict, $centre_id);

$active_students = count_students(
    $conn,
    "completion_status='incomplete' AND status='active' AND training_start_date <= CURDATE()",
    $restrict,
    $centre_id
);

$completed_students = count_students(
    $conn,
    "completion_status='completed'",
    $restrict,
    $centre_id
);

$removed_students = count_students(
    $conn,
    "status='removed'",
    $restrict,
    $centre_id
);

$pending_students = count_students(
    $conn,
    "completion_status='incomplete' AND status='active' AND training_start_date > CURDATE()",
    $restrict,
    $centre_id
);

// Earliest training year on record, to build the year dropdown
$year_sql = "SELECT MIN(YEAR(training_start_date)) AS min_year FROM students WHERE training_start_date IS NOT NULL"
    . ($restrict ? " AND centre_id = ?" : "");

$year_stmt = mysqli_prepare($conn, $year_sql);
if ($restrict) {
    mysqli_stmt_bind_param($year_stmt, "i", $centre_id);
}
mysqli_stmt_execute($year_stmt);
$min_year_row = mysqli_fetch_assoc(mysqli_stmt_get_result($year_stmt));

$min_year = $min_year_row['min_year'] ? (int)$min_year_row['min_year'] : (int)date('Y');
$current_year = (int)date('Y');

$months = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
];

?>
<div class="admin-content">
<div class="d-flex justify-content-between align-items-center mb-4">
<h2 class="fw-bold mb-0">Reports Dashboard</h2>
<a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
</div>

<div class="row g-4">

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center">

<h1 class="text-primary">

<?php
echo $total_students;
?>

</h1>

<h5>
Total Students
</h5>

</div>

</div>

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center">

<h1 class="text-success">

<?php
echo $active_students;
?>

</h1>

<h5>
Active Students
</h5>

</div>

</div>

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center">

<h1 class="text-warning">

<?php
echo $pending_students;
?>

</h1>

<h5>
Pending Training
</h5>

</div>

</div>

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center">

<h1 class="text-secondary">

<?php
echo $removed_students;
?>

</h1>

<h5>
Removed Students
</h5>

</div>

</div>

</div>

<!-- Export / Print filters -->
<div class="card border-0 shadow-sm p-4 mt-4">

    <h4 class="fw-bold mb-3">Download / Print Reports</h4>

    <div class="row g-4">

        <!-- 1. Current class -->
        <div class="col-md-4">
            <h6>Current Class</h6>
            <p class="text-muted small">Students currently active and in training.</p>
            <a href="export_students_pdf.php?filter=current_class" class="btn btn-primary w-100">
                📄 Download Current Class
            </a>
        </div>

        <!-- 2. Month range (current year) -->
        <div class="col-md-4">
            <h6>By Month Range (<?php echo $current_year; ?>)</h6>
            <form action="export_students_pdf.php" method="GET" class="row g-2">
                <input type="hidden" name="filter" value="month_range">
                <div class="col-6">
                    <label class="form-label small mb-1">From</label>
                    <select name="from_month" class="form-select form-select-sm">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>"><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-6">
                    <label class="form-label small mb-1">To</label>
                    <select name="to_month" class="form-select form-select-sm">
                        <?php foreach ($months as $num => $name): ?>
                            <option value="<?php echo $num; ?>" <?php echo $num == date('n') ? 'selected' : ''; ?>><?php echo $name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary w-100 mt-2">Print Month Range</button>
                </div>
            </form>
        </div>

        <!-- 3. Full year with summary -->
        <div class="col-md-4">
            <h6>By Year</h6>
            <form action="export_students_pdf.php" method="GET" class="row g-2">
                <input type="hidden" name="filter" value="year">
                <div class="col-12">
                    <label class="form-label small mb-1">Year</label>
                    <select name="year" class="form-select form-select-sm">
                        <?php for ($y = $current_year; $y >= $min_year; $y--): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-outline-primary w-100 mt-2">Print Year Report</button>
                </div>
            </form>
        </div>

    </div>

</div>

</div>
</div>
<?php include '../includes/admin_footer.php'; ?>
