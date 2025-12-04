<?php
require '_auth_check.php';
require '../config.php';

/* ---------------------------------------
    DELETE PRODUCT
---------------------------------------- */
if (isset($_GET['delete_id'])) {
    $id = (int)$_GET['delete_id'];

    $stmt = $pdo->prepare("SELECT image FROM products WHERE id=:id");
    $stmt->execute([':id' => $id]);
    $product = $stmt->fetch();

    if ($product) {
        if (!empty($product['image']) && file_exists("../".$product['image'])) {
            unlink("../".$product['image']);
        }

        $del = $pdo->prepare("DELETE FROM products WHERE id=:id");
        $del->execute([':id' => $id]);

        header("Location: manage_products.php?msg=deleted");
        exit;
    }
}

/* ---------------------------------------
    SEARCH + FILTER
---------------------------------------- */
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';

$query = "
    SELECT p.*, c.name AS category
    FROM products p
    LEFT JOIN categories c ON p.category_id=c.id
    WHERE 1
";

$params = [];

if (!empty($search)) {
    $query .= " AND p.name LIKE :search";
    $params[':search'] = "%$search%";
}

if (!empty($categoryFilter)) {
    $query .= " AND p.category_id = :cat";
    $params[':cat'] = $categoryFilter;
}

$query .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categoryList = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Products</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen">

<!-- HEADER -->
<header class="bg-white shadow-md mb-6 sticky top-0 z-50">
    <div class="max-w-6xl mx-auto p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        <h1 class="text-2xl sm:text-3xl font-bold text-orange-600">Manage Products</h1>

        <div class="flex gap-3">
            <a href="dashboard.php" class="px-4 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 font-medium">Dashboard</a>
            <a href="add_product.php" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-medium">Add Product</a>
        </div>
    </div>
</header>

<!-- SUCCESS MESSAGE -->
<?php if(isset($_GET['msg']) && $_GET['msg']=='deleted'): ?>
<div class="max-w-6xl mx-auto mb-4 p-4 bg-green-100 text-green-700 rounded-lg shadow text-center">
    Product deleted successfully!
</div>
<?php endif; ?>

<!-- SEARCH AND FILTER -->
<div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow mb-6">
    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <input 
            type="text"
            name="search"
            placeholder="Search products..."
            value="<?= htmlspecialchars($search) ?>"
            class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-400 w-full"
        >

        <select name="category" class="px-4 py-3 border rounded-lg focus:ring-2 focus:ring-orange-400 w-full">
            <option value="">All Categories</option>
            <?php foreach($categoryList as $cat): ?>
                <option value="<?= $cat['id'] ?>" <?= ($categoryFilter == $cat['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button class="px-4 py-3 bg-orange-600 text-white rounded-lg hover:bg-orange-700 font-semibold w-full">
            Filter
        </button>
    </form>
</div>

<!-- PRODUCT TABLE -->
<main class="max-w-6xl mx-auto p-4">
    <div class="overflow-x-auto bg-white rounded-xl shadow-lg">
        <table class="min-w-full divide-y divide-gray-200">

            <thead class="bg-orange-100">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-700 font-medium">Image</th>
                    <th class="px-4 py-3 text-left text-gray-700 font-medium">Name</th>
                    <th class="px-4 py-3 text-left text-gray-700 font-medium">Category</th>
                    <th class="px-4 py-3 text-left text-gray-700 font-medium">Price</th>
                   
                    <th class="px-4 py-3 text-center text-gray-700 font-medium">Actions</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                <?php if($products): ?>
                    <?php foreach($products as $p): ?>
                    <tr class="hover:bg-orange-50 transition">

                        <td class="px-4 py-3">
                            <?php if(!empty($p['image'])): ?>
                                <img src="../<?= htmlspecialchars($p['image']) ?>" class="h-16 w-16 object-cover rounded border">
                            <?php else: ?>
                                <span class="text-gray-400 text-sm">No Image</span>
                            <?php endif; ?>
                        </td>

                        <td class="px-4 py-3 text-sm"><?= htmlspecialchars($p['name']) ?></td>

                        <td class="px-4 py-3 text-orange-700 font-semibold text-sm">
                            <?= htmlspecialchars($p['category'] ?? '-') ?>
                        </td>

                        <td class="px-4 py-3 text-sm">Rs <?= number_format($p['price'], 2) ?></td>

                        

                        <td class="px-4 py-3 flex justify-center gap-2">
                            <a href="edit_product.php?id=<?= $p['id'] ?>"
                               class="px-3 py-1 text-white bg-blue-600 hover:bg-blue-700 rounded-lg text-sm">
                               Edit
                            </a>
                            <a href="?delete_id=<?= $p['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this product?')"
                               class="px-3 py-1 text-white bg-red-600 hover:bg-red-700 rounded-lg text-sm">
                               Delete
                            </a>
                        </td>

                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                            No products found.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>

        </table>
    </div>
</main>

</body>
</html>
