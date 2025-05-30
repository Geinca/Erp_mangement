<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Work Logs</title>
<style>
  body {
    font-family: Arial, sans-serif;
    margin: 2rem;
    background: #f7f9fc;
    color: #222;
  }
  h1 {
    color: #0d47a1;
  }
  table {
    border-collapse: collapse;
    width: 100%;
    max-width: 900px;
    margin-top: 1rem;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
  }
  th, td {
    border: 1px solid #ddd;
    padding: 12px 15px;
    text-align: left;
  }
  th {
    background-color: #1976d2;
    color: white;
  }
  tr:nth-child(even) {
    background-color: #f1f9ff;
  }
  caption {
    caption-side: top;
    margin-bottom: 1rem;
    font-weight: 700;
    font-size: 1.2rem;
  }
</style>
</head>
<body>
  <h1>Work Logs for <?= $username ?></h1>

  <?php
  $file = __DIR__ . '/worklogs.json';
  if (!file_exists($file)) {
    echo "<p>No work logs found.</p>";
    exit;
  }
  $logs = json_decode(file_get_contents($file), true);
  if (!$logs) {
    echo "<p>No valid logs found.</p>";
    exit;
  }

  // Filter logs for current user
  $userLogs = array_filter($logs, fn($log) => $log['username'] === $username);

  if (!$userLogs) {
    echo "<p>No logs found for user.</p>";
    exit;
  }
  ?>

  <table>
    <caption>Work Time Logs</caption>
    <thead>
      <tr>
        <th>#</th>
        <th>Check In</th>
        <th>Check Out</th>
        <th>Total Break</th>
        <th>Total Worked</th>
        <th>Saved At</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($userLogs as $i => $log): ?>
        <tr>
          <td><?= $i + 1 ?></td>
          <td><?= date('Y-m-d H:i:s', strtotime($log['checkIn'])) ?></td>
          <td><?= date('Y-m-d H:i:s', strtotime($log['checkOut'])) ?></td>
          <td><?= gmdate('H:i:s', $log['totalBreakSeconds']) ?></td>
          <td><?= gmdate('H:i:s', $log['totalWorkedSeconds']) ?></td>
          <td><?= date('Y-m-d H:i:s', strtotime($log['savedAt'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</body>
</html>
