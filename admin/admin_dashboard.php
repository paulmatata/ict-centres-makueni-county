<?php
include '../includes/db.php';
include '../includes/admin_auth.php';
include '../includes/admin_header.php';
include 'auth_check.php';
include '../includes/admin_sidebar.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if(!isset($_SESSION['admin_id'])){

    header("Location: admin_login.php");

    exit();

}

$centre_id = $_SESSION['centre_id'];

$role = $_SESSION['admin_role'];

if($role == 'super_admin'){

    $student_query =
    mysqli_query(
        $conn,
        "SELECT * FROM students"
    );

}else{
    $stmt = mysqli_prepare($conn,
        "SELECT * FROM students

        WHERE centre_id=?"
    );
    mysqli_stmt_bind_param($stmt, "i", $centre_id);
    mysqli_stmt_execute($stmt);
    $student_query = mysqli_stmt_get_result($stmt);

}

$students =
mysqli_num_rows($student_query);
$admins =
mysqli_num_rows(
mysqli_query($conn,
"SELECT * FROM admins")
);

$centre_stmt = mysqli_prepare($conn, "SELECT * FROM ict_centres WHERE id=?");
mysqli_stmt_bind_param($centre_stmt, "i", $centre_id);
mysqli_stmt_execute($centre_stmt);
$centre = mysqli_fetch_assoc(mysqli_stmt_get_result($centre_stmt));

//dashboard charts
$restrict = ($role != 'super_admin');

// ---- 1. Completion breakdown: Completed / In Training / Pending ----
$breakdown_sql = "SELECT
    SUM(CASE WHEN completion_status='completed' THEN 1 ELSE 0 END) AS completed,
    SUM(CASE WHEN status='active' AND completion_status='incomplete' AND training_start_date <= CURDATE() THEN 1 ELSE 0 END) AS in_training,
    SUM(CASE WHEN status='active' AND completion_status='incomplete' AND training_start_date > CURDATE() THEN 1 ELSE 0 END) AS pending
    FROM students"
    . ($restrict ? " WHERE centre_id = ?" : "");

$breakdown_stmt = mysqli_prepare($conn, $breakdown_sql);
if ($restrict) {
    mysqli_stmt_bind_param($breakdown_stmt, "i", $centre_id);
}
mysqli_stmt_execute($breakdown_stmt);
$breakdown = mysqli_fetch_assoc(mysqli_stmt_get_result($breakdown_stmt));

$completion_labels = ['Completed', 'In Training', 'Pending'];
$completion_values = [
    (int) $breakdown['completed'],
    (int) $breakdown['in_training'],
    (int) $breakdown['pending'],
];

// ---- 2. Centre performance: average rating + number of ratings per centre ----
// "Average rating" = the mean of all star ratings (1-5) a centre has received.
// "Number of ratings" = how many students actually left a review at all —
// a centre with a 5.0 average from 2 reviews is a much weaker signal than
// a 4.2 average from 40 reviews, which is why both numbers matter together.
$perf_sql = "SELECT ict_centres.centre_name,
    COUNT(reviews.id) AS review_count,
    COALESCE(AVG(reviews.rating), 0) AS avg_rating
    FROM ict_centres
    LEFT JOIN reviews ON reviews.centre_id = ict_centres.id"
    . ($restrict ? " WHERE ict_centres.id = ?" : "") .
    " GROUP BY ict_centres.id";

$perf_stmt = mysqli_prepare($conn, $perf_sql);
if ($restrict) {
    mysqli_stmt_bind_param($perf_stmt, "i", $centre_id);
}
mysqli_stmt_execute($perf_stmt);
$perf_result = mysqli_stmt_get_result($perf_stmt);

$centre_labels = [];
$centre_avg_ratings = [];
$centre_review_counts = [];

while ($row = mysqli_fetch_assoc($perf_result)) {
    $centre_labels[] = $row['centre_name'];
    $centre_avg_ratings[] = round((float) $row['avg_rating'], 2);
    $centre_review_counts[] = (int) $row['review_count'];
}

// ---- 3. Monthly intake trend: student count per month, last 12 months ----
$intake_sql = "SELECT
    DATE_FORMAT(training_start_date, '%Y-%m') AS month,
    COUNT(*) AS total
    FROM students
    WHERE training_start_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)"
    . ($restrict ? " AND centre_id = ?" : "") .
    " GROUP BY month ORDER BY month ASC";

$intake_stmt = mysqli_prepare($conn, $intake_sql);
if ($restrict) {
    mysqli_stmt_bind_param($intake_stmt, "i", $centre_id);
}
mysqli_stmt_execute($intake_stmt);
$intake_result = mysqli_stmt_get_result($intake_stmt);

// Build a full 12-month scaffold first so months with zero intake still show as 0,
// rather than being skipped and making the trend line misleading.
$intake_map = [];
for ($i = 11; $i >= 0; $i--) {
    $month_key = date('Y-m', strtotime("-$i months"));
    $intake_map[$month_key] = 0;
}
while ($row = mysqli_fetch_assoc($intake_result)) {
    $intake_map[$row['month']] = (int) $row['total'];
}

$intake_labels = [];
$intake_values = [];
foreach ($intake_map as $month_key => $count) {
    $intake_labels[] = date('M Y', strtotime($month_key . '-01'));
    $intake_values[] = $count;
}

?>
<div class="admin-content">

<div class="container py-4">

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4">

<div>

<h2 class="fw-bold">
Admin Dashboard
</h2>

