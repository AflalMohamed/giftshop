<?php
require '_auth_check.php';
require '../config.php';

$message = "";

// -----------------------------
// Generate Slug
// -----------------------------
function generateSlug($name) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
}

// -----------------------------
// ADD CATEGORY
// -----------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {

    $name = trim($_POST['name']);

    if ($name === "") {
        $message = "<div class='text-red-600 mb-4 font-semibold'>Category name cannot be empty!</div>";
    } else {

        // Check duplicate NAME (case-insensitive)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE LOWER(name) = LOWER(:name)");
        $stmt->execute([':name' => $name]);
        $exists = $stmt->fetchColumn();

        if ($exists > 0) {
            $message = "<div class='text-red-600 mb-4 font-semibold'>This category already exists!</div>";
        } else {

            // Generate slug
            $slug = generateSlug($name);

            // Check duplicate slug
            $stmtSlug = $pdo->prepare("SELECT COUNT(*) FROM categories WHERE slug = :slug");
            $stmtSlug->execute([':slug' => $slug]);
            $slugExists = $stmtSlug->fetchColumn();

            if ($slugExists > 0) {
                $slug .= '-' . time();
            }

            // Insert category
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO categories (name, slug) VALUES (:name, :slug)");
                $stmtInsert->execute([':name' => $name, ':slug' => $slug]);

                $message = "<div class='text-green-600 mb-4 font-semibold'>Category added successfully!</div>";

            } catch (Exception $e) {
                $message = "<div class='text-red-600 mb-4 font-semibold'>Error: {$e->getMessage()}</div>";
            }
        }
    }
}

// -----------------------------
// DELETE CATEGORY
// -----------------------------
// This will run ONLY when NOT adding a category
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['delete'])) {

    $id = (int)$_GET['delete'];

    try {
        $stmtDel = $pdo->prepare("DELETE FROM categories WHERE id = :id");
        $stmtDel->execute([':id' => $id]);

        $message = "<div class='text-green-600 mb-4 font-semibold'>Category deleted successfully!</div>";

    } catch (Exception $e) {
        $message = "<div class='text-red-600 mb-4 font-semibold'>Error: {$e->getMessage()}</div>";
    }
}

// -----------------------------
// Fetch all categories
// -----------------------------
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Categories</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen py-6">

<div class="max-w-5xl mx-auto bg-white p-8 rounded-3xl shadow-xl">

    <!-- Back -->
    <a href="dashboard.php" class="inline-flex items-center mb-6 text-orange-600 font-semibold hover:text-orange-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
        Back to Dashboard
    </a>

    <h1 class="text-3xl font-bold mb-6 text-orange-600">Manage Categories</h1>

    <!-- Message -->
    <?= $message ?>

    <!-- Add Category Form -->
    <form method="post" class="mb-6 flex gap-3">
        <input 
            type="text" 
            name="name" 
            placeholder="New Category Name"
            class="flex-1 p-3 border rounded-lg focus:ring focus:ring-orange-300"
        />

        <button 
            type="submit" 
            class="px-6 py-3 bg-orange-600 text-white rounded-lg font-semibold hover:bg-orange-700 transition">
            Add
        </button>
    </form>

    <!-- Category Table -->
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-200">
            <thead class="bg-orange-100">
                <tr>
                    <th class="border px-4 py-2 text-left">ID</th>
                    <th class="border px-4 py-2 text-left">Name</th>
                    <th class="border px-4 py-2 text-left">Slug</th>
                    <th class="border px-4 py-2 text-left">Actions</th>
                </tr>
            </thead>
            <tbody>

                <?php if ($categories): ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="border px-4 py-2"><?= $cat['id'] ?></td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($cat['name']) ?></td>
                            <td class="border px-4 py-2"><?= htmlspecialchars($cat['slug']) ?></td>

                            <td class="border px-4 py-2 flex gap-2">
                                <a 
                                    href="edit_category.php?id=<?= $cat['id'] ?>" 
                                    class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
                                    Edit
                                </a>

                                <a 
                                    href="?delete=<?= $cat['id'] ?>" 
                                    onclick="return confirm('Are you sure?')" 
                                    class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                <?php else: ?>
                    <tr>
                        <td colspan="4" class="border px-4 py-2 text-center text-gray-500">
                            No categories found.
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>
    </div>

</div>

</body>
</html>
