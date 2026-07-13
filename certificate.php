<?php

session_start();

include 'includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';

$student_id = $_SESSION['student_id'];

$stmt = mysqli_prepare($conn,
    "SELECT students.*, ict_centres.centre_name
     FROM students
     LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
     WHERE students.id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$is_completed = ($student['completion_status'] === 'completed');
$is_issued = !empty($student['certificate_serial']);

?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card border-0 shadow-lg rounded-4 p-4 text-center">

        <i class="bi bi-award" style="font-size: 3rem; color: var(--mc-green);"></i>

        <h3 class="fw-bold mt-3 mb-4">My Certificate</h3>

        <?php if ($is_issued): ?>

            <p class="text-muted">Your certificate is ready.</p>
            <p class="mb-4"><strong>Serial:</strong> <?php echo htmlspecialchars($student['certificate_serial']); ?></p>

            <a href="generate_certificate.php" class="btn btn-primary btn-lg" target="_blank">
                <i class="bi bi-download"></i> Download Certificate
            </a>

        <?php elseif ($is_completed): ?>

            <p class="text-muted">
                You've completed training! Your certificate is being prepared by your ICT centre
                and will appear here once issued.
            </p>

        <?php else: ?>

            <p class="text-muted">
                Your certificate will be available here once you complete your training.
            </p>

        <?php endif; ?>

        <a href="dashboard.php" class="btn btn-link mt-3">Back to Dashboard</a>

      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
