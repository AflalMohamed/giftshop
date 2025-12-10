<?php
session_start();
require 'config.php';

// --- FETCH CATEGORIES ---
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- SITE SETTINGS ---
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRaw as $s) $settings[$s['key_name']] = $s['value_text'];

// --- USER INPUTS ---
$search = trim($_GET['q'] ?? '');
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$order = $_GET['order'] ?? 'new';
$sizeFilter = trim($_GET['size'] ?? '');

// --- FRAME SIZE GROUPS ---
$frameGroups = [
    'small' => ['4x4','4x6','5x7','8x10'],
    'medium' => ['11x14','12x12','12x16'],
    'large' => ['16x20','18x24','20x24','24x36'],
    'a4' => ['A4','a4']
];

// --- DISTINCT FRAME SIZES ---
$sizeStmtSql = "SELECT DISTINCT frame_size FROM products WHERE frame_size IS NOT NULL AND frame_size <> ''";
$sizeParams = [];
if ($cat) {
    $sizeStmtSql .= " AND category_id = :size_cat";
    $sizeParams[':size_cat'] = $cat;
}
$sizeStmtSql .= " ORDER BY frame_size";
$sizeStmt = $pdo->prepare($sizeStmtSql);
$sizeStmt->execute($sizeParams);
$distinctSizes = array_map(fn($r)=>$r['frame_size'], $sizeStmt->fetchAll(PDO::FETCH_ASSOC));

// --- MAIN PRODUCT QUERY ---
$limit = 12;
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";
$params = [];

if ($search !== '') {
    $sql .= " AND (p.name LIKE :s1 OR c.name LIKE :s2 OR p.short_desc LIKE :s3 OR p.description LIKE :s4)";
    $params[':s1'] = "%$search%";
    $params[':s2'] = "%$search%";
    $params[':s3'] = "%$search%";
    $params[':s4'] = "%$search%";
}

if ($cat) {
    $sql .= " AND p.category_id = :cat";
    $params[':cat'] = $cat;
}

if ($sizeFilter !== '') {
    $lf = strtolower($sizeFilter);
    if (in_array($lf, ['small','medium','large','a4'])) {
        if ($lf === 'a4') {
            $sql .= " AND LOWER(p.frame_size) LIKE :size_like";
            $params[':size_like'] = '%a4%';
        } else {
            $groupSizes = $frameGroups[$lf] ?? [];
            if (!empty($groupSizes)) {
                $placeholders = [];
                foreach ($groupSizes as $i=>$val) {
                    $ph = ":fs_{$lf}_{$i}";
                    $placeholders[] = $ph;
                    $params[$ph] = $val;
                }
                $sql .= " AND p.frame_size IN (".implode(',',$placeholders).")";
            }
        }
    } else {
        $sql .= " AND LOWER(p.frame_size) = :size_exact";
        $params[':size_exact'] = strtolower($sizeFilter);
    }
}

if ($order === 'price_asc') $sql .= " ORDER BY p.price ASC";
elseif ($order === 'price_desc') $sql .= " ORDER BY p.price DESC";
else $sql .= " ORDER BY p.created_at DESC";

$sql .= " LIMIT $limit";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960';
$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? "https" : "http")."://".$_SERVER['HTTP_HOST'];

include 'header.php';
?>

<!-- HERO -->
<section class="bg-gradient-to-r from-orange-100 to-white py-24">
  <div class="container mx-auto px-6 md:px-12 flex flex-col-reverse md:flex-row items-center gap-12">
    <div class="flex-1 text-center md:text-left">
      <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">
        <?= htmlspecialchars($settings['hero_title'] ?? 'Delightful Gifts & Sweet Treats') ?>
      </h1>
      <p class="text-lg md:text-xl text-gray-600 mb-8">
        <?= htmlspecialchars($settings['hero_subtitle'] ?? 'Fast, Personalized Gifting for Every Occasion') ?>
      </p>
      <a href="#products" class="inline-block px-8 py-4 bg-orange-600 text-white rounded-xl font-bold shadow-lg hover:bg-orange-700 transition">
        Shop Now
      </a>
    </div>
    <div class="flex-1">
      <img src="<?= htmlspecialchars($settings['hero_image'] ?? 'uploads/gift.jpg') ?>" alt="Gifts" class="rounded-3xl shadow-xl w-full">
    </div>
  </div>
