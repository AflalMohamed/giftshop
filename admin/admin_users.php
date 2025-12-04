<?php
require '_auth_check.php'; // ensure admin is logged in
require '../config.php';

$users = $pdo->query("SELECT id, username, created_at FROM admins ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Admin Users</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen">

<!-- Header -->
<header class="bg-white shadow p-4 flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-orange-600">Admin Users</h1>
    <a href="dashboard.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">Dashboard</a>
</header>

<main class="max-w-5xl mx-auto p-4">
    <div class="overflow-x-auto bg-white rounded-2xl shadow-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-orange-100">
                <tr>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">ID</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Username</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-gray-700">Created At</th>
                    <th class="px-4 py-3 text-center text-sm font-medium text-gray-700">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if($users): ?>
                    <?php foreach($users as $u): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3"><?= $u['id'] ?></td>
                            <td class="px-4 py-3"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="px-4 py-3"><?= $u['created_at'] ?></td>
                            <td class="px-4 py-3 flex justify-center gap-2">
                                <a href="edit_admin.php?id=<?= $u['id'] ?>" class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">Edit</a>
                                <a href="delete_admin.php?id=<?= $u['id'] ?>" onclick="return confirm('Are you sure you want to delete this admin?')" class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">No admin users found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>