<p>
Welcome,
<?php echo $_SESSION['admin_name']; ?>
</p>

<p class="text-muted">
    <?php 
    if ($_SESSION['admin_role']== 'super_admin'){echo "All Centres (Super Admin)";}else{
    echo isset($centre['centre_name'])? $centre['centre_name'] : 'No Centre Assigned'; }
    ?>
</p>

</div>

<div class="d-flex align-items-center justify-content-end gap-2 float-end">
    
    <a href="change_admin_password.php" class="btn btn-secondary btn-sm rounded-pill px-3 d-inline-flex align-items-center">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-key-fill me-1" viewBox="0 0 16 16">
            <path d="M3.5 11.5a.5.5 0 0 1 .5-.5h5.793L8.146 9.354a.5.5 0 1 1 .708-.708l3 3a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708-.708L9.793 13H4a.5.5 0 0 1-.5-.5z"/>
            <path d="M10 4a3 3 0 1 1-6 0 3 3 0 0 1 6 0zm-1 2.5V3a1.5 1.5 0 0 0-3 0v3.5h3z"/>
        </svg>
        Change Password
    </a>



<a href="logout.php"
class="btn btn-danger rounded-pill">

Logout

</a>

</div>
</div>

<div class="row g-4">

<!-- STUDENTS -->

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1 class="text-primary">

<?php echo $students; ?>

</h1>

<h5>Total Students</h5>

</div>

</div>

<!--notes card -->
<div class="col-6 col-lg-3">

<a href="upload_notes.php"
class="text-decoration-none">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1>📤</h1>

<h5>Upload Notes</h5>

</div>

</a>

</div>
    <!-- The following sections are only visible to super_admin -->
<?php if($role == 'super_admin') {?>
<!-- ADMINS -->

<div class="col-6 col-lg-3">

<div class="card border-0 shadow-sm p-4 text-center dashboard-card">

<h1 class="text-success">

<?php echo $admins; ?>

</h1>

<h5>Total Admins</h5>

</div>

</div>
<!--the space left to maintain container size, do not delete-->
<?php } ?>
            
<!-- ============================================================
     DASHBOARD CHARTS — HTML + SCRIPT
     ============================================================ -->

<div class="row g-4 mt-2">

    <!-- Completion breakdown -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Training Status Breakdown</h6>
            <canvas id="completionChart"></canvas>
        </div>
    </div>

    <!-- Centre performance -->
    <div class="col-md-6 col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Centre Performance (Ratings)</h6>
            <canvas id="performanceChart"></canvas>
        </div>
    </div>

    <!-- Monthly intake -->
    <div class="col-md-12 col-lg-4">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h6 class="fw-bold mb-3">Monthly Intake (Last 12 Months)</h6>
            <canvas id="intakeChart"></canvas>
        </div>
    </div>

</div>


    </div>

</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>

<script>

// Data injected from PHP (all values already computed server-side above)
const completionLabels = <?php echo json_encode($completion_labels); ?>;
const completionValues = <?php echo json_encode($completion_values); ?>;

const centreLabels = <?php echo json_encode($centre_labels); ?>;
const centreAvgRatings = <?php echo json_encode($centre_avg_ratings); ?>;
const centreReviewCounts = <?php echo json_encode($centre_review_counts); ?>;

const intakeLabels = <?php echo json_encode($intake_labels); ?>;
const intakeValues = <?php echo json_encode($intake_values); ?>;

// Makueni palette, pulled to match the rest of the dashboard
const mcBlue = '#0B3D69';
const mcGreen = '#1B7A3D';
const mcGold = '#D4A017';
const mcGrey = '#adb5bd';

// ---- 1. Completion breakdown (doughnut) ----
new Chart(document.getElementById('completionChart'), {
    type: 'doughnut',
    data: {
        labels: completionLabels,
        datasets: [{
            data: completionValues,
            backgroundColor: [mcGreen, mcBlue, mcGold],
            borderWidth: 0
        }]
    },
    options: {
        plugins: { legend: { position: 'bottom' } }
    }
});

// ---- 2. Centre performance (dual-axis: avg rating + number of ratings) ----
new Chart(document.getElementById('performanceChart'), {
    data: {
        labels: centreLabels,
        datasets: [
            {
                type: 'bar',
                label: 'Number of Ratings',
                data: centreReviewCounts,
                backgroundColor: mcGrey,
                yAxisID: 'y1'
            },
            {
                type: 'line',
                label: 'Average Rating (out of 5)',
                data: centreAvgRatings,
                borderColor: mcGold,
                backgroundColor: mcGold,
                yAxisID: 'y2',
                tension: 0.3
            }
        ]
    },
    options: {
        scales: {
            y1: {
                type: 'linear',
                position: 'left',
                title: { display: true, text: 'Number of Ratings' },
                beginAtZero: true,
                ticks: { precision: 0 }
            },
            y2: {
                type: 'linear',
                position: 'right',
                title: { display: true, text: 'Average Rating' },
                min: 0,
                max: 5,
                grid: { drawOnChartArea: false }
            }
        },
        plugins: { legend: { position: 'bottom' } }
    }
});

// ---- 3. Monthly intake trend (bar) ----
new Chart(document.getElementById('intakeChart'), {
    type: 'bar',
    data: {
        labels: intakeLabels,
        datasets: [{
            label: 'Students Enrolled',
            data: intakeValues,
            backgroundColor: mcBlue,
            borderRadius: 4
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});

</script>
<?php include '../includes/admin_footer.php'; ?>
