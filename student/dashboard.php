<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}
require '../config/db.php';

$stmt = $pdo->prepare("SELECT * FROM results WHERE student_id = ?");
$stmt->execute([$_SESSION['student_id']]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
$results_json = json_encode($results, JSON_PRETTY_PRINT);

$stmt = $pdo->query("SELECT * FROM exams");
$exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Student Dashboard</h2>
<div class="mb-4">
    <h3 class="mb-2">Available Exams</h3>
    <?php foreach ($exams as $exam): ?>
        <a href="exam.php?exam_id=<?php echo $exam['id']; ?>" class="btn btn-primary mb-2"><?php echo htmlspecialchars($exam['title']); ?></a>
    <?php endforeach; ?>
</div>
<h3 class="mb-2">Exam History (JSON Format)</h3>
<pre class="bg-dark text-white p-3 rounded"><?php echo htmlspecialchars($results_json); ?></pre>
<?php include '../includes/footer.php'; ?>