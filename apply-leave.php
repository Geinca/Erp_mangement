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
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply Leave</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen flex">

  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-lg hidden md:block">
    <?php include 'sidebar.php'; ?>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-6 md:p-10">
    <!-- Header -->
    <header class="flex justify-between items-center mb-8">
      <h2 class="text-3xl font-bold text-indigo-600">📝 Apply for Leave</h2>
      <a href="logout.php" class="text-red-600 font-semibold hover:text-red-800">Logout</a>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Apply Leave Form -->
      <section class="bg-white p-6 rounded-xl shadow-md">
        <form id="leaveForm" class="space-y-6">
          <div>
            <label for="leaveType" class="block mb-2 font-semibold">Leave Type</label>
            <select id="leaveType" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-indigo-300" required>
              <option value="">Select Leave Type</option>
              <option value="Sick Leave">Sick Leave</option>
              <option value="Casual Leave">Casual Leave</option>
              <option value="Earned Leave">Earned Leave</option>
              <option value="Work from Home">Work from Home</option>
            </select>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="fromDate" class="block mb-2 font-semibold">From Date</label>
              <input type="date" id="fromDate" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
              <label for="toDate" class="block mb-2 font-semibold">To Date</label>
              <input type="date" id="toDate" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
          </div>

          <div>
            <label for="reason" class="block mb-2 font-semibold">Reason</label>
            <textarea id="reason" rows="4" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Explain briefly..." required></textarea>
          </div>

          <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow flex items-center gap-2">
            <i class="fas fa-paper-plane"></i> Submit Leave Request
          </button>
          <p class="text-green-600 text-sm mt-2 hidden" id="successMessage">✅ Leave request submitted successfully!</p>
        </form>
      </section>

      <!-- Leave Table -->
      <section class="bg-white p-6 rounded-xl shadow-md">
        <h3 class="text-xl font-semibold mb-4">📋 Applied Leaves</h3>
        <div class="overflow-auto max-h-[400px]">
          <table class="min-w-full table-auto border border-gray-200 text-sm">
            <thead class="bg-gray-100 text-gray-700">
              <tr>
                <th class="px-4 py-2 border">Leave Type</th>
                <th class="px-4 py-2 border">From</th>
                <th class="px-4 py-2 border">To</th>
                <th class="px-4 py-2 border">Reason</th>
                <th class="px-4 py-2 border">Status</th>
                <th class="px-4 py-2 border">Actions</th>
              </tr>
            </thead>
            <tbody id="leaveTableBody" class="text-gray-800">
              <!-- JS will populate rows -->
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <script>
    // Sidebar auto-load if needed (for JS-only sidebar loading)
    // fetch('sidebar.html').then(res => res.text()).then(html => document.getElementById('sidebar-container').innerHTML = html);

    // Handle Leave Form Submission
    const leaveForm = document.getElementById('leaveForm');
    const leaveTableBody = document.getElementById('leaveTableBody');
    const successMessage = document.getElementById('successMessage');

    const getLeaves = () => {
      const leaves = JSON.parse(localStorage.getItem('leaves') || '[]');
      leaveTableBody.innerHTML = '';
      leaves.forEach((leave, index) => {
        leaveTableBody.innerHTML += `
          <tr>
            <td class="px-4 py-2 border">${leave.type}</td>
            <td class="px-4 py-2 border">${leave.from}</td>
            <td class="px-4 py-2 border">${leave.to}</td>
            <td class="px-4 py-2 border">${leave.reason}</td>
            <td class="px-4 py-2 border text-yellow-500 font-semibold">Pending</td>
            <td class="px-4 py-2 border">
              <button onclick="deleteLeave(${index})" class="text-red-500 hover:underline">Delete</button>
            </td>
          </tr>
        `;
      });
    };

    const deleteLeave = (index) => {
      const leaves = JSON.parse(localStorage.getItem('leaves') || '[]');
      leaves.splice(index, 1);
      localStorage.setItem('leaves', JSON.stringify(leaves));
      getLeaves();
    };

    leaveForm.addEventListener('submit', (e) => {
      e.preventDefault();
      const newLeave = {
        type: document.getElementById('leaveType').value,
        from: document.getElementById('fromDate').value,
        to: document.getElementById('toDate').value,
        reason: document.getElementById('reason').value,
      };
      const leaves = JSON.parse(localStorage.getItem('leaves') || '[]');
      leaves.push(newLeave);
      localStorage.setItem('leaves', JSON.stringify(leaves));
      leaveForm.reset();
      successMessage.classList.remove('hidden');
      getLeaves();
      setTimeout(() => successMessage.classList.add('hidden'), 3000);
    });

    window.onload = getLeaves;
  </script>
</body>
</html>