</section>

<!-- SEARCH & FILTER -->
<section class="py-8 bg-gray-50" id="search-panel">
  <div class="container mx-auto px-6 md:px-12">
    <form id="searchForm" method="get" class="flex flex-wrap gap-3 items-center justify-center md:justify-start">
      <input name="q" value="<?= htmlentities($search) ?>" placeholder="Search products..." class="p-3 border rounded-xl flex-1 focus:ring focus:ring-orange-300">
      <select name="cat" class="p-3 border rounded-xl">
        <option value="0">All categories</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':'' ?>><?= htmlentities($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
      <?php if(!empty($distinctSizes)): ?>
        <select name="size" class="p-3 border rounded-xl">
          <option value="">All sizes</option>
          <optgroup label="Quick groups">
            <option value="small" <?= $sizeFilter==='small'?'selected':'' ?>>Small</option>
            <option value="medium" <?= $sizeFilter==='medium'?'selected':'' ?>>Medium</option>
            <option value="large" <?= $sizeFilter==='large'?'selected':'' ?>>Large</option>
            <option value="a4" <?= strtolower($sizeFilter)==='a4'?'selected':'' ?>>A4</option>
          </optgroup>
          <optgroup label="Exact sizes">
            <?php foreach($distinctSizes as $ds):
              if(!$ds) continue;
              $sel = strtolower($sizeFilter) === strtolower($ds) ? 'selected' : '';
            ?>
            <option value="<?= htmlspecialchars($ds) ?>" <?= $sel ?>><?= htmlspecialchars($ds) ?></option>
            <?php endforeach; ?>
          </optgroup>
        </select>
      <?php endif; ?>
      <select name="order" class="p-3 border rounded-xl">
        <option value="new" <?= $order=='new'?'selected':'' ?>>Newest</option>
        <option value="price_asc" <?= $order=='price_asc'?'selected':'' ?>>Price ↑</option>
        <option value="price_desc" <?= $order=='price_desc'?'selected':'' ?>>Price ↓</option>
      </select>
      <button type="submit" class="px-6 py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition">
        Search
      </button>
    </form>
  </div>
</section>

