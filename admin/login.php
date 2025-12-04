<?php
// Start session safely at the very top
session_start();

// Include database configuration
require __DIR__ . '/../config.php';

// Initialize message
$msg = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Fetch admin details
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Verify hashed password
        if (password_verify($password, $admin['password'])) {
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            // Redirect to dashboard
            header("Location: ./dashboard.php");
            exit;
        } else {
            $msg = "Incorrect password!";
        }
    } else {
        $msg = "User not found!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login | Gifts Dashboard</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
/* Optional: background animation */
body {
    background: linear-gradient(135deg, #FFEDD5, #FEE2E2);
}
</style>
</head>
<body class="min-h-screen flex items-center justify-center">

<div class="w-full max-w-md p-6 bg-white rounded-3xl shadow-2xl border border-gray-200">
    
    <div class="text-center mb-6">
        <img src="../uploads/logo.png" alt="Logo" class="mx-auto w-24 h-24 object-contain mb-3">
        <h1 class="text-3xl font-bold text-gray-800">Admin Login</h1>
        <p class="text-gray-500 text-sm mt-1">Manage your gifts & orders</p>
    </div>

    <?php if($msg): ?>
    <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-center font-medium">
        <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
        <div>
            <label class="block text-gray-700 font-medium mb-1">Username</label>
            <input 
                type="text" 
                name="username" 
                required 
                placeholder="Enter your username"
                class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 focus:outline-none"
            >
        </div>

        <div>
            <label class="block text-gray-700 font-medium mb-1">Password</label>
            <input 
                type="password" 
                name="password" 
                required 
                placeholder="Enter your password"
                class="w-full p-3 border rounded-xl focus:ring-2 focus:ring-orange-400 focus:outline-none"
            >
        </div>

        <button type="submit" class="w-full py-3 bg-orange-600 text-white font-bold rounded-xl hover:bg-orange-700 transition shadow-lg">
            Login
        </button>
    </form>

    <div class="text-center mt-5 text-gray-500 text-sm">
        &copy; <?= date('Y') ?> Gifts Dashboard. All rights reserved.
    </div>

</div>

</body>
</html>
