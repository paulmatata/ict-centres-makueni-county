<?php

include 'includes/db.php';
include 'includes/header.php';

$serial = isset($_GET['serial']) ? trim($_GET['serial']) : '';
$student = null;

if ($serial !== '') {
    $stmt = mysqli_prepare($conn,
        "SELECT students.fullname, students.completion_status, students.certificate_serial,
                ict_centres.centre_name
         FROM students
         LEFT JOIN ict_centres ON students.centre_id = ict_centres.id
         WHERE students.certificate_serial = ?"
    );
    mysqli_stmt_bind_param($stmt, "s", $serial);
    mysqli_stmt_execute($stmt);
    $student = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

?>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-6">
      <div class="card border-0 shadow-lg rounded-4 p-4 text-center">

        <h3 class="fw-bold mb-4">Certificate Verification</h3>

        <?php if ($serial === ''): ?>

            <p class="text-muted">No certificate serial number was provided.</p>

        <?php elseif ($student && $student['completion_status'] === 'completed'): ?>

            <div class="mb-3" style="font-size: 3rem; color: var(--mc-green);">
                <i class="bi bi-patch-check-fill"></i>
            </div>

            <h5 class="fw-bold">Certificate Verified</h5>

            <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($student['fullname']); ?></p>
            <p class="mb-1"><strong>ICT Centre:</strong> <?php echo htmlspecialchars($student['centre_name']); ?></p>
            <p class="mb-0"><strong>Serial:</strong> <?php echo htmlspecialchars($student['certificate_serial']); ?></p>

            <p class="text-muted mt-3 small">
                This certificate was issued by the Makueni County ICT Centres
                Digital Empowerment Programme.
            </p>

        <?php else: ?>

            <div class="mb-3" style="font-size: 3rem; color: #dc3545;">
                <i class="bi bi-x-circle-fill"></i>
            </div>

            <h5 class="fw-bold">Not Found</h5>

            <p class="text-muted">
                This serial number doesn't match any issued certificate on record.
            </p>

        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
