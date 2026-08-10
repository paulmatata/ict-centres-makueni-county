<?php
include 'includes/db.php';
session_start();

$fullname         = trim($_POST['fullname']);
$username         = trim($_POST['username']);
$email            = trim($_POST['email']);
$phone            = trim($_POST['phone']);
$password_raw     = $_POST['password'];
$confirm_password = $_POST['confirm_password'];
$centre_id        = $_POST['centre_id'];
$consent_given = 1;
$consent_timestamp = date("Y-m-d H:i:s");

// 1. EMPTY FIELDS
if (empty($fullname) || empty($username) || empty($email) || empty($phone) ||
    empty($password_raw) || empty($confirm_password) || empty($centre_id)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: register.php"); exit();
}

// 2. PASSWORD MATCH
if ($password_raw !== $confirm_password) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: register.php"); exit();
}

// 3. PASSWORD STRENGTH
if (strlen($password_raw) < 8) {
    $_SESSION['error'] = "Password must be at least 8 characters.";
    header("Location: register.php"); exit();
}
if (!preg_match('/[A-Z]/', $password_raw)) {
    $_SESSION['error'] = "Password must contain at least one uppercase letter.";
    header("Location: register.php"); exit();
}
if (!preg_match('/[a-z]/', $password_raw)) {
    $_SESSION['error'] = "Password must contain at least one lowercase letter.";
    header("Location: register.php"); exit();
}
if (!preg_match('/[0-9]/', $password_raw)) {
    $_SESSION['error'] = "Password must contain at least one number.";
    header("Location: register.php"); exit();
}
if (!preg_match('/[^A-Za-z0-9]/', $password_raw)) {
    $_SESSION['error'] = "Password must contain at least one special character.";
    header("Location: register.php"); exit();
}

// 4. HASH PASSWORD
$password = password_hash($password_raw, PASSWORD_DEFAULT);

// 5. TRAINING START DATE (Constant-time O(1) logic)
date_default_timezone_set('Africa/Nairobi');
$today       = new DateTime('today');
$base_intake = new DateTime('2026-07-27');
$day_number  = (int)$today->format('N');

// Total days elapsed since base intake
$days_diff = $today->diff($base_intake)->days;

// Determine offset within the 35-day (5-week) cycle
$cycle_days  = 35; 
$days_into_cycle = $days_diff % $cycle_days;

// Active week is the first 7 days (days 0 to 6) of the 35-day cycle
$is_active_week = ($today >= $base_intake) && ($days_into_cycle < 7);

if ($is_active_week && $day_number >= 1 && $day_number <= 4) {
    // If active week Mon–Thu, start tomorrow
    $training_start = clone $today;
    $training_start->modify('+1 day');
} else {
    // Calculate exact start of the NEXT 5-week cycle without loops
    $cycles_passed = floor($days_diff / $cycle_days);
    $next_cycle_number = ($today < $base_intake) ? 0 : $cycles_passed + 1;
    
    $training_start = clone $base_intake;
    $training_start->modify('+' . ($next_cycle_number * 5) . ' weeks');
}

$training_start_date = $training_start->format('Y-m-d');

// 6. CHECK DUPLICATE USERNAME
$stmt_check = mysqli_prepare($conn, "SELECT id FROM students WHERE username = ?");
mysqli_stmt_bind_param($stmt_check, "s", $username);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);
if (mysqli_stmt_num_rows($stmt_check) > 0) {
    $_SESSION['error'] = "Username already exists.";
    header("Location: register.php"); exit();
}
mysqli_stmt_close($stmt_check);

// 7. CHECK DUPLICATE EMAIL
$stmt_check = mysqli_prepare($conn, "SELECT id FROM students WHERE email = ?");
mysqli_stmt_bind_param($stmt_check, "s", $email);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_store_result($stmt_check);
if (mysqli_stmt_num_rows($stmt_check) > 0) {
    $_SESSION['error'] = "Email already exists.";
    header("Location: register.php"); exit();
}
mysqli_stmt_close($stmt_check);

//8. confirm user consent.
if (!isset($_POST['data_consent']) || $_POST['data_consent'] !== 'on') {
    $_SESSION['error'] = "You must agree to the Data Protection & Privacy Policy to register.";
    header("Location: register.php");
    exit;
}


// 9. INSERT
$stmt = mysqli_prepare($conn,
    "INSERT INTO students (fullname, username, email, phone, password, centre_id, registration_fee_status, training_fee_note, training_start_date, training_status, consent_given, consent_timestamp)
     VALUES (?, ?, ?, ?, ?, ?, 'pending', 'Student will pay Ksh. 1000 certificate fee after completing training at the training centre', ?, 'Upcoming', ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sssssisis",
    $fullname, $username, $email, $phone, $password, $centre_id, $training_start_date, $consent_given, $consent_timestamp);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    $_SESSION['success'] = "Account created successfully! Please log in.";
    header("Location: login.php"); exit();
} else {
    //capture error logs.
    error_log("Database Insertion Error: " . mysqli_stmt_error($stmt));
    
    mysqli_stmt_close($stmt);
    $_SESSION['error'] = "Registration failed. Please try again.";
    header("Location: register.php"); exit();
}
?>
