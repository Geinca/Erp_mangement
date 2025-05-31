<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'db.php'; // Make sure this file contains your database connection

$username = $_SESSION['username'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leaveType'])) {
    $leaveType = $_POST['leaveType'];
    $fromDate = $_POST['fromDate'];
    $toDate = $_POST['toDate'];
    $reason = $_POST['reason'];
    
    try {
        // Get user ID
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("User not found!");
        }

        // Get leave type ID
        $stmt = $pdo->prepare("SELECT type_id FROM leave_types WHERE type_name = ?");
        $stmt->execute([$leaveType]);
        $type = $stmt->fetch();
        
        if (!$type) {
            throw new Exception("Leave type not found!");
        }

        // Insert leave application
        $stmt = $pdo->prepare("INSERT INTO leave_applications 
                              (user_id, type_id, start_date, end_date, reason, status) 
                              VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->execute([
            $user['user_id'], 
            $type['type_id'], 
            $fromDate, 
            $toDate, 
            $reason
        ]);
        
        $success = "Leave request submitted successfully!";
        
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get user's leave applications
try {
    $stmt = $pdo->prepare("SELECT la.*, lt.type_name 
                         FROM leave_applications la
                         JOIN leave_types lt ON la.type_id = lt.type_id
                         JOIN users u ON la.user_id = u.user_id
                         WHERE u.username = ?
                         ORDER BY la.start_date DESC");
    $stmt->execute([$username]);
    $leaves = $stmt->fetchAll();
} catch (PDOException $e) {
    $leaves = [];
    $error = "Error fetching leave applications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Apply Leave</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    <!-- Display messages -->
    <?php if ($error): ?>
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
      <p><?php echo htmlspecialchars($error); ?></p>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
      <p><?php echo htmlspecialchars($success); ?></p>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
      <!-- Apply Leave Form -->
      <section class="bg-white p-6 rounded-xl shadow-md">
        <form id="leaveForm" method="POST" class="space-y-6">
          <div>
            <label for="leaveType" class="block mb-2 font-semibold">Leave Type</label>
            <select id="leaveType" name="leaveType" class="w-full p-2 border border-gray-300 rounded-md focus:ring focus:ring-indigo-300" required>
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
              <input type="date" id="fromDate" name="fromDate" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
            <div>
              <label for="toDate" class="block mb-2 font-semibold">To Date</label>
              <input type="date" id="toDate" name="toDate" class="w-full p-2 border border-gray-300 rounded-md" required>
            </div>
          </div>

          <div>
            <label for="reason" class="block mb-2 font-semibold">Reason</label>
            <textarea id="reason" name="reason" rows="4" class="w-full p-2 border border-gray-300 rounded-md" placeholder="Explain briefly..." required></textarea>
          </div>

          <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md shadow flex items-center gap-2">
            <i class="fas fa-paper-plane"></i> Submit Leave Request
          </button>
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
            <tbody class="text-gray-800">
              <?php foreach ($leaves as $leave): ?>
                <?php
                  $statusClass = [
                    'pending' => 'text-yellow-500',
                    'approved' => 'text-green-500',
                    'rejected' => 'text-red-500'
                  ][$leave['status']] ?? 'text-gray-500';
                ?>
                <tr>
                  <td class="px-4 py-2 border"><?php echo htmlspecialchars($leave['type_name']); ?></td>
                  <td class="px-4 py-2 border"><?php echo date('M j, Y', strtotime($leave['start_date'])); ?></td>
                  <td class="px-4 py-2 border"><?php echo date('M j, Y', strtotime($leave['end_date'])); ?></td>
                  <td class="px-4 py-2 border"><?php echo htmlspecialchars($leave['reason']); ?></td>
                  <td class="px-4 py-2 border font-semibold <?php echo $statusClass; ?>">
                    <?php echo ucfirst($leave['status']); ?>
                  </td>
                  <td class="px-4 py-2 border">
                    <?php if ($leave['status'] === 'pending'): ?>
                      <button onclick="confirmDelete(<?php echo $leave['application_id']; ?>)" 
                        class="text-red-500 hover:underline">Delete</button>
                    <?php else: ?>
                      <span class="text-gray-400">No action</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (empty($leaves)): ?>
                <tr>
                  <td colspan="6" class="px-4 py-4 text-center text-gray-500">
                    No leave applications found
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </main>

  <script>
    // Set minimum dates for date inputs
    document.addEventListener('DOMContentLoaded', function() {
      const today = new Date().toISOString().split('T')[0];
      document.getElementById('fromDate').min = today;
      document.getElementById('toDate').min = today;
      
      // Update toDate min when fromDate changes
      document.getElementById('fromDate').addEventListener('change', function() {
        document.getElementById('toDate').min = this.value;
      });
    });

    function confirmDelete(applicationId) {
      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          // Submit delete request
          const form = document.createElement('form');
          form.method = 'post';
          form.action = 'delete_leave.php';
          
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'application_id';
          input.value = applicationId;
          
          form.appendChild(input);
          document.body.appendChild(form);
          form.submit();
        }
      });
    }
  </script>
</body>
</html>