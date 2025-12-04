<?php
// Variables expected:
// $productName, $price, $imageUrl, $productLink, $adminNumber
?>
<form class="space-y-2 orderForm" data-product="<?= htmlspecialchars($productName) ?>" data-price="<?= $price ?>" data-image="<?= htmlspecialchars($imageUrl) ?>" data-link="<?= htmlspecialchars($productLink) ?>">
    <input type="text" name="customer_name" placeholder="Your Name" required
           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
    <input type="text" name="customer_phone" placeholder="Phone" required
           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
    <input type="text" name="customer_address" placeholder="Delivery Address" required
           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
    <input type="number" name="quantity" min="1" value="1"
           class="w-full p-2 border rounded focus:ring focus:ring-blue-300">
    <button type="submit" 
            class="w-full py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition font-semibold">
        Buy via WhatsApp
    </button>
</form>
