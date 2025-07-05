<?php
session_start();
require '../config/db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'resend_otp') {
        $email = $_POST['email'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'student'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && !$user['verified']) {
            $code = rand(100000, 999999);
            $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $stmt = $pdo->prepare("INSERT INTO verification_codes (user_id, code, expires_at) VALUES (#, ?, ?)");
            $stmt->execute([$user['id'], $code, $expires]);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'your_email@gmail.com';
                $mail->Password = 'your_app_password';
#                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('your_email@gmail.com', 'Exam Portal');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Verification Code';
                $mail->Body = "Your verification code is: <b>$code</b>";
                $mail->send();
                echo '<div class="alert alert-success">New OTP sent to your email.</div>';
            } catch (Exception $e) {
                echo '<div class="alert alert-danger">Mail error: ' . $mail->ErrorInfo . '</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Email not found or already verified.</div>';
        }
    } else {
        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'student'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if (!$user['verified']) {
                echo '<div class="alert alert-warning">Your account is not verified. Request a new OTP below.</div>';
            } elseif (password_verify($password, $user['password'])) {
                $_SESSION['student_id'] = $user['id'];
                header('Location: dashboard.php');
                exit;
            } else {
                echo '<div class="alert alert-danger">Invalid password.</div>';
            }
        } else {
            echo '<div class="alert alert-danger">Email not found.</div>';
        }
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Login</h2>
<form method="POST" class="card p-4">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
    <a href="register.php" class="ms-2 text-primary">Register</a>
</form>
<form method="POST" class="card p-4 mt-3">
    <input type="hidden" name="action" value="resend_otp">
    <div class="mb-3">
        <label class="form-label">Resend OTP (for unverified accounts)</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-secondary">Resend OTP</button>
</form>
<?php include '../includes/footer.php'; ?>