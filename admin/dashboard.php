<?php
require '_auth_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome Free -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-Q0Fv/QxVE2u+..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
/* Sidebar slide animation */
.sidebar { transition: transform 0.3s ease-in-out; }
</style>
</head>
<body class="bg-gray-100 font-sans flex min-h-screen">

<!-- Mobile Top Bar -->
<div class="lg:hidden fixed top-0 left-0 w-full bg-white shadow-md p-4 flex justify-between items-center z-20">
    <h2 class="text-xl font-bold text-orange-600">Admin Panel</h2>
    <button id="menuBtn" class="text-gray-700 focus:outline-none">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar w-64 bg-white shadow-md min-h-screen fixed lg:static top-0 left-0 transform -translate-x-full lg:translate-x-0 z-30">
    <!-- Logo -->
    <div class="p-6 flex flex-col items-center border-b mt-14 lg:mt-0">
        <img src="../uploads/logo.png" alt="Logo" class="h-16 w-16 object-contain mb-2">
        <h2 class="text-2xl font-bold text-orange-600 mb-1">Admin Panel</h2>
        <span class="text-gray-500 text-sm">Welcome, <?= htmlspecialchars($adminUsername ?? 'Admin') ?></span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 mt-6">
        <ul>
            <li><a href="dashboard.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition">
                <i class="fas fa-tachometer-alt w-5"></i> Dashboard
            </a></li>
            <li><a href="products.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition mt-2">
                <i class="fas fa-box w-5"></i> Products
            </a></li>
            <li><a href="categories.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-green-50 hover:text-green-600 rounded-lg transition mt-2">
                <i class="fas fa-list w-5"></i> Categories
            </a></li>
            <li><a href="embroidery.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-pink-50 hover:text-pink-600 rounded-lg transition mt-2">
                <i class="fas fa-seedling w-5"></i> Embroidery
            </a></li>
            <li><a href="admin_users.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition mt-2">
                <i class="fas fa-users w-5"></i> Admin Users
            </a></li>
            <li><a href="settings.php" class="flex items-center gap-3 py-3 px-6 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition mt-2">
                <i class="fas fa-cog w-5"></i> Site Settings
            </a></li>
        </ul>
    </nav>

    <!-- Logout -->
    <div class="p-6 border-t mt-auto">
        <a href="logout.php" class="block w-full text-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 font-semibold transition">Logout</a>
    </div>
</aside>

<!-- Overlay for Mobile -->
<div id="overlay" class="hidden fixed inset-0 bg-black bg-opacity-40 z-20 lg:hidden"></div>

<!-- Main Content -->
<main class="flex-1 p-8 mt-20 lg:mt-0">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Dashboard</h1>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <a href="products.php" class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition flex flex-col items-center justify-center text-center">
            <i class="fas fa-box text-4xl text-orange-500 mb-4"></i>
            <span class="font-semibold text-gray-800 text-lg">Products</span>
        </a>
        <a href="categories.php" class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition flex flex-col items-center justify-center text-center">
            <i class="fas fa-list text-4xl text-green-500 mb-4"></i>
            <span class="font-semibold text-gray-800 text-lg">Categories</span>
        </a>
        <a href="embroidery.php" class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition flex flex-col items-center justify-center text-center">
            <i class="fas fa-seedling text-4xl text-pink-500 mb-4"></i>
            <span class="font-semibold text-gray-800 text-lg">Embroidery</span>
        </a>
        <a href="admin_users.php" class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition flex flex-col items-center justify-center text-center">
            <i class="fas fa-users text-4xl text-purple-500 mb-4"></i>
            <span class="font-semibold text-gray-800 text-lg">Admin Users</span>
        </a>
        <a href="settings.php" class="bg-white p-6 rounded-3xl shadow hover:shadow-xl transition flex flex-col items-center justify-center text-center">
            <i class="fas fa-cog text-4xl text-blue-500 mb-4"></i>
            <span class="font-semibold text-gray-800 text-lg">Settings</span>
        </a>
    </div>

    <footer class="mt-12 text-center text-gray-500 text-sm">
        &copy; <?= date('Y') ?> Admin Dashboard. All rights reserved.
    </footer>
</main>

<!-- Sidebar Toggle Script -->
<script>
const menuBtn = document.getElementById("menuBtn");
const sidebar = document.getElementById("sidebar");
const overlay = document.getElementById("overlay");

menuBtn.addEventListener("click", () => {
    sidebar.classList.toggle("-translate-x-full");
    overlay.classList.toggle("hidden");
});

overlay.addEventListener("click", () => {
    sidebar.classList.add("-translate-x-full");
    overlay.classList.add("hidden");
});
</script>

</body>
</html>
