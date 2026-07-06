<?php
require_once "../includes/db.php";
include "../includes/header.php";
include "../includes/admin_auth.php";

// Check both common session variables to be completely safe
$session_id = $_SESSION['admin_id'] ?? $_SESSION['id'] ?? null;

if (!$session_id) {
    header("Location: admin_login.php");
    exit();
}

$success = "";
$error = "";
$admin_id = $session_id;

// --- MYSQLI PREPARED STATEMENT ---
$stmt = mysqli_prepare($conn, "SELECT * FROM admins WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $admin_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

// --- SAFEGUARD: If the user doesn't exist in the database, log out gracefully ---
if (!$admin) {
    session_destroy();
    header("Location: admin_login.php?error=account_not_found");
    exit();
}

// Check database columns safely using null coalescing ??
$username = $admin['username'] ?? 'Admin';
$mustChange = $admin['must_change_password'] ?? 0;
$userRole = $admin['role'] ?? $admin['username'] ?? ''; 

$isFirstLogin = ($mustChange == 1 && $userRole !== 'superadmin');


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $current_password = $_POST["current_password"] ?? "";
    $new_password = trim($_POST["new_password"] ?? "");
    $confirm_password = trim($_POST["confirm_password"] ?? "");

    $blockedPasswords = [
        "Makueni102",
        "Admin123",
        "Password123",
        "12345678"
    ];

    // Check current password
    if (!$isFirstLogin && !password_verify($current_password, $admin['password'])) {
        $error = "Current password is incorrect.";
    } 
    elseif ($new_password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    elseif (strlen($new_password) < 8) {
        $error = "Password must be at least 8 characters.";
    }
    elseif (!preg_match('/[0-9]/', $new_password)) {
        $error = "Password must contain at least one number.";
    }
    elseif (!preg_match('/[^A-Za-z0-9]/', $new_password)) {
        $error = "Password must contain a special character.";
    }
    elseif (in_array($new_password, $blockedPasswords)) {
        $error = "This password is too common. Choose another one.";
    }
    elseif (password_verify($new_password, $admin['password'])) {
        $error = "Your new password must be different from your current password.";
    }
    else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // --- MYSQLI UPDATE STATEMENT ---
        $update_stmt = mysqli_prepare($conn, "UPDATE admins SET password = ?, must_change_password = 0 WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "si", $hashed_password, $admin_id);
        
        if (mysqli_stmt_execute($update_stmt)) {
            // Update current tracking session state
            $_SESSION['must_change_password'] = 0;
            $isFirstLogin = false; 
            
            header("refresh:2; url=admin_dashboard.php");
            $success = "Password changed successfully.";
        } else {
            $error = "Something went wrong updating the database.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Change Admin Password</title>
</head>
<body>

<h2>
<?php if ($isFirstLogin): ?>
    Change Temporary Password
<?php else: ?>
    Change Password
<?php endif; ?>
</h2>

<p>
Welcome, 
<strong>
<?= htmlspecialchars($username) ?>
</strong>
</p>

<?php if ($isFirstLogin): ?>
<p style="color: red;">
For security reasons, you must change your temporary password before accessing the dashboard.
</p>
<?php endif; ?>

<?php if($error): ?>
<p style="color:red;">
<?= $error ?>
</p>
<?php endif; ?>

<?php if($success): ?>
<p style="color:green;">
<?= $success ?>
</p>
<?php endif; ?>

<form method="POST">

<?php if (!$isFirstLogin): ?>
<label>Current Password</label><br>
<input 
    type="password"
    name="current_password"
    required>
<br><br>
<?php endif; ?>

<label>New Password</label><br>
<input 
type="password"
name="new_password"
required>
<br><br>

<label>Confirm New Password</label><br>
<input 
type="password"
name="confirm_password"
required>
<br><br>

<button type="submit">
Change Password
</button>

</form>

</body>
</html>