<!-- PRODUCTS GRID -->
<section class="py-16 bg-white" id="products">
  <div class="container mx-auto px-6 md:px-12">
    <h2 class="text-4xl font-bold mb-8 text-center md:text-left">Today's Gift Picks</h2>

    <?php if(count($products)>0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
      <?php foreach($products as $p):
        $img = $p['image'] ?: 'uploads/placeholder.jpg';
        $fullLink = $baseUrl."/product.php?id=".$p['id'];
      ?>
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition relative">
          <div class="relative">
            <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlentities($p['name']) ?>" class="w-full h-56 object-cover cursor-pointer product-image" data-full="<?= htmlspecialchars($img) ?>">
          </div>
          <div class="p-5 flex flex-col flex-1">
            <h3 class="font-semibold text-lg"><?= htmlentities($p['name']) ?></h3>
            <p class="text-gray-500 text-sm mb-2"><?= htmlentities($p['short_desc']) ?></p>
            <?php if($p['frame_size']): ?>
              <p class="text-sm text-gray-600 mb-2">Frame: <span class="font-medium"><?= htmlentities($p['frame_size']) ?></span></p>
            <?php endif; ?>
            <span class="font-bold text-orange-600 text-xl mb-3">Rs <?= number_format($p['price'],2) ?></span>

            <!-- ACTION BUTTONS -->
            <div class="flex gap-2 items-center mt-auto">
              <button type="button" class="addCartBtn w-1/2 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition font-bold" data-name="<?= htmlentities($p['name']) ?>" data-price="<?= number_format($p['price'],2) ?>" data-link="<?= $fullLink ?>">Add to Cart</button>
              <button type="button" class="buyNowBtn w-1/2 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold" data-name="<?= htmlentities($p['name']) ?>" data-price="<?= number_format($p['price'],2) ?>" data-link="<?= $fullLink ?>">Order Now</button>
            </div>

            <!-- SHARE BUTTONS -->
            <div class="flex justify-center gap-3 mt-4">
              <!-- WhatsApp -->
              <a href="https://wa.me/?text=<?= urlencode($fullLink) ?>" target="_blank" class="w-10 h-10 flex items-center justify-center bg-green-500 text-white rounded-full hover:bg-green-600 transition text-2xl">
                <i class="fab fa-whatsapp"></i>
              </a>
              <!-- Facebook -->
              <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullLink) ?>" target="_blank" class="w-10 h-10 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 transition text-2xl">
                <i class="fab fa-facebook-f"></i>
              </a>
              <!-- Copy Link -->
              <button type="button" class="copyLinkBtn w-10 h-10 flex items-center justify-center bg-gray-600 text-white rounded-full hover:bg-gray-700 transition text-2xl" data-link="<?= $fullLink ?>">
                <i class="fas fa-link"></i>
              </button>
            </div>

            <!-- COLLAPSIBLE ORDER FORM -->
            <div class="orderFormContainer mt-3 hidden">
              <input type="text" placeholder="Your Name" class="orderName w-full p-2 border rounded-lg mb-2" value="<?= $_SESSION['user_name'] ?? '' ?>">
              <input type="text" placeholder="Phone Number" class="orderPhone w-full p-2 border rounded-lg mb-2" value="<?= $_SESSION['user_phone'] ?? '' ?>">
              <input type="text" placeholder="Delivery Address" class="orderAddress w-full p-2 border rounded-lg mb-2" value="<?= $_SESSION['user_address'] ?? '' ?>">
              <input type="number" min="1" value="1" class="orderQty w-full p-2 border rounded-lg mb-2">
              <button class="sendOrderBtn w-full py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">Send via WhatsApp</button>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="text-center mt-12">
      <a href="all_products.php" class="px-6 py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition">See More Products</a>
    </div>

    <?php else: ?>
      <p class="text-center text-gray-500 text-lg py-20">No products found matching your search criteria.</p>
    <?php endif; ?>
  </div>
</section>

<!-- LIGHTBOX -->
<div id="lightbox" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
  <img id="lightboxImage" src="" alt="Preview" class="max-h-[90%] max-w-[90%] rounded-xl shadow-2xl">
  <button id="lightboxClose" class="absolute top-6 right-6 text-white text-3xl font-bold hover:text-orange-400">&times;</button>
</div>

<!-- SNOW CANVAS -->
<canvas id="snowCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>

<!-- FONT AWESOME -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>

<script>
// --- Variables ---
const adminWhatsapp = '<?= preg_replace("/\D/","",$adminWhatsapp) ?>';

// --- COPY LINK BUTTON ---
document.querySelectorAll('.copyLinkBtn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    navigator.clipboard.writeText(btn.dataset.link).then(()=> alert('Link copied!'));
  });
});

// --- COLLAPSIBLE ORDER FORM ---
document.querySelectorAll('.buyNowBtn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const card = btn.closest('div.flex.flex-col');
    document.querySelectorAll('.orderFormContainer').forEach(f=>{
      if(f!==card.querySelector('.orderFormContainer')) f.classList.add('hidden');
    });
    const form = card.querySelector('.orderFormContainer');
    form.classList.toggle('hidden');
  });
});

