<?php
require '../config/db.php';
require '../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $code = $_POST['code'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        $stmt = $pdo->prepare("SELECT * FROM verification_codes WHERE user_id = ? AND code = ? AND expires_at > NOW()");
        $stmt->execute([$user['id'], $code]);
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("UPDATE users SET verified = TRUE WHERE id = ?");
            $stmt->execute([$user['id']]);
            $stmt = $pdo->prepare("DELETE FROM verification_codes WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            header('Location: login.php');
        } else {
            echo '<div class="alert alert-danger">Invalid or expired code</div>';
        }
    } else {
        echo '<div class="alert alert-danger">User not found</div>';
    }
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Verify Email</h2>
<form method="POST" class="card p-4">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($_GET['email'] ?? ''); ?>" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Verification Code</label>
        <input type="text" name="code" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Verify</button>
</form>
<?php include '../includes/footer.php'; ?>