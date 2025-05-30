<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
require 'db.php';
$username = $_SESSION['username'];

$stmt = $pdo->prepare("SELECT leave_balance, next_payroll FROM users WHERE username = ?");
$stmt->execute([$username]);
$userData = $stmt->fetch();

$leaveBalance = $userData['leave_balance'] ?? 0;
$nextPayroll = $userData['next_payroll'] ?? 'Not Available';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Dashboard</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
<body class="min-h-screen bg-gray-100 text-gray-800 flex">
  
  <!-- Sidebar -->
  <aside class="w-64 hidden md:block bg-white border-r">
    <?php include 'sidebar.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6">
    <!-- Header -->
    <header class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-2xl font-bold">Welcome, <?= htmlspecialchars($username) ?></h1>
        <p class="text-gray-600 text-sm mt-1">
          ⏰ Current Time: <span id="currentTime">--:--:--</span>
        </p>
      </div>
      <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded">Logout</a>
    </header>

    <!-- Info Cards -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      
      <!-- Attendance Card -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">🕒 Attendance</h2>
        <form action="checkin.php" method="post">
          <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded">
            Check In
          </button>
        </form>
      </div>

      <!-- Leave Balance -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">🏖️ Leave Balance</h2>
        <p class="text-3xl text-indigo-600"><?= $leaveBalance ?> Days</p>
      </div>

      <!-- Next Payroll -->
      <div class="bg-white p-6 rounded shadow">
        <h2 class="text-xl font-semibold mb-2">💰 Next Payroll</h2>
        <p><?= htmlspecialchars($nextPayroll) ?></p>
      </div>

    </section>
  </main>

  <!-- Live Time Script -->
  <script>
    function updateTime() {
      const now = new Date();
      document.getElementById('currentTime').textContent = now.toLocaleTimeString();
    }
    setInterval(updateTime, 1000);
    updateTime();
  </script>

</body>
</html>
