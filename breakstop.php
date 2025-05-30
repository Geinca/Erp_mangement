<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$username = $_SESSION['username'];
$today = date('Y-m-d');

// Fetch current break_start and total_break_time
$stmt = $pdo->prepare("SELECT break_start, total_break_time FROM attendance_logs WHERE username = ? AND log_date = ?");
$stmt->execute([$username, $today]);
$log = $stmt->fetch();

if ($log && $log['break_start']) {
    $breakStart = new DateTime($log['break_start']);
    $now = new DateTime();

    $breakDuration = $now->getTimestamp() - $breakStart->getTimestamp();

    // Calculate new total break time in seconds
    $totalBreakSeconds = 0;
    if (!empty($log['total_break_time'])) {
        list($h, $m, $s) = explode(':', $log['total_break_time']);
        $totalBreakSeconds = $h*3600 + $m*60 + $s;
    }
    $totalBreakSeconds += $breakDuration;

    // Format back to HH:MM:SS
    $hours = floor($totalBreakSeconds / 3600);
    $minutes = floor(($totalBreakSeconds % 3600) / 60);
    $seconds = $totalBreakSeconds % 60;
    $totalBreakTimeFormatted = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

    // Update DB: break_stop time and total_break_time, clear break_start
    $update = $pdo->prepare("UPDATE attendance_logs SET break_stop = NOW(), total_break_time = ?, break_start = NULL WHERE username = ? AND log_date = ?");
    $update->execute([$totalBreakTimeFormatted, $username, $today]);
}

header("Location: timer.php");
exit();
