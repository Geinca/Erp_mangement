<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
require 'db.php';

$username = $_SESSION['username'];
$now = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// Check if user already checked in today
$stmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE username = ? AND log_date = ?");
$stmt->execute([$username, $today]);
$existing = $stmt->fetch();

if ($existing && $existing['check_in']) {
    // Already checked in, go to timer page
    header("Location: timer.php");
    exit();
}

// Insert new check-in record
$stmt = $pdo->prepare("INSERT INTO attendance_logs (username, check_in, log_date) VALUES (?, ?, ?)");
$stmt->execute([$username, $now, $today]);

header("Location: timer.php");
exit();
?>
