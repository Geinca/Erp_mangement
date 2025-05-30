<?php
session_start();
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit();
}
$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Leave Calendar</title>
  <script src="https://cdn.tailwindcss.com"></script>  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen flex">

  <!-- Sidebar -->
  <aside class="hidden md:block w-64 bg-white shadow-md border-r">
    <?php include 'sidebar.php'; ?>
  </aside>

  <!-- Main content -->
  <main class="flex-1 flex flex-col p-6">

    <!-- Topbar / Header -->
    <header class="mb-6">
      <h2 class="text-2xl font-semibold flex items-center gap-2">📅 Leave Calendar</h2>
    </header>

    <!-- Month Selector -->
    <section class="mb-6">
      <label for="monthSelect" class="block font-semibold mb-1">Select Month:</label>
      <input type="month" id="monthSelect" class="border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500" />
    </section>

    <!-- Calendar Container -->
    <section id="calendarContainer" class="grid grid-cols-7 gap-2 mb-6">
      <!-- Calendar dynamically inserted here -->
    </section>

    <!-- Info Section -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">

      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">📌 Features</h3>
        <ul class="list-disc list-inside space-y-1 text-sm">
          <li><strong>Select Month:</strong> View and manage leave days.</li>
          <li><strong>Mark Leave Days:</strong> Click to mark full-day leave. Right-click for half-day (coming soon).</li>
          <li><strong>Public Holidays:</strong> Will auto-highlight in green (coming soon).</li>
          <li><strong>Tooltips:</strong> Hover to view leave type (coming soon).</li>
        </ul>
      </div>

      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">📅 Legend</h3>
        <ul class="space-y-2 text-sm">
          <li class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-sm bg-red-500 inline-block"></span> Leave Day
          </li>
          <li class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-sm bg-blue-500 inline-block"></span> Weekend
          </li>
          <li class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-sm bg-green-500 inline-block"></span> Public Holiday
          </li>
          <li class="flex items-center gap-2">
            <span class="w-5 h-5 rounded-sm bg-orange-500 inline-block"></span> Half-Day
          </li>
        </ul>
      </div>

      <div class="bg-white p-4 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-3 flex items-center gap-2">📝 Notes</h3>
        <ul class="list-disc list-inside space-y-1 text-sm">
          <li>This calendar is for personal tracking unless shared with your manager.</li>
          <li>Always submit official leave through the HR portal.</li>
        </ul>
      </div>

    </section>
  </main>

  <script src="/js/calender.js"></script>
</body>
</html>
