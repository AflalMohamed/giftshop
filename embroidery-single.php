<?php
require 'config.php';

$id = $_GET['id'] ?? 0;
$stmt = $pdo->prepare("SELECT * FROM embroidery_designs WHERE id=? AND is_active=1");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    echo "<h2 class='text-center py-20 text-xl'>Product not found!</h2>";
    exit;
}

$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960';
$productUrl = "embroidery-single.php?id=" . $p['id'];
$img = $p['image'] ?: "uploads/placeholder.jpg";

include 'header1.php';
?>

<section class="py-12 bg-gray-50">
  <div class="container mx-auto px-5 md:px-12 grid grid-cols-1 md:grid-cols-2 gap-10 items-start">

    <!-- PRODUCT IMAGE -->
    <div class="flex justify-center">
      <img src="<?= $img ?>" alt="<?= htmlentities($p['name']) ?>" 
           class="w-56 md:w-64 rounded-2xl shadow-lg cursor-pointer"
           id="productImage">
    </div>

    <!-- PRODUCT DETAILS -->
    <div class="flex flex-col gap-4">

      <!-- BACK BUTTON -->
      <a href="embroidery.php" 
         class="inline-block mb-3 text-white bg-gray-600 hover:bg-gray-700 transition px-4 py-2 rounded-lg font-medium">
         &larr; Back to Embroidery
      </a>

      <h1 class="text-2xl md:text-3xl font-bold text-gray-900"><?= htmlentities($p['name']) ?></h1>

      <p class="text-gray-600 text-base md:text-lg"><?= htmlentities($p['short_desc']) ?></p>

      <?php if ($p['price'] > 0): ?>
        <p class="text-orange-600 text-2xl md:text-3xl font-bold">Rs <?= number_format($p['price'], 2) ?></p>
      <?php endif; ?>

      <!-- SHARE BUTTONS WITH INLINE SVG ICONS -->
      <div class="flex items-center gap-4 mt-3">

        <!-- WhatsApp -->
        <button class="shareWhatsapp p-2 rounded-full hover:bg-green-100 transition" data-link="<?= $productUrl ?>" title="Share on WhatsApp">
          <svg class="w-6 h-6 text-green-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17.472 14.382c-.297-.149-1.758-.868-2.031-.967-.273-.099-.471-.148-.669.149-.198.297-.767.966-.94 1.164-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.786-1.48-1.754-1.653-2.051-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.496.099-.198.05-.372-.025-.521-.074-.149-.669-1.611-.916-2.206-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.793.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.077 4.487.709.306 1.262.489 1.693.625.711.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414-.074-.124-.272-.198-.57-.347z"/>
          </svg>
        </button>

        <!-- Facebook -->
        <button class="shareFacebook p-2 rounded-full hover:bg-blue-100 transition" data-link="<?= $productUrl ?>" title="Share on Facebook">
          <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
            <path d="M22 12c0-5.522-4.477-10-10-10S2 6.478 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987H7.898v-2.89h2.54V9.845c0-2.507 1.492-3.89 3.777-3.89 1.094 0 2.238.196 2.238.196v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562v1.875h2.773l-.443 2.89h-2.33v6.987C18.343 21.128 22 16.991 22 12"/>
          </svg>
        </button>

        <!-- Copy Link -->
        <button class="copyLink p-2 rounded-full hover:bg-gray-200 transition" data-link="<?= $productUrl ?>" title="Copy Link">
          <svg class="w-6 h-6 text-gray-700" fill="currentColor" viewBox="0 0 24 24">
            <path d="M3.9 12a3.9 3.9 0 0 1 3.9-3.9h4v2h-4a1.9 1.9 0 1 0 0 3.8h4v2h-4a3.9 3.9 0 0 1-3.9-3.9zm7.2-1h6a3 3 0 1 1 0 6h-6v-2h6a1 1 0 1 0 0-2h-6v-2z"/>
          </svg>
        </button>

      </div>

      <!-- ORDER BUTTON -->
      <button id="showOrderFormBtn" class="mt-4 w-full py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
        Order via WhatsApp
      </button>

      <!-- ORDER FORM (HIDDEN) -->
      <div id="orderFormContainer" class="bg-white shadow-xl rounded-2xl p-6 mt-4 hidden">
        <h2 class="text-xl font-semibold mb-4">Enter Your Details</h2>

        <form class="orderForm space-y-3">
          <input type="text" name="customer_name" placeholder="Your Name" required class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-400">
          <input type="text" name="customer_phone" placeholder="Phone Number" required class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-400">
          <input type="text" name="customer_address" placeholder="Delivery Address" required class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-400">
          <input type="number" name="quantity" min="1" value="1" class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-orange-400">

          <button type="submit" class="w-full py-3 bg-green-600 text-white rounded-lg font-bold hover:bg-green-700 transition">
            Send Order via WhatsApp
          </button>
        </form>
      </div>

    </div>
  </div>
</section>

<!-- LIGHTBOX -->
<div id="lightbox" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
  <img id="lightboxImage" class="max-h-[90%] max-w-[90%] rounded-xl shadow-2xl">
  <button id="lightboxClose" class="absolute top-6 right-6 text-white text-3xl font-bold">&times;</button>
</div>

<script>
// IMAGE LIGHTBOX
const lb = document.getElementById("lightbox");
const lbImg = document.getElementById("lightboxImage");
document.getElementById("productImage").onclick = () => {
  lbImg.src = "<?= $img ?>";
  lb.classList.remove("hidden");
  lb.classList.add("flex");
};
document.getElementById("lightboxClose").onclick = () => lb.classList.add("hidden");
lb.onclick = e => { if(e.target===lb) lb.classList.add("hidden"); };

// SHOW ORDER FORM
document.getElementById('showOrderFormBtn').addEventListener('click', function(){
    document.getElementById('orderFormContainer').classList.toggle('hidden');
});

// ORDER FORM SUBMIT
document.querySelector(".orderForm").addEventListener("submit", function(e){
    e.preventDefault();
    let name = this.customer_name.value.trim();
    let phone = this.customer_phone.value.trim();
    let address = this.customer_address.value.trim();
    let qty = this.quantity.value;

    if(!name || !phone || !address){
        alert("Please fill all fields");
        return;
    }

    const msg = `Hello! I'm interested in this embroidery design:
🧵 <?= htmlentities($p['name']) ?>
💰 Rs <?= number_format($p['price'],2) ?>
📦 Qty: ${qty}
🏠 Address: ${address}
👤 Name: ${name}
📞 Phone: ${phone}
🔗 Link: <?= $productUrl ?>`;

    window.open(
        "https://wa.me/<?= preg_replace('/\D/','',$adminWhatsapp) ?>?text=" + encodeURIComponent(msg),
        "_blank"
    );
});

// SHARE BUTTONS
document.querySelector(".shareWhatsapp").onclick = (e)=>{
    const link = location.origin + "/" + e.currentTarget.dataset.link;
    window.open("https://wa.me/?text=" + encodeURIComponent(link), "_blank");
};
document.querySelector(".shareFacebook").onclick = (e)=>{
    const link = location.origin + "/" + e.currentTarget.dataset.link;
    window.open("https://www.facebook.com/sharer/sharer.php?u=" + encodeURIComponent(link), "_blank");
};
document.querySelector(".copyLink").onclick = (e)=>{
    const link = location.origin + "/" + e.currentTarget.dataset.link;
    navigator.clipboard.writeText(link);
    alert("Link Copied!");
};
</script>

<?php include 'footer1.php'; ?>
