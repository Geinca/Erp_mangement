<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  echo json_encode(['success' => false, 'message' => 'Invalid request method']);
  exit;
}

if (!isset($_SESSION['username'])) {
  echo json_encode(['success' => false, 'message' => 'Not authenticated']);
  exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
  echo json_encode(['success' => false, 'message' => 'Invalid JSON']);
  exit;
}

$username = $_SESSION['username'];
$checkIn = $input['checkIn'] ?? null;
$checkOut = $input['checkOut'] ?? null;
$totalBreakSeconds = $input['totalBreakSeconds'] ?? 0;
$totalWorkedSeconds = $input['totalWorkedSeconds'] ?? 0;

if (!$checkIn || !$checkOut) {
  echo json_encode(['success' => false, 'message' => 'Missing check-in or check-out times']);
  exit;
}

// Save data to a file - you can replace with DB logic later
$log = [
  'username' => $username,
  'checkIn' => $checkIn,
  'checkOut' => $checkOut,
  'totalBreakSeconds' => (int)$totalBreakSeconds,
  'totalWorkedSeconds' => (int)$totalWorkedSeconds,
  'savedAt' => date('c')
];

$file = __DIR__ . '/worklogs.json';

$logs = [];
if (file_exists($file)) {
  $json = file_get_contents($file);
  $logs = json_decode($json, true) ?: [];
}

$logs[] = $log;

if (file_put_contents($file, json_encode($logs, JSON_PRETTY_PRINT))) {
  echo json_encode(['success' => true]);
} else {
  echo json_encode(['success' => false, 'message' => 'Failed to save log']);
}
