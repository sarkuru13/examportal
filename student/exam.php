<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header('Location: ../index.php');
    exit;
}
require '../config/db.php';

$exam_id = $_GET['exam_id'] ?? 1;
$stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = ?");
$stmt->execute([$exam_id]);
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Load saved progress
$stmt = $pdo->prepare("SELECT answers FROM exam_progress WHERE student_id = ? AND exam_id = ?");
$stmt->execute([$_SESSION['student_id'], $exam_id]);
$progress = $stmt->fetch();
$saved_answers = $progress ? json_decode($progress['answers'], true) : [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $answers = json_decode(file_get_contents('php://input'), true);
    $score = 0;
    foreach ($questions as $question) {
        if (isset($answers[$question['id']]) && $answers[$question['id']] === $question['correct_answer']) {
            $score += $question['marks'];
        }
    }
    $stmt = $pdo->prepare("INSERT INTO results (student_id, exam_id, score) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['student_id'], $exam_id, $score]);
    $stmt = $pdo->prepare("DELETE FROM exam_progress WHERE student_id = ? AND exam_id = ?");
    $stmt->execute([$_SESSION['student_id'], $exam_id]);
    echo json_encode(['score' => $score]);
    exit;
}
?>

<?php include '../includes/header.php'; ?>
<h2 class="mb-4">Exam</h2>
<form id="examForm" class="card p-4">
    <?php foreach ($questions as $index => $question): ?>
        <div class="mb-3">
            <p class="fw-bold"><?php echo ($index + 1) . '. ' . htmlspecialchars($question['question']); ?> (<?php echo $question['marks']; ?> marks)</p>
            <?php $options = json_decode($question['options'], true); ?>
            <?php foreach ($options as $option): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="question[<?php echo $question['id']; ?>]" value="<?php echo htmlspecialchars($option); ?>" 
                        <?php echo isset($saved_answers[$question['id']]) && $saved_answers[$question['id']] === $option ? 'checked' : ''; ?> required>
                    <label class="form-check-label"><?php echo htmlspecialchars($option); ?></label>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
    <button type="submit" class="btn btn-primary">Submit Exam</button>
</form>
<div id="result" class="mt-4 d-none">
    <h3>Your Score: <span id="score"></span></h3>
</div>
<script>
document.getElementById('examForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const answers = {};
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('question[')) {
            const id = key.match(/\[(\d+)\]/)[1];
            answers[id] = value;
        }
    }
    fetch('exam.php?exam_id=<?php echo $exam_id; ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(answers)
    }).then(res => res.json()).then(data => {
        document.getElementById('score').textContent = data.score;
        document.getElementById('result').classList.remove('d-none');
    });
});
</script>
<?php include '../includes/footer.php'; ?>