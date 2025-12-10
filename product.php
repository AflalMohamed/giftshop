<?php
session_start();
require 'config.php'; 

// --- SITE SETTINGS (get admin whatsapp) ---
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRaw as $s) $settings[$s['key_name']] = $s['value_text'];
$adminNumber = preg_replace('/\D/', '', $settings['footer_whatsapp'] ?? '');

// --- GET PRODUCT ---
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header("Location: products.php");
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    WHERE p.id = :id AND p.is_active = 1
    LIMIT 1
");
$stmt->execute([':id'=>$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: products.php");
    exit;
}

// --- URLS ---
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];
$image_path = !empty($product['image']) ? $product['image'] : 'uploads/placeholder.jpg';
$image_url = $baseUrl . '/' . ltrim($image_path, '/');
$fullLink = $baseUrl . $_SERVER['REQUEST_URI'];

include 'header.php';
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<main class="max-w-5xl mx-auto p-4">
  <a href="index.php" class="text-sm text-blue-600 hover:underline inline-block mb-4">← Back to Website</a>

  <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
    <div class="md:flex">

      <!-- PRODUCT IMAGE -->
      <div class="md:w-1/2">
        <img id="mainImage" src="<?= htmlspecialchars($image_path) ?>" 
             alt="<?= htmlspecialchars($product['name']) ?>"
             class="w-full h-80 md:h-[520px] object-cover cursor-zoom-in">
      </div>

      <!-- PRODUCT DETAILS -->
      <div class="md:w-1/2 p-6 flex flex-col">
        <h1 class="text-2xl md:text-3xl font-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>

        <?php if(!empty($product['category_name'])): ?>
        <div class="text-sm text-gray-500 mb-2">
          Category: <?= htmlspecialchars($product['category_name']) ?>
        </div>
        <?php endif; ?>

        <?php if(!empty($product['short_desc'])): ?>
        <p class="text-gray-700 mb-3"><?= htmlspecialchars($product['short_desc']) ?></p>
        <?php endif; ?>

        <div class="text-2xl md:text-3xl font-bold text-orange-600 mb-4">
          Rs <?= number_format($product['price'],2) ?>
        </div>

        <?php if(!empty($product['frame_size'])): ?>
        <div class="text-sm text-gray-600 mb-3">
          Frame: <span class="font-medium"><?= htmlspecialchars($product['frame_size']) ?></span>
        </div>
        <?php endif; ?>

        <div class="prose max-w-none text-gray-700 mb-6">
          <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="mt-auto grid grid-cols-2 gap-3">
          <button id="addCart"
            class="py-3 px-4 rounded-xl bg-blue-500 text-white font-bold flex items-center justify-center">
            <i class="fas fa-cart-plus mr-2"></i> Add to Cart
          </button>

          <button id="orderNow"
            class="py-3 px-4 rounded-xl bg-green-600 text-white font-bold flex items-center justify-center">
            <i class="fas fa-bolt mr-2"></i> Order Now
          </button>
        </div>

        <!-- INLINE ORDER FORM -->
        <div id="inlineOrderContainer" class="mt-4"></div>

        <!-- SHARE -->
        <div class="flex gap-3 mt-4">
          <a href="https://wa.me/?text=<?= urlencode($fullLink) ?>" 
             target="_blank"
             class="w-12 h-12 flex items-center justify-center rounded-full bg-green-500 text-white">
            <i class="fab fa-whatsapp"></i>
          </a>

          <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullLink) ?>" 
             target="_blank"
             class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-600 text-white">
            <i class="fab fa-facebook-f"></i>
          </a>

          <button id="copyLinkBtn" 
                  data-link="<?= htmlspecialchars($fullLink) ?>"
                  class="w-12 h-12 flex items-center justify-center rounded-full bg-gray-700 text-white">
            <i class="fas fa-link"></i>
          </button>
        </div>

      </div>
    </div>
  </div>
</main>

<!-- LIGHTBOX -->
<div id="lightbox" 
     class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50">
  <span id="closeLightbox" 
        class="absolute top-4 right-6 text-white text-4xl cursor-pointer">&times;</span>
  <img id="lightboxImg" class="max-h-[90%] max-w-[90%] rounded-lg shadow-xl">
