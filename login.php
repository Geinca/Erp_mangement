<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = $_POST['username'];
  $password = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    header("Location: dashboard.php");
    exit();
  } else {
    $error = "Invalid credentials!";
  }
}
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    }
  </script>
</head>
<body class="bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 dark:from-gray-900 dark:to-gray-800 flex items-center justify-center min-h-screen transition-colors duration-300">

  <div class="relative w-full max-w-md px-8 py-10 bg-white/30 dark:bg-gray-800/40 backdrop-blur-md rounded-2xl shadow-2xl ring-1 ring-white/20 transition-all duration-300">
    
    <div class="flex justify-center mb-4">
      <img src="https://th.bing.com/th/id/OIP.LJ60Sn3f_JUhVAJf-w1O8AAAAA?rs=1&pid=ImgDetMain" alt="Logo" class="h-20 w-20 rounded-full shadow-lg" />
    </div>

    <h2 class="text-3xl font-extrabold text-center text-white dark:text-white mb-6 font-sans drop-shadow-md">Welcome to ERP</h2>

    <!-- Registration Success Message -->
    <?php if (isset($_GET['registered']) && $_GET['registered'] == 1): ?>
      <p class="text-green-400 text-center mb-4">Registration successful! Please login below.</p>
    <?php endif; ?>

    <!-- Error Message -->
    <?php if (!empty($error)): ?>
      <p class="text-red-200 text-sm text-center mb-4 animate-pulse"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" class="space-y-5">
      <div>
        <label class="block text-sm text-white/90 dark:text-gray-300">Username</label>
        <input type="text" name="username" required
               class="w-full mt-1 px-4 py-2 rounded-lg bg-white/60 dark:bg-gray-700 border-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-600 text-gray-900 dark:text-white placeholder-gray-600 dark:placeholder-gray-300" />
      </div>

      <div>
        <label class="block text-sm text-white/90 dark:text-gray-300">Password</label>
        <input type="password" name="password" required
               class="w-full mt-1 px-4 py-2 rounded-lg bg-white/60 dark:bg-gray-700 border-none focus:ring-2 focus:ring-indigo-400 dark:focus:ring-indigo-600 text-gray-900 dark:text-white placeholder-gray-600 dark:placeholder-gray-300" />
      </div>

      <div class="flex justify-between text-sm text-white/90 dark:text-gray-300">
        <a href="#" class="hover:underline">Forgot Password?</a>
      </div>

      <button type="submit"
              class="w-full py-2 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold shadow-md transition-transform transform hover:scale-105">
        Login
      </button>
    </form>

    <p class="mt-6 text-center text-white/90 dark:text-gray-300 text-sm">
      Don't have an account? <a href="register.php" class="underline hover:text-white">Register here</a>
    </p>

    <div class="text-center mt-6">
      <button onclick="document.documentElement.classList.toggle('dark')" class="text-sm text-white/80 underline hover:text-white transition">
        Toggle Dark Mode
      </button>
    </div>

  </div>

</body>
</html>
