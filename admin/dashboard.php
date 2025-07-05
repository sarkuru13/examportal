<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../index.php');
    exit;
}
require '../config/db.php';

$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
$exam_data = json_encode($exams, JSON_PRETTY_PRINT);
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Admin Dashboard</h2>
<div class="mb-4">
    <a href="upload_questions.php" class="btn btn-primary me-2">Upload Questions</a>
    <a href="create_exam.php" class="btn btn-success">Create Exam</a>
</div>
<h3 class="mb-2">Exams (JSON Format)</h3>
<pre class="bg-dark text-white p-3 rounded"><?php echo htmlspecialchars($exam_data); ?></pre>
<?php include '../includes/footer.php'; ?>