// --- SEND ORDER VIA WHATSAPP ---
document.querySelectorAll('.sendOrderBtn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const card = btn.closest('.orderFormContainer');
    const productCard = btn.closest('div.flex.flex-col');
    const product = productCard.querySelector('.buyNowBtn').dataset.name;
    const price = productCard.querySelector('.buyNowBtn').dataset.price;
    const link = productCard.querySelector('.buyNowBtn').dataset.link;
    const name = card.querySelector('.orderName').value.trim();
    const phone = card.querySelector('.orderPhone').value.trim();
    const address = card.querySelector('.orderAddress').value.trim();
    const qty = card.querySelector('.orderQty').value;
    if(!name||!phone||!address){ alert('All fields required'); return;}
    const message = `Hello! I'm interested in this product:
🛍 Product: ${product}
💰 Price: Rs ${price}
📦 Quantity: ${qty}
🏠 Address: ${address}
👤 Name: ${name}
📞 Phone: ${phone}
🔗 Link: ${link}`;
    window.open(`https://wa.me/${adminWhatsapp}?text=${encodeURIComponent(message)}`,'_blank');
  });
});

// --- ADD TO CART ---
let miniCart = JSON.parse(localStorage.getItem('miniCart')||'[]');
document.querySelectorAll('.addCartBtn').forEach(btn=>{
  btn.addEventListener('click',()=>{
    const name = btn.dataset.name;
    const price = btn.dataset.price;
    const link = btn.dataset.link;
    miniCart.push({name,price,link});
    localStorage.setItem('miniCart', JSON.stringify(miniCart));
    alert(`${name} added to cart! Total items: ${miniCart.length}`);
  });
});

// --- LIGHTBOX ---
const lightbox=document.getElementById('lightbox'); 
const lightboxImage=document.getElementById('lightboxImage'); 
const lightboxClose=document.getElementById('lightboxClose'); 
let snowActive = true; 
document.querySelectorAll('.product-image').forEach(img=>{
  img.addEventListener('click',()=>{
    lightboxImage.src = img.dataset.full;
    lightbox.classList.remove('hidden'); lightbox.classList.add('flex');
    snowActive=false;
  });
});
function closeLightbox(){ lightbox.classList.add('hidden'); lightbox.classList.remove('flex'); snowActive=true; drawSnow(); }
lightboxClose.addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e=>{ if(e.target===lightbox) closeLightbox(); });

// --- PRESERVE ANCHOR ON SEARCH ---
document.getElementById('searchForm').addEventListener('submit',function(e){
  e.preventDefault();
  const params=new URLSearchParams(new FormData(this));
  window.location.href=window.location.pathname+'?'+params.toString()+'#products';
});

// --- SNOW EFFECT ---
const canvas = document.getElementById('snowCanvas'); 
const ctx = canvas.getContext('2d'); 
canvas.width = window.innerWidth; canvas.height = window.innerHeight; 
const particles=[]; 
for(let i=0;i<100;i++){ particles.push({x:Math.random()*canvas.width, y:Math.random()*canvas.height, r:Math.random()*2+1, d:Math.random()*2}); } 
function drawSnow(){ ctx.clearRect(0,0,canvas.width,canvas.height); if(!snowActive) return; ctx.fillStyle="white"; ctx.beginPath(); for(let p of particles){ ctx.moveTo(p.x,p.y); ctx.arc(p.x,p.y,p.r,0,Math.PI*2,true); } ctx.fill(); updateSnow(); if(snowActive) requestAnimationFrame(drawSnow);} 
function updateSnow(){ for(let p of particles){ p.y += Math.pow(p.d,0.5); p.x += Math.sin(p.y/10); if(p.y>canvas.height){ p.y=0; p.x=Math.random()*canvas.width; } } } 
drawSnow(); 
window.addEventListener('resize',()=>{canvas.width=window.innerWidth; canvas.height=window.innerHeight;});
</script>

<?php include 'footer.php'; ?>
