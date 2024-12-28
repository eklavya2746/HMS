<?php
// Start session and include necessary files
session_start();
require 'db.php'; // Database connection file
require 'autoload.php'; // PHPMailer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Check if email exists in the database
        $stmt = $conn->prepare("SELECT id FROM user WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Generate token and expiry
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Update token in the database
            $stmt = $conn->prepare("UPDATE user SET reset_token = ?, token_expiry = ? WHERE email = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            // Send password reset email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Your SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'your_email@gmail.com'; // Your email
                $mail->Password = 'your_email_password'; // Your email password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $resetLink = "http://yourwebsite.com/reset_password.php?token=$token";
                $mail->setFrom('no-reply@yourwebsite.com', 'Your Website');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Password Reset Request';
                $mail->Body = "Click the link below to reset your password:<br><a href='$resetLink'>$resetLink</a>";

                $mail->send();
                $success = "Password reset link has been sent to your email.";
            } catch (Exception $e) {
                $error = "Failed to send email. Mailer Error: {$mail->ErrorInfo}";
            }
        } else {
            $error = "No account found with that email.";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css">
</head>
<body>
<div class="container">
    <div class="card card-container">
        <h2 class="text-center">Forgot Password</h2>
        <br>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif (isset($success)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="post" action="forgot_password.php">
            <div class="form-group">
                <label for="email">Enter your registered email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Send Reset Link</button>
        </form>
        <div class="text-center mt-3">
            <a href="index.php">Back to Login</a>
        </div>
    </div>
</div>
<script src="js/jquery-1.11.1.min.js"></script>
<script src="js/bootstrap.min.js"></script>
</body>
</html>
