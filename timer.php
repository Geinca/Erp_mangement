<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
require 'db.php';

$username = $_SESSION['username'];
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE username = ? AND log_date = ?");
$stmt->execute([$username, $today]);
$log = $stmt->fetch();

function formatTimeDiff($start, $end) {
    $startDT = new DateTime($start);
    $endDT = new DateTime($end);
    $diff = $startDT->diff($endDT);
    return sprintf("%02d:%02d:%02d", $diff->h, $diff->i, $diff->s);
}

function timeToSeconds($time) {
    list($h, $m, $s) = explode(":", $time);
    return $h*3600 + $m*60 + $s;
}

function secondsToTime($seconds) {
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return sprintf("%02d:%02d:%02d", $h, $m, $s);
}

$netWorkTime = "00:00:00";
if ($log && $log['check_in']) {
    $checkIn = new DateTime($log['check_in']);
    $checkOut = isset($log['check_out']) ? new DateTime($log['check_out']) : new DateTime();

    $workSeconds = $checkOut->getTimestamp() - $checkIn->getTimestamp();

    $breakSeconds = 0;
    if (!empty($log['total_break_time'])) {
        list($bh, $bm, $bs) = explode(":", $log['total_break_time']);
        $breakSeconds = $bh * 3600 + $bm * 60 + $bs;
    }

    if (!empty($log['break_start']) && empty($log['break_stop'])) {
        $breakStart = new DateTime($log['break_start']);
        $breakSeconds += (new DateTime())->getTimestamp() - $breakStart->getTimestamp();
    }

    $netSeconds = max(0, $workSeconds - $breakSeconds);
    $netWorkTime = secondsToTime($netSeconds);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Work Timer</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 min-h-screen flex flex-col items-center p-6 font-sans">

  <h1 class="text-4xl font-extrabold mb-8 text-indigo-800 drop-shadow-md">⏱️ Work Timer</h1>

  <?php if (!$log || !$log['check_in']): ?>
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg text-center">
      <p class="text-lg text-gray-700 mb-4">You have not checked in today.</p>
      <a href="dashboard.php" class="inline-block mt-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
        Back to Dashboard
      </a>
    </div>
  <?php else: ?>
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-lg">
      
      <div class="space-y-4 text-gray-800">
        <p><span class="font-semibold text-indigo-600">Check In:</span> <?= htmlspecialchars($log['check_in']) ?></p>
        <p><span class="font-semibold text-indigo-600">Check Out:</span> <?= htmlspecialchars($log['check_out'] ?? '-') ?></p>
        <p><span class="font-semibold text-indigo-600">Break Start:</span> <?= htmlspecialchars($log['break_start'] ?? '-') ?></p>
        <p><span class="font-semibold text-indigo-600">Break Stop:</span> <?= htmlspecialchars($log['break_stop'] ?? '-') ?></p>
      </div>

      <div class="mt-8 bg-indigo-50 rounded-lg p-6 shadow-inner text-center">
        <p class="text-xl font-semibold text-indigo-700 mb-1">Net Work Time</p>
        <p id="netWorkTime" class="text-5xl font-bold text-indigo-900 tracking-widest"><?= $netWorkTime ?></p>
      </div>

      <div class="mt-4 bg-yellow-50 rounded-lg p-4 shadow-inner text-center">
        <p class="text-lg font-semibold text-yellow-800 mb-1">Total Break Time</p>
        <p id="totalBreakTime" class="text-3xl font-semibold text-yellow-900 tracking-wide"><?= htmlspecialchars($log['total_break_time'] ?? '00:00:00') ?></p>
      </div>

      <div class="mt-8 flex flex-wrap justify-center gap-4">

        <!-- Start Break -->
        <?php if (empty($log['break_start']) && empty($log['check_out'])): ?>
          <form action="breakstart.php" method="post" class="inline">
            <button type="submit" class="flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-yellow-900 font-semibold px-5 py-3 rounded-lg shadow-md transition">
              <i class="fas fa-coffee"></i> Start Break
            </button>
          </form>
        <?php endif; ?>

        <!-- Stop Break -->
        <?php if (!empty($log['break_start']) && empty($log['check_out'])): ?>
          <form action="breakstop.php" method="post" class="inline">
            <button type="submit" class="flex items-center gap-2 bg-purple-600 hover:bg-purple-700 text-white font-semibold px-5 py-3 rounded-lg shadow-md transition">
              <i class="fas fa-stopwatch"></i> Stop Break
            </button>
          </form>
        <?php endif; ?>

        <!-- Check Out -->
        <?php if (empty($log['check_out'])): ?>
          <form action="checkout.php" method="post" class="inline">
            <button type="submit" class="flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white font-semibold px-5 py-3 rounded-lg shadow-md transition">
              <i class="fas fa-sign-out-alt"></i> Check Out
            </button>
          </form>
        <?php else: ?>
          <p class="text-green-700 font-bold mt-6 text-center text-lg">✅ You have checked out. Great job!</p>
        <?php endif; ?>

      </div>
    </div>

    <a href="dashboard.php" class="mt-8 text-indigo-700 font-semibold underline hover:text-indigo-900 transition">
      ← Back to Dashboard
    </a>
  <?php endif; ?>

  <script>
    function timeToSeconds(t) {
      const parts = t.split(':').map(x => parseInt(x, 10));
      return parts[0]*3600 + parts[1]*60 + parts[2];
    }

    function secondsToTime(s) {
      const h = Math.floor(s/3600);
      const m = Math.floor((s % 3600) / 60);
      const sec = s % 60;
      return [h,m,sec].map(v => v.toString().padStart(2, '0')).join(':');
    }

    let netWorkTimeEl = document.getElementById('netWorkTime');
    let totalBreakTimeEl = document.getElementById('totalBreakTime');

    let netWorkSeconds = timeToSeconds(netWorkTimeEl.textContent);
    let breakSeconds = timeToSeconds(totalBreakTimeEl.textContent);

    <?php if (!empty($log['break_start']) && empty($log['break_stop'])): ?>
      let breakStart = new Date("<?= $log['break_start'] ?>").getTime() / 1000;
      let nowSec = Math.floor(Date.now() / 1000);
      breakSeconds += (nowSec - breakStart);
    <?php endif; ?>

    <?php if (empty($log['check_out'])): ?>
      setInterval(() => {
        netWorkSeconds++;
        netWorkTimeEl.textContent = secondsToTime(netWorkSeconds);

        <?php if (!empty($log['break_start']) && empty($log['break_stop'])): ?>
          breakSeconds++;
          totalBreakTimeEl.textContent = secondsToTime(breakSeconds);
        <?php endif; ?>
      }, 1000);
    <?php endif; ?>
  </script>

</body>
</html>
