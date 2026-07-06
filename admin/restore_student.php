<?php
session_start();
require_once "../includes/db.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET["id"])) {
    header("Location: manage_students.php");
    exit();
}

$student_id = $_GET["id"];
$admin_id = $_SESSION["admin_id"];

// Get student name
$getStudent = $conn->prepare(
    "SELECT fullname FROM students WHERE id = ?"
);

$getStudent->execute([$student_id]);

$student = $getStudent->fetch();

if (!$student) {
    die("Student not found.");
}


// Restore student
$stmt = $conn->prepare("
    UPDATE students
    SET 
        status = 'active',
        removal_reason = NULL,
        removed_at = NULL
    WHERE id = ?
");

$stmt->execute([$student_id]);


// Create log
$action = "Restored student: "
         . $student["fullname"];

$log = $conn->prepare("
    INSERT INTO system_logs(admin_id, action)
    VALUES(?, ?)
");

$log->execute([
    $admin_id,
    $action
]);


$_SESSION["success"] =
    "Student restored successfully.";

header("Location: manage_students.php");
exit();
?>