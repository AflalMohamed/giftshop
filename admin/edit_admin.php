<?php
require '_auth_check.php';
require '../config.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: admin_users.php"); exit; }

// fetch user
$stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header("Location: admin_users.php"); exit; }

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '') $errors[] = "Username is required.";

    // check uniqueness
    $stmtCheck = $pdo->prepare("SELECT id FROM admins WHERE username = ? AND id != ? LIMIT 1");
    $stmtCheck->execute([$username, $id]);
    if ($stmtCheck->fetch()) $errors[] = "Username already exists.";

    if (empty($errors)) {
        if ($password !== '') {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmtU = $pdo->prepare("UPDATE admins SET username = ?, password = ? WHERE id = ?");
            $stmtU->execute([$username, $hashedPassword, $id]);
        } else {
            $stmtU = $pdo->prepare("UPDATE admins SET username = ? WHERE id = ?");
            $stmtU->execute([$username, $id]);
        }
        $success = "Admin updated successfully!";
        $user['username'] = $username;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-6">

<div class="max-w-2xl mx-auto bg-white p-6 rounded-2xl shadow-lg">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-orange-600">Edit Admin</h1>
        <a href="admin_users.php" class="text-gray-700 hover:text-gray-900">← Back</a>
    </div>

    <?php if(!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            <?php foreach($errors as $e) echo "<div>- " . htmlspecialchars($e) . "</div>"; ?>
        </div>
    <?php endif; ?>

    <?php if($success): ?>
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block text-sm font-medium">Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full p-2 border rounded" required>
        </div>

        <div>
            <label class="block text-sm font-medium">New Password (leave blank to keep current)</label>
            <input type="password" name="password" class="w-full p-2 border rounded">
        </div>

        <div class="flex gap-3">
            <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition">Save Changes</button>
            <a href="admin_users.php" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 transition">Cancel</a>
        </div>
    </form>

</div>

</body>
</html>
