<?php
require '_auth_check.php';
require_once __DIR__ . '/../config.php';

// Fetch categories
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// Initialize variables
$errors = [];
$message = '';
$frameSizes = [
    'Small' => ['4x4','4x6', '5x7', '8x10'],
    'Medium' => ['11x14', '12x12', '12x16'],
    'Large' => ['16x20', '18x24', '20x24', '24x36'],
    'Other' => ['A4']
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?: null;
    $short_desc = trim($_POST['short_desc'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = isset($_POST['price']) ? (float)$_POST['price'] : 0;
    $stock = isset($_POST['stock']) ? (int)$_POST['stock'] : 0;
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $frame_size = $_POST['frame_size'] ?? null;

    // Validation
    if ($name === '') $errors[] = "Product name is required.";
    if ($price <= 0) $errors[] = "Price must be greater than 0.";

    // Generate slug
    $base_slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    $slug = $base_slug;
    $i = 1;
    while ($pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1")->execute([$slug]) && $pdo->prepare("SELECT id FROM products WHERE slug = ? LIMIT 1")->fetch()) {
        $slug = $base_slug . '-' . $i;
        $i++;
    }

    // Image upload
    $imagePath = null;
    if (!empty($_FILES['image']['name'])) {
        $allowed = ['jpg','jpeg','png','webp'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid image type. Allowed: jpg, jpeg, png, webp.";
        } else {
            $newName = uniqid('p_') . '.' . $ext;
            $dstDir = __DIR__ . '/../uploads/products/';
            if (!is_dir($dstDir)) mkdir($dstDir, 0755, true);
            $dst = $dstDir . $newName;
            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dst)) {
                $errors[] = "Failed to upload image.";
            } else {
                $imagePath = 'uploads/products/' . $newName;
            }
        }
    }

    // Insert into DB if no errors
    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, slug, short_desc, description, price, stock, image, is_active, frame_size) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$category_id, $name, $slug, $short_desc, $description, $price, $stock, $imagePath, $is_active, $frame_size]);
        $message = "<div class='text-green-600 font-semibold mb-4'>Product added successfully!</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Product</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen py-8">

<div class="max-w-4xl mx-auto bg-white p-8 rounded-3xl shadow-xl">

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-orange-600">Add New Product</h1>
        <a href="products.php" class="text-blue-600 font-semibold hover:underline">← Back to Products</a>
    </div>

    <!-- Messages -->
    <?= $message ?>
    <?php if(!empty($errors)): ?>
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 rounded">
            <?php foreach($errors as $e) echo "<div>- " . htmlspecialchars($e) . "</div>"; ?>
        </div>
    <?php endif; ?>

    <!-- Add Product Form -->
    <form method="post" enctype="multipart/form-data" class="space-y-5">

        <div>
            <label class="block font-medium mb-1">Product Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-orange-300" required>
        </div>

        <div>
            <label class="block font-medium mb-1">Category</label>
            <select name="category_id" id="categorySelect" class="w-full p-3 border rounded-lg">
                <option value="">-- None --</option>
                <?php foreach($categories as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (isset($_POST['category_id']) && $_POST['category_id']==$c['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Frame Size Dropdown (hidden by default) -->
        <div id="frameSizeDiv" class="hidden">
            <label class="block font-medium mb-1">Frame Size</label>
            <select name="frame_size" class="w-full p-3 border rounded-lg">
                <option value="">-- Select Frame Size --</option>
                <?php foreach($frameSizes as $group => $sizes): ?>
                    <optgroup label="<?= $group ?>">
                        <?php foreach($sizes as $size): ?>
                            <option value="<?= $size ?>" <?= (isset($_POST['frame_size']) && $_POST['frame_size']==$size) ? 'selected' : '' ?>><?= $size ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label class="block font-medium mb-1">Short Description</label>
            <input type="text" name="short_desc" value="<?= htmlspecialchars($_POST['short_desc'] ?? '') ?>" class="w-full p-3 border rounded-lg">
        </div>

      

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block font-medium mb-1">Price (LKR) <span class="text-red-500">*</span></label>
                <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" class="w-full p-3 border rounded-lg" required>
            </div>
          

        <div>
            <label class="block font-medium mb-1">Product Image (jpg, png, webp)</label>
            <input type="file" name="image" accept="image/*" class="w-full p-2 border rounded-lg">
        </div>

        <div class="flex items-center gap-4">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" <?= isset($_POST['is_active']) ? 'checked' : 'checked' ?>> Active
            </label>
        </div>

        <div>
            <button type="submit" class="w-full py-3 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition">
                Add Product
            </button>
        </div>
    </form>
</div>

<!-- JS to show/hide frame size -->
<script>
const categorySelect = document.getElementById('categorySelect');
const frameDiv = document.getElementById('frameSizeDiv');

function toggleFrameDropdown() {
    const selectedText = categorySelect.options[categorySelect.selectedIndex].text.toLowerCase();
    if (selectedText === 'frames') {
        frameDiv.classList.remove('hidden');
    } else {
        frameDiv.classList.add('hidden');
    }
}

// Initial check
toggleFrameDropdown();

// Event listener
categorySelect.addEventListener('change', toggleFrameDropdown);
</script>

</body>
</html>
