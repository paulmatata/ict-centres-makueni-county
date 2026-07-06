<?php

include '../includes/admin_auth.php';

include '../includes/db.php';

include '../includes/admin_header.php';

$role = $_SESSION['admin_role'];
$centre_id = $_SESSION['centre_id'];

$restrict = ($role != 'super_admin');

$sql = "SELECT ict_centres.centre_name,

COUNT(students.id) AS total_students,

SUM(CASE WHEN students.completion_status='completed' THEN 1 ELSE 0 END) AS completed_students,

SUM(CASE WHEN students.status='removed' THEN 1 ELSE 0 END) AS removed_students,

SUM(CASE WHEN students.status='active' AND students.completion_status='incomplete'
    AND students.training_start_date <= CURDATE() THEN 1 ELSE 0 END) AS in_training,

SUM(CASE WHEN students.status='active' AND students.completion_status='incomplete'
    AND students.training_start_date > CURDATE() THEN 1 ELSE 0 END) AS pending_training

FROM ict_centres

LEFT JOIN students ON ict_centres.id = students.centre_id"

. ($restrict ? " WHERE ict_centres.id = ?" : "") .

" GROUP BY ict_centres.id";

$stmt = mysqli_prepare($conn, $sql);

if ($restrict) {
    mysqli_stmt_bind_param($stmt, "i", $centre_id);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Earliest training year on record, for the year dropdown below (same centre restriction)
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

<div class="container py-4">

<div class="card border-0 shadow-lg rounded-4 p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
<h3 class="mb-4">ICT Centre Report</h3>
<a href="admin_dashboard.php" class="btn btn-secondary">Back</a>
</div>

</div>

<div class="table-responsive">

<table class="table table-hover">

<thead>

<tr>

<th>ICT Centre</th>
<th>Total Students</th>
<th>Completed</th>
<th>Removed</th>
<th>In Training</th>
<th>Pending Training</th>

</tr>

</thead>

<tbody>

<?php

if (mysqli_num_rows($result) === 0) {
    echo '<tr><td colspan="6" class="text-center text-muted py-4">No centre data found.</td></tr>';
}

while($row =
mysqli_fetch_assoc($result)){

?>

<tr>

<td>

<?php
echo htmlspecialchars($row['centre_name']);
?>

</td>

<td>

<?php
echo $row['total_students'];
?>

</td>

<td>

<?php
echo $row['completed_students'];
?>

</td>

<td>

<?php
echo $row['removed_students'];
?>

</td>

<td>

<?php
echo $row['in_training'];
?>

</td>

<td>

<?php
echo $row['pending_training'];
?>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<!-- Print filters -->
<div class="card border-0 shadow-sm p-4 mt-4">

    <h4 class="fw-bold mb-3">Print Centre Report</h4>

    <div class="row g-4">

        <!-- 1. Entire report -->
        <div class="col-md-4">
            <h6>Entire Report</h6>
            <p class="text-muted small">
                <?php echo $restrict ? "Your centre's all-time distribution." : "All centres, all-time distribution."; ?>
            </p>
            <a href="export_centre_report_pdf.php?filter=all" class="btn btn-primary w-100">
                📄 Print Entire Report
            </a>
        </div>

        <!-- 2. By month range (current year) -->
        <div class="col-md-4">
            <h6>By Month Range (<?php echo $current_year; ?>)</h6>
            <form action="export_centre_report_pdf.php" method="GET" class="row g-2">
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

        <!-- 3. By year -->
        <div class="col-md-4">
            <h6>By Year</h6>
            <form action="export_centre_report_pdf.php" method="GET" class="row g-2">
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

<?php include '../includes/admin_footer.php'; ?>
