<?php
// Start session and include database connection
session_start();
require 'db.php'; // Replace with your actual database connection file

// Initialize variables
$token = $_GET['token'] ?? null;
$error = $success = "";

// Check if token is provided
if (!$token) {
    die("Invalid request. No token provided.");
}

// Handle password reset form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $password = trim($_POST['password']);
    $confirmPassword = trim($_POST['confirm_password']);

    if (empty($password) || empty($confirmPassword)) {
        $error = "All fields are required.";
    } elseif ($password !== $confirmPassword) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // Validate the token
        $stmt = $conn->prepare("SELECT id FROM users WHERE reset_token = ? AND token_expiry > NOW()");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Update the password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, token_expiry = NULL WHERE reset_token = ?");
            $stmt->bind_param("ss", $hashedPassword, $token);

            if ($stmt->execute()) {
                $success = "Your password has been reset successfully.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        } else {
            $error = "Invalid or expired token.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css"/>
</head>
<body>
<div class="container">
    <div class="card card-container">
        <h2 class="text-center">Reset Password</h2>
        <br>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
        <form method="post" action="reset_password.php?token=<?= htmlspecialchars($token) ?>">
            <div class="form-group">
                <label for="password">New Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm New Password</label>
                <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
        </form>
        <?php endif; ?>
        <div class="text-center mt-3">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</div>
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
