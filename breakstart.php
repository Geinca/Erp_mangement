<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$username = $_SESSION['username'];
$today = date('Y-m-d');

// Start break only if not already started and no check out
$stmt = $pdo->prepare("UPDATE attendance_logs SET break_start = NOW(), break_stop = NULL WHERE username = ? AND log_date = ? AND break_start IS NULL AND check_out IS NULL");
$stmt->execute([$username, $today]);

header("Location: timer.php");
exit();
