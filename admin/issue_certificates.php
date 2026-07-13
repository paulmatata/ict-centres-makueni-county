<?php

include '../includes/db.php';
include '../includes/admin_auth.php';

$role = $_SESSION['admin_role'];
$centre_id = $_SESSION['centre_id'];

// A super_admin with no centre attached to their account (i.e. an
// "all centres" super_admin) cannot issue certificates — only admins
// tied to a specific centre can, regardless of role.
if (empty($centre_id)) {
    $_SESSION['error'] = "Only admins assigned to a specific centre can issue certificates.";
    header("Location: manage_students.php");
    exit();
}

if (!isset($_POST['student_ids']) || !is_array($_POST['student_ids'])) {
    $_SESSION['error'] = "No students selected.";
    header("Location: manage_students.php");
    exit();
}

$student_ids = array_map('intval', $_POST['student_ids']);
$issued_count = 0;
$skipped_count = 0;

$year = date('Y');

// Get the current highest serial number for this year, once, then
// increment locally for each student in this batch.
$count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) AS total FROM students WHERE certificate_serial LIKE ?");
$like = "%/" . $year;
mysqli_stmt_bind_param($count_stmt, "s", $like);
mysqli_stmt_execute($count_stmt);
$next_number = (int) mysqli_fetch_assoc(mysqli_stmt_get_result($count_stmt))['total'];

foreach ($student_ids as $id) {

    // Re-check completion status, ownership (centre), and that it isn't
    // already issued — server-side, not trusting the submitted list blindly.
    $check_stmt = mysqli_prepare($conn,
        "SELECT completion_status, certificate_serial FROM students WHERE id = ? AND centre_id = ?"
    );
    mysqli_stmt_bind_param($check_stmt, "ii", $id, $centre_id);
    mysqli_stmt_execute($check_stmt);
    $row = mysqli_fetch_assoc(mysqli_stmt_get_result($check_stmt));

    if (!$row || $row['completion_status'] !== 'completed' || !empty($row['certificate_serial'])) {
        $skipped_count++;
        continue;
    }

    $next_number++;
    $serial = "S/NO:ICT/" . str_pad($next_number, 4, '0', STR_PAD_LEFT) . "/" . $year;

    $update_stmt = mysqli_prepare($conn, "UPDATE students SET certificate_serial = ? WHERE id = ?");
    mysqli_stmt_bind_param($update_stmt, "si", $serial, $id);
    mysqli_stmt_execute($update_stmt);

    $issued_count++;
}

if ($issued_count > 0) {
    $_SESSION['success'] = "$issued_count certificate(s) issued." . ($skipped_count > 0 ? " ($skipped_count skipped — not completed, already issued, or not in your centre.)" : "");
} else {
    $_SESSION['error'] = "No certificates were issued. Selected students may already be issued, not completed, or not in your centre.";
}

header("Location: manage_students.php");
exit();

?>
