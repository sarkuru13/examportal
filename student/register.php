<?php
require '../config/db.php';
require '../vendor/autoload.php'; // Use Composer autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        die('Email already exists');
    }

    $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'student')");
    $stmt->execute([$email, $password]);
    $user_id = $pdo->lastInsertId();

    $code = rand(100000, 999999);
    $expires = date('Y-m-d H:i:s', strtotime('+10 minutes'));
    $stmt = $pdo->prepare("INSERT INTO verification_codes (user_id, code, expires_at) VALUES (?, ?, ?)");
    $stmt->execute([$user_id, $code, $expires]);

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'engtisarkuru13@gmail.com';
        $mail->Password = 'cpqs qqei cvve uobm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('engtisarkuru13@gmail.com', 'Exam Portal');
        $mail->addAddress($email);
        $mail->isHTML(true);
        $mail->Subject = 'Verification Code';
        $mail->Body = "Your verification code is: <b>$code</b>";
        $mail->send();
        header('Location: verify.php?email=' . urlencode($email));
    } catch (Exception $e) {
        die('Mail error: ' . $mail->ErrorInfo);
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Register</h2>
<form method="POST" class="card p-4">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>
<?php include '../includes/footer.php'; ?>