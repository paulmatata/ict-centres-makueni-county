<?php
session_start();
require_once "../includes/db.php";
include '../admin_header.php';

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}
$centre_id = isset ($_SESSION['centre_id'])? $_SESSION['centre_id'] : 0;

$admin_id = $_SESSION['admin_id'];

try {

    // Update students whose training period has ended
    $sql = "UPDATE students 
            SET completion_status = 'completed',
            training_status = 'completed'
            WHERE status = 'active'
            AND completion_status = 'incomplete'
            AND DATEDIFF(CURDATE(), training_start_date) >= 35
            AND centre_id=?"; //restirct admins to update only students of their centres. super admins should not be able to update students unless they are tied to such centres.

    $stmt = $conn->prepare($sql);
    $stmt->execute([$centre_id]);

    // Update students whose training period has started
$sql_start = "UPDATE students 
              SET training_status = 'in training' 
              WHERE status = 'active' 
              AND completion_status = 'incomplete' 
              AND training_status != 'in training' -- Performance boost: avoids rewriting rows already set
              AND training_start_date <= CURDATE()
              AND centre_id=?";

$stmt_start = $conn->prepare($sql_start);
$stmt_start->execute([$centre_id]);

    $updated_count = $stmt->affected_rows + $stmt_start-> affected_rows;

    // Log the action
    $log = "Updated completion status for $updated_count student(s) automatically after 5 weeks.";

    $logStmt = $conn->prepare(
        "INSERT INTO system_logs(admin_id, action)
         VALUES (?, ?)"
    );

    $logStmt->execute([
        $admin_id,
        $log
    ]);

    $_SESSION['success'] =
        "$updated_count student(s) marked as completed successfully.";

} catch (PDOException $e) {

    $_SESSION['error'] =
        "Error updating students: " . $e->getMessage();
}

header("Location: manage_students.php");
exit();
include '../includes/admin_footer.php';
?>
