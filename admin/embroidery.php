<?php
require '_auth_check.php';
require '../config.php';

// Fetch all embroidery designs
$designs = $pdo->query("SELECT * FROM embroidery_designs ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Embroidery Admin</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans min-h-screen">

<!-- Header -->
<header class="bg-orange-600 text-white p-4 shadow-md flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
    <h1 class="text-2xl font-bold">Embroidery Admin</h1>
    <div class="flex gap-2 flex-wrap">
        <a href="dashboard.php" class="bg-white text-orange-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">Back to Dashboard</a>
        <a href="embroidery_add.php" class="bg-white text-orange-600 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100">Add New Design</a>
    </div>
</header>

<!-- Main Content -->
<main class="container mx-auto p-6">
    <?php if(count($designs) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php foreach($designs as $d): ?>
                <?php
                    $imgPath = "../" . $d['image'];
                    $hasImage = $d['image'] && file_exists($imgPath);
                ?>
                <div class="bg-white rounded-3xl shadow hover:shadow-xl overflow-hidden flex flex-col">
                    <div class="relative">
                        <?php if($hasImage): ?>
                            <img src="<?= htmlspecialchars($imgPath) ?>" alt="<?= htmlspecialchars($d['name']) ?>" class="w-full h-48 object-cover cursor-pointer" onclick="window.open('<?= htmlspecialchars($imgPath) ?>','_blank')">
                        <?php else: ?>
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-500">No Image</div>
                        <?php endif; ?>
                    </div>
                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <h2 class="text-lg font-bold mb-1"><?= htmlspecialchars($d['name']) ?></h2>
                            <p class="text-gray-600 mb-1"><?= htmlspecialchars($d['short_desc']) ?></p>
                            <p class="text-orange-600 font-semibold mb-2">Rs <?= number_format($d['price'],2) ?></p>
                            <p class="text-sm text-gray-500 mb-2">Active: <?= $d['is_active'] ? 'Yes' : 'No' ?></p>
                            <p class="text-xs text-gray-400 mb-2">Created at: <?= $d['created_at'] ?></p>
                        </div>
                        <div class="flex gap-2 mt-2">
                            <a href="edit_embroidery.php?id=<?= $d['id'] ?>" class="flex-1 text-center bg-blue-500 text-white px-2 py-2 rounded-xl hover:bg-blue-600 font-semibold">Edit</a>
                            <a href="delete_embroidery.php?id=<?= $d['id'] ?>" onclick="return confirm('Are you sure?')" class="flex-1 text-center bg-red-500 text-white px-2 py-2 rounded-xl hover:bg-red-600 font-semibold">Delete</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-gray-500 text-center mt-8">No embroidery designs found. <a href="embroidery_add.php" class="text-orange-600 hover:underline">Add one now</a>.</p>
    <?php endif; ?>
</main>

</body>
</html>