</div>

<?php include 'footer.php'; ?>

<script>
// PRODUCT DATA
const adminNumber = '<?= $adminNumber ?>';
const pName = <?= json_encode($product['name']) ?>;
const pPrice = <?= json_encode(number_format($product['price'],2)) ?>;
const pLink = <?= json_encode($fullLink) ?>;
const pImage = <?= json_encode($image_url) ?>;

// COPY LINK
document.getElementById('copyLinkBtn')?.addEventListener('click', e=>{
  navigator.clipboard.writeText(e.target.closest('button').dataset.link)
    .then(()=> alert("Link Copied!"));
});

// LIGHTBOX
const mainImage = document.getElementById('mainImage');
const lightbox = document.getElementById('lightbox');
const closeLightbox = document.getElementById('closeLightbox');
const lightboxImg = document.getElementById('lightboxImg');

mainImage?.addEventListener('click', ()=>{
  lightboxImg.src = mainImage.src;
  lightbox.classList.remove('hidden');
  lightbox.classList.add('flex');
});
closeLightbox?.addEventListener('click', ()=>{
  lightbox.classList.add('hidden');
});
lightbox?.addEventListener('click', (e)=>{
  if(e.target === lightbox){
    lightbox.classList.add('hidden');
  }
});

// MINI CART
document.getElementById('addCart')?.addEventListener('click', ()=>{
  const cart = JSON.parse(localStorage.getItem('miniCart') || '[]');
  cart.push({ name: pName, price: pPrice, link: pLink });
  localStorage.setItem('miniCart', JSON.stringify(cart));
  alert(`${pName} added to cart. Total: ${cart.length}`);
});

// ORDER FORM (Same as all_products)
document.getElementById('orderNow')?.addEventListener('click', ()=>{

  const wrap = document.getElementById('inlineOrderContainer');
  let form = wrap.querySelector('.orderFormContainer');

  if(form){
    form.classList.toggle('hidden');
    return;
  }

  form = document.createElement('div');
  form.className = "orderFormContainer mt-3 bg-gray-50 p-4 rounded-lg border";
  form.innerHTML = `
    <input type="text" placeholder="Your Name" class="orderName w-full p-2 border rounded-lg mb-2">
    <input type="text" placeholder="Phone Number" class="orderPhone w-full p-2 border rounded-lg mb-2">
    <input type="text" placeholder="Delivery Address" class="orderAddress w-full p-2 border rounded-lg mb-2">
    <input type="number" value="1" min="1" class="orderQty w-full p-2 border rounded-lg mb-3">

    <div class="flex gap-2">
      <button class="sendOrderBtn flex-1 py-2 bg-green-600 text-white rounded-xl font-bold">
        Send via WhatsApp
      </button>

      <button class="sendOrderBtnNew flex-1 py-2 bg-orange-500 text-white rounded-xl font-bold">
        Open WhatsApp (New Tab)
      </button>
    </div>
  `;

  wrap.appendChild(form);

  form.querySelector('.sendOrderBtn').addEventListener('click', ()=> sendOrder(form));
  form.querySelector('.sendOrderBtnNew').addEventListener('click', ()=> sendOrder(form));
});

function sendOrder(f){
  const name = f.querySelector('.orderName').value.trim();
  const phone = f.querySelector('.orderPhone').value.trim();
  const address = f.querySelector('.orderAddress').value.trim();
  const qty = f.querySelector('.orderQty').value || 1;

  if(!name || !phone || !address){
    alert("All fields required!");
    return;
  }

  const msg = `Hello! I'm interested in this product:
🛍 Product: ${pName}
💰 Price: Rs ${pPrice}
📦 Quantity: ${qty}
🏠 Address: ${address}
👤 Name: ${name}
📞 Phone: ${phone}
🔗 Link: ${pLink}`;

  const url = `https://wa.me/${adminNumber}?text=${encodeURIComponent(msg)}`;
  window.open(url, '_blank');
}
</script>
