<?php
require '_auth_check.php'; // Ensure admin is logged in
require '../config.php';    // PDO connection

$message = '';

// Get category ID
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch category
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
$stmt->execute([':id' => $id]);
$category = $stmt->fetch();

if (!$category) {
    die("<div class='text-red-600 font-semibold text-center mt-10'>Category not found. <a href='categories.php' class='text-orange-600 underline'>Go back</a></div>");
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if ($name !== '') {
        $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
        try {
            $stmt->execute([':name' => $name, ':id' => $id]);
            $message = "<div class='text-green-600 mb-4 font-semibold'>Category updated successfully!</div>";
            // Refresh category data
            $stmt = $pdo->prepare("SELECT * FROM categories WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $category = $stmt->fetch();
        } catch (Exception $e) {
            $message = "<div class='text-red-600 mb-4 font-semibold'>Error: " . $e->getMessage() . "</div>";
        }
    } else {
        $message = "<div class='text-red-600 mb-4 font-semibold'>Category name cannot be empty!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Category</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-6">

<div class="max-w-3xl mx-auto bg-white p-8 rounded-3xl shadow-xl">

    <!-- Back button -->
    <a href="categories.php" class="inline-flex items-center mb-6 text-orange-600 font-semibold hover:text-orange-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
        Back to Categories
    </a>

    <h1 class="text-3xl font-bold mb-6 text-orange-600">Edit Category</h1>

    <?= $message ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block font-semibold mb-2">Category Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($category['name']) ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-orange-300"/>
        </div>
        <button type="submit" class="w-full py-3 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition text-lg">Update Category</button>
    </form>

</div>

</body>
</html>
