<?php
require '_auth_check.php';
require '../config.php';

$errors = [];
$success = false;

// Get the design ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid design ID");
}

// Fetch existing design
$stmt = $pdo->prepare("SELECT * FROM embroidery_designs WHERE id=?");
$stmt->execute([$id]);
$design = $stmt->fetch();
if (!$design) {
    die("Design not found");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $short_desc = trim($_POST['short_desc']);
    $description = trim($_POST['description']);
    $price = floatval($_POST['price']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Validation
    if (!$name) $errors[] = "Name is required";
    if (!$short_desc) $errors[] = "Short description is required";
    if (!$description) $errors[] = "Description is required";
    if ($price <= 0) $errors[] = "Price must be greater than 0";

    // Handle image upload
    $image_path = $design['image'];
    if (!empty($_FILES['image']['name'])) {
        $uploadDir = '../uploads/embroidery/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $image_name;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['image']['type'], $allowedTypes)) {
            $errors[] = "Only JPG, PNG, GIF images are allowed";
        } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // Delete old image if exists
            if ($design['image'] && file_exists("../" . $design['image'])) {
                unlink("../" . $design['image']);
            }
            $image_path = 'uploads/embroidery/' . $image_name;
        } else {
            $errors[] = "Failed to upload image";
        }
    }

    // Update DB
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE embroidery_designs 
                               SET name=?, short_desc=?, description=?, image=?, price=?, is_active=? 
                               WHERE id=?");
        $stmt->execute([$name, $short_desc, $description, $image_path, $price, $is_active, $id]);
        $success = true;

        // Refresh data
        $stmt = $pdo->prepare("SELECT * FROM embroidery_designs WHERE id=?");
        $stmt->execute([$id]);
        $design = $stmt->fetch();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Embroidery Design</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen">

<!-- Header -->
<header class="bg-orange-600 text-white p-4 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
    <h1 class="text-2xl font-bold">Edit Embroidery Design</h1>
    <a href="embroidery.php" class="bg-white text-orange-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">Back to List</a>
</header>

<main class="container mx-auto p-6">
    <!-- Success Message -->
    <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 p-4 rounded-lg mb-4 shadow">
            Design updated successfully!
        </div>
    <?php endif; ?>

    <!-- Error Messages -->
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-700 p-4 rounded-lg mb-4 shadow">
            <ul class="list-disc pl-5">
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Form -->
    <form action="" method="POST" enctype="multipart/form-data" class="bg-white p-6 rounded-3xl shadow-md max-w-xl mx-auto space-y-4">
        <div>
            <label class="block mb-1 font-semibold">Name</label>
            <input type="text" name="name" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required value="<?= htmlspecialchars($design['name']) ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Short Description</label>
            <input type="text" name="short_desc" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required value="<?= htmlspecialchars($design['short_desc']) ?>">
        </div>
        
        <div>
            <label class="block mb-1 font-semibold">Price (Rs)</label>
            <input type="number" step="0.01" name="price" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-orange-400" required value="<?= htmlspecialchars($design['price']) ?>">
        </div>
        <div>
            <label class="block mb-1 font-semibold">Current Image</label>
            <?php if($design['image'] && file_exists("../" . $design['image'])): ?>
                <img src="../<?= htmlspecialchars($design['image']) ?>" class="w-32 h-32 object-cover rounded mb-2 cursor-pointer" onclick="window.open('../<?= htmlspecialchars($design['image']) ?>','_blank')">
            <?php else: ?>
                <p class="text-gray-500">No image uploaded</p>
            <?php endif; ?>
            <input type="file" name="image" class="w-full border border-gray-300 rounded-lg p-2 mt-2">
        </div>
        <div class="flex items-center gap-2">
            <input type="checkbox" name="is_active" id="is_active" class="w-5 h-5" <?= $design['is_active'] ? 'checked' : '' ?>>
            <label for="is_active" class="font-semibold">Active</label>
        </div>
        <button type="submit" class="w-full bg-orange-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-orange-700 transition">Update Design</button>
    </form>
</main>

</body>
</html>
