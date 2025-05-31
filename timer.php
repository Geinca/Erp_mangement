<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
require 'db.php';

// Set the default timezone to match your location
date_default_timezone_set('Asia/Kolkata'); // Change to your timezone

$username = $_SESSION['username'];
$today = date('Y-m-d');

$stmt = $pdo->prepare("SELECT * FROM attendance_logs WHERE username = ? AND log_date = ?");
$stmt->execute([$username, $today]);
$log = $stmt->fetch();

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

function formatDateTime($datetime) {
    if (empty($datetime)) return '-';
    $dt = new DateTime($datetime);
    return $dt->format('H:i:s');
}

$netWorkTime = "00:00:00";
$totalBreakTime = "00:00:00";
$checkInTimestamp = 0;
$checkOutTimestamp = 0;
$breakStartTimestamp = 0;
$currentTime = date('H:i:s'); // Get current server time

if ($log && $log['check_in']) {
    $checkIn = new DateTime($log['check_in']);
    $now = new DateTime();
    $checkInTimestamp = $checkIn->getTimestamp();
    
    // Format times for display
    $checkInDisplay = formatDateTime($log['check_in']);
    $checkOutDisplay = formatDateTime($log['check_out'] ?? '');
    $breakStartDisplay = formatDateTime($log['break_start'] ?? '');
    $breakStopDisplay = formatDateTime($log['break_stop'] ?? '');
    
    // Calculate total work time
    $workEnd = isset($log['check_out']) ? new DateTime($log['check_out']) : $now;
    $workSeconds = $workEnd->getTimestamp() - $checkInTimestamp;
    
    // Calculate total break time
    $breakSeconds = 0;
    if (!empty($log['total_break_time'])) {
        $breakSeconds = timeToSeconds($log['total_break_time']);
        $totalBreakTime = $log['total_break_time'];
    }
    
    // Add current break time if in break
    if (!empty($log['break_start']) && empty($log['break_stop'])) {
        $breakStart = new DateTime($log['break_start']);
        $breakStartTimestamp = $breakStart->getTimestamp();
        $breakSeconds += $now->getTimestamp() - $breakStartTimestamp;
        $totalBreakTime = secondsToTime($breakSeconds);
    }
    
    $netSeconds = max(0, $workSeconds - $breakSeconds);
    $netWorkTime = secondsToTime($netSeconds);
    
    if (isset($log['check_out'])) {
        $checkOutTimestamp = (new DateTime($log['check_out']))->getTimestamp();
    }
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
  <style>
    .sidebar-link:hover {
      background-color: rgba(79, 70, 229, 0.1);
    }
    .sidebar-link.active {
      background-color: rgba(79, 70, 229, 0.2);
      border-left: 4px solid #4f46e5;
    }
    .current-time {
      font-size: 1.2rem;
      font-weight: bold;
      color: #4f46e5;
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen flex">
  <!-- Sidebar -->
  <div id="sidebar-container">
    <?php include 'sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div class="flex-1 flex flex-col overflow-hidden">
    <!-- Mobile header -->
    <header class="bg-white shadow-sm p-4 md:hidden">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-indigo-800">Work Timer</h1>
        <div class="current-time" id="currentTime"></div>
        <button id="mobile-menu-button" class="text-gray-500">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </header>

    <!-- Desktop header -->
    <header class="bg-white shadow-sm p-4 hidden md:block">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold text-indigo-800">Work Timer</h1>
        <div class="current-time" id="currentTimeDesktop"><?= $currentTime ?></div>
      </div>
    </header>

    <!-- Mobile sidebar (hidden by default) -->
    <div id="mobile-sidebar" class="bg-white shadow-lg w-full hidden md:hidden">
      <nav class="p-2">
        <a href="dashboard.php" class="block sidebar-link px-4 py-3 text-gray-700 rounded-lg mb-1">
          <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
        </a>
        <a href="timer.php" class="block sidebar-link px-4 py-3 text-gray-700 rounded-lg mb-1 active">
          <i class="fas fa-clock mr-3"></i> Work Timer
        </a>
        <a href="attendance.php" class="block sidebar-link px-4 py-3 text-gray-700 rounded-lg mb-1">
          <i class="fas fa-calendar-check mr-3"></i> Attendance
        </a>
        <a href="profile.php" class="block sidebar-link px-4 py-3 text-gray-700 rounded-lg mb-1">
          <i class="fas fa-user mr-3"></i> Profile
        </a>
        <a href="logout.php" class="block sidebar-link px-4 py-3 text-red-600 rounded-lg mb-1">
          <i class="fas fa-sign-out-alt mr-3"></i> Logout
        </a>
      </nav>
    </div>

    <!-- Page content -->
    <main class="flex-1 overflow-y-auto p-6 bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50">
      <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-8">
          <h1 class="text-3xl font-extrabold text-indigo-800 drop-shadow-md">⏱️ Work Timer</h1>
          <div class="current-time" id="currentTimeMain"><?= $currentTime ?></div>
        </div>

        <?php if (!$log || !$log['check_in']): ?>
          <div class="bg-white p-8 rounded-xl shadow-lg w-full text-center">
            <p class="text-lg text-gray-700 mb-4">You have not checked in today.</p>
            <a href="dashboard.php" class="inline-block mt-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
              Back to Dashboard
            </a>
          </div>
        <?php else: ?>
          <div class="bg-white p-8 rounded-xl shadow-lg w-full">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-gray-800">
              <div>
                <p><span class="font-semibold text-indigo-600">Check In:</span> <?= $checkInDisplay ?></p>
                <p><span class="font-semibold text-indigo-600">Check Out:</span> <?= $checkOutDisplay ?></p>
              </div>
              <div>
                <p><span class="font-semibold text-indigo-600">Break Start:</span> <?= $breakStartDisplay ?></p>
                <p><span class="font-semibold text-indigo-600">Break Stop:</span> <?= $breakStopDisplay ?></p>
              </div>
            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="bg-indigo-50 rounded-lg p-6 shadow-inner text-center">
                <p class="text-xl font-semibold text-indigo-700 mb-1">Net Work Time</p>
                <p id="netWorkTime" class="text-5xl font-bold text-indigo-900 tracking-widest"><?= $netWorkTime ?></p>
              </div>

              <div class="bg-yellow-50 rounded-lg p-6 shadow-inner text-center">
                <p class="text-xl font-semibold text-yellow-700 mb-1">Total Break Time</p>
                <p id="totalBreakTime" class="text-5xl font-bold text-yellow-900 tracking-widest"><?= $totalBreakTime ?></p>
              </div>
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
                <div class="w-full text-center">
                  <p class="text-green-700 font-bold mt-6 text-lg">✅ You have checked out. Great job!</p>
                  <a href="dashboard.php" class="inline-block mt-4 text-indigo-700 font-semibold underline hover:text-indigo-900 transition">
                    ← Back to Dashboard
                  </a>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>

  <script>
  // Time formatting functions
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

  // Current time updater
  function updateCurrentTime() {
    const options = {
      timeZone: 'Asia/Kolkata',
      hour12: false,
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    };
    const timeString = new Date().toLocaleTimeString('en-US', options);
    
    document.querySelectorAll('.current-time').forEach(el => {
      el.textContent = timeString;
    });
  }

  // Mobile menu toggle
  document.getElementById('mobile-menu-button').addEventListener('click', function() {
    const sidebar = document.getElementById('mobile-sidebar');
    sidebar.classList.toggle('hidden');
  });

  // Initialize current time and update every second
  updateCurrentTime();
  setInterval(updateCurrentTime, 1000);

  // Timer functionality
  document.addEventListener('DOMContentLoaded', function() {
    const netWorkTimeEl = document.getElementById('netWorkTime');
    const totalBreakTimeEl = document.getElementById('totalBreakTime');

    if (netWorkTimeEl && totalBreakTimeEl) {
      // Get initial timestamps from PHP
      const checkInTimestamp = <?= $checkInTimestamp ?>;
      const breakStartTimestamp = <?= $breakStartTimestamp ?>;
      const checkOutTimestamp = <?= $checkOutTimestamp ?>;
      const initialBreakSeconds = timeToSeconds("<?= $totalBreakTime ?>");
      
      <?php if (empty($log['check_out']) && !empty($log['check_in'])): ?>
        // Update timer every second
        setInterval(() => {
          const now = Math.floor(Date.now() / 1000);
          const totalWorkSeconds = now - checkInTimestamp;
          
          <?php if (!empty($log['break_start']) && empty($log['break_stop'])): ?>
            const currentBreakSeconds = (now - breakStartTimestamp) + initialBreakSeconds;
            const netSeconds = Math.max(0, totalWorkSeconds - currentBreakSeconds);
            
            // Update both displays
            netWorkTimeEl.textContent = secondsToTime(netSeconds);
            totalBreakTimeEl.textContent = secondsToTime(currentBreakSeconds);
          <?php else: ?>
            // Only update work time (not in break)
            const netSeconds = Math.max(0, totalWorkSeconds - initialBreakSeconds);
            netWorkTimeEl.textContent = secondsToTime(netSeconds);
          <?php endif; ?>
        }, 1000);
      <?php endif; ?>
    }
  });
</script>
</body>
</html>