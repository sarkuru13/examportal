<?php
session_start();
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'tab_change') {
    file_put_contents('violations.log', "Tab change detected for student ID: {$_SESSION['student_id']} at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
    echo json_encode(['status' => 'reported']);
}
?>