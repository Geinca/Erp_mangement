<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require 'db.php';

$username = $_SESSION['username'];
$today = date('Y-m-d');
$now = date('Y-m-d H:i:s');

// Fetch current log
$stmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE username = ? AND log_date = ?");
$stmt->execute([$username, $today]);
$log = $stmt->fetch();

if (!$log || !$log['check_in']) {
    // Not checked in yet
    header("Location: timer.php");
    exit();
}

if ($log['check_out']) {
    // Already checked out
    header("Location: timer.php");
    exit();
}

// If break ongoing, stop it before checkout and add break time
$totalBreakSeconds = 0;
if (!empty($log['total_break_time'])) {
    list($bh, $bm, $bs) = explode(":", $log['total_break_time']);
    $totalBreakSeconds = $bh*3600 + $bm*60 + $bs;
}

if (!empty($log['break_start']) && empty($log['break_stop'])) {
    $breakStart = new DateTime($log['break_start']);
    $breakStop = new DateTime($now);
    $breakDiff = $breakStop->getTimestamp() - $breakStart->getTimestamp();
    $totalBreakSeconds += $breakDiff;
}

$totalBreakTimeFormatted = sprintf("%02d:%02d:%02d", floor($totalBreakSeconds/3600), floor(($totalBreakSeconds%3600)/60), $totalBreakSeconds%60);

$stmt = $pdo->prepare("UPDATE attendance_logs SET check_out = ?, break_stop = ?, total_break_time = ?, break_start = NULL WHERE username = ? AND log_date = ?");
$stmt->execute([$now, $now, $totalBreakTimeFormatted, $username, $today]);

header("Location: timer.php");
exit();
?>
