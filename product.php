<?php
require 'config.php'; // ADMIN_WHATSAPP should already be defined here

// Get product ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product info
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = ? LIMIT 1
");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Redirect if product not found
if (!$product) {
    header("Location: index.php");
    exit;
}

// Product image path & URL
$image_path = !empty($product['image']) ? $product['image'] : 'assets/placeholder.jpg';
$image_url = !empty($product['image'])
    ? (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") 
      . "://{$_SERVER['HTTP_HOST']}/{$product['image']}" 
    : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($product['name']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">

<div class="container mx-auto p-4">

    <!-- Back link -->
    <a href="index.php" class="text-sm text-blue-600 hover:underline">← Back to Products</a>

    <div class="bg-white rounded-2xl shadow p-6 mt-4">
        <div class="md:flex md:gap-6">

            <!-- Product Image -->
            <div class="md:w-1/2 mb-4 md:mb-0">
                <img src="<?= htmlspecialchars($image_path) ?>" 
                     alt="<?= htmlspecialchars($product['name']) ?>" 
                     class="w-full h-96 object-cover rounded-2xl shadow">
            </div>

            <!-- Product Info & Order Form -->
            <div class="md:w-1/2 flex flex-col justify-between">
                <div>
                    <h1 class="text-3xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                    <?php if(!empty($product['category_name'])): ?>
                        <p class="text-gray-500 mb-2">Category: <?= htmlspecialchars($product['category_name']) ?></p>
                    <?php endif; ?>
                    <p class="text-gray-700 mb-4"><?= htmlspecialchars($product['short_desc']) ?></p>
                    <div class="text-2xl font-semibold mb-4">Rs <?= number_format($product['price'],2) ?></div>
                    <p class="text-gray-600 mb-6"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>

                <!-- Customer Order Form -->
                <form id="orderForm" class="space-y-3">
                    <input type="text" name="customer_name" placeholder="Your Name" required
                           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
                    <input type="text" name="customer_phone" placeholder="Phone Number" required
                           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
                    <input type="text" name="customer_address" placeholder="Delivery Address" required
                           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
                    <input type="number" name="quantity" min="1" value="1" required
                           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
                    <button type="submit"
                            class="w-full py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
                        Send Order via WhatsApp
                    </button>
                </form>

            </div>

        </div>
    </div>

</div>

<script>
document.getElementById('orderForm').addEventListener('submit', function(e){
    e.preventDefault(); // prevent page reload

    const name = this.customer_name.value.trim();
    const phone = this.customer_phone.value.trim();
    const address = this.customer_address.value.trim();
    const quantity = this.quantity.value.trim();

    // Construct WhatsApp message
    const message = `Hello, I'm interested in this product:\n` +
                    `Product: <?= addslashes($product['name']) ?>\n` +
                    `Price: Rs <?= number_format($product['price'],2) ?>\n` +
                    `Quantity: ${quantity}\n` +
                    `Image: <?= $image_url ?>\n` +
                    `Product link: http://<?= $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ?>\n` +
                    `Delivery to: ${address}\n` +
                    `Name: ${name}\n` +
                    `Phone: ${phone}`;

    // Open WhatsApp with prefilled message
    const wa_link = `https://wa.me/<?= ADMIN_WHATSAPP ?>?text=` + encodeURIComponent(message);
    window.open(wa_link, '_blank');
});
</script>

</body>
</html>
