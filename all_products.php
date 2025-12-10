<?php
session_start();
require 'config.php';

// --- FETCH CATEGORIES ---
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

// --- SITE SETTINGS ---
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRaw as $s) $settings[$s['key_name']] = $s['value_text'];

// --- Admin WhatsApp number ---
$adminNumber = preg_replace('/\D/', '', $settings['footer_whatsapp']); // e.g., 94703720960

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
if ($cat) { $sizeStmtSql .= " AND category_id = :size_cat"; $sizeParams[':size_cat']=$cat; }
$sizeStmtSql .= " ORDER BY frame_size";
$sizeStmt = $pdo->prepare($sizeStmtSql);
$sizeStmt->execute($sizeParams);
$distinctSizes = array_map(fn($r)=>$r['frame_size'], $sizeStmt->fetchAll(PDO::FETCH_ASSOC));

// --- MAIN PRODUCT QUERY ---
$sql = "SELECT p.*, c.name AS category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.is_active = 1";
$params = [];

if ($search!=='') { 
    $sql.=" AND (p.name LIKE :s OR c.name LIKE :s OR p.short_desc LIKE :s OR p.description LIKE :s)"; 
    $params[':s']="%$search%"; 
}

if ($cat) { 
    $sql.=" AND p.category_id=:cat"; 
    $params[':cat']=$cat; 
}

if ($sizeFilter!=='') {
    $lf = strtolower($sizeFilter);
    if (in_array($lf,['small','medium','large','a4'])){
        if ($lf==='a4'){ $sql.=" AND LOWER(p.frame_size) LIKE :size_like"; $params[':size_like']='%a4%'; }
        else{
            $groupSizes = $frameGroups[$lf]??[];
            if(!empty($groupSizes)){
                $placeholders = [];
                foreach($groupSizes as $i=>$val){ $ph=":fs_{$lf}_{$i}"; $placeholders[]=$ph; $params[$ph]=$val; }
                $sql.=" AND p.frame_size IN (".implode(',',$placeholders).")";
            }
        }
    } else { $sql.=" AND LOWER(p.frame_size)=:size_exact"; $params[':size_exact']=strtolower($sizeFilter); }
}

// --- ORDER BY ---
if ($order==='price_asc') $sql.=" ORDER BY p.price ASC";
elseif ($order==='price_desc') $sql.=" ORDER BY p.price DESC";
else $sql.=" ORDER BY p.created_at DESC";

$stmt=$pdo->prepare($sql);
$stmt->execute($params);
$products=$stmt->fetchAll(PDO::FETCH_ASSOC);
$totalProducts=count($products);

$baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'];

include 'header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- BACK TO HOME -->
<section class="py-8 bg-white border-b">
  <div class="container mx-auto px-6 md:px-12 text-center">
    <a href="index.php" 
       class="inline-block px-8 py-4 bg-orange-600 text-white font-bold text-lg rounded-2xl shadow-lg hover:bg-orange-700 transition">
        Back to Home
    </a>
  </div>
</section>

<!-- TOTAL PRODUCTS -->
<section class="py-4 bg-gray-50 border-b">
  <div class="container mx-auto px-3 md:px-6 text-center">
    <h2 class="text-lg text-gray-400">Total Products: <?= $totalProducts ?></h2>
  </div>
</section>

<!-- SEARCH & FILTER -->
<section class="py-8 bg-gray-50">
  <div class="container mx-auto px-6 md:px-12">
    <form method="get" class="flex flex-wrap gap-3 items-center justify-center md:justify-start">
      <input name="q" value="<?= htmlentities($search) ?>" placeholder="Search products..." class="p-3 border rounded-xl flex-1">
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
            <?php foreach($distinctSizes as $ds): ?>
              <option value="<?= htmlspecialchars($ds) ?>" <?= strtolower($sizeFilter)==strtolower($ds) ? 'selected' : '' ?>>
                <?= htmlspecialchars($ds) ?>
              </option>
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
<section class="py-12 bg-white">
  <div class="container mx-auto px-6 md:px-12">
    <?php if($totalProducts>0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
      <?php foreach($products as $p):
        $img = $p['image'] ?: 'uploads/placeholder.jpg';
        $fullLink = $baseUrl."/product.php?id=".$p['id'];
      ?>
      <div class="bg-white rounded-3xl shadow-lg overflow-hidden flex flex-col hover:shadow-2xl transition relative">
        <div class="relative cursor-pointer productImageWrapper">
          <img src="<?= htmlspecialchars($img) ?>" 
               alt="<?= htmlentities($p['name']) ?>" 
               class="w-full h-56 object-cover product-image"
               data-full="<?= htmlspecialchars($img) ?>">
        </div>

        <div class="p-5 flex flex-col flex-1">
          <h3 class="font-semibold text-lg"><?= htmlentities($p['name']) ?></h3>
          <p class="text-gray-500 text-sm mb-2"><?= htmlentities($p['short_desc']) ?></p>

          <?php if($p['frame_size']): ?>
            <p class="text-sm text-gray-600 mb-2">Frame: 
              <span class="font-medium"><?= htmlentities($p['frame_size']) ?></span>
            </p>
          <?php endif; ?>

          <span class="font-bold text-orange-600 text-xl mb-3">Rs <?= number_format($p['price'],2) ?></span>

          <!-- ACTION BUTTONS -->
          <div class="flex gap-2 items-center mt-auto mb-2">
            <button class="addCartBtn flex-1 py-2 bg-blue-500 text-white rounded-xl hover:bg-blue-600 transition font-bold" 
              data-name="<?= htmlentities($p['name']) ?>" 
              data-price="<?= number_format($p['price'],2) ?>" 
              data-link="<?= $fullLink ?>">
              <i class="fas fa-cart-plus mr-2"></i> Add to Cart
            </button>

            <button class="buyNowBtn flex-1 py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold" 
              data-name="<?= htmlentities($p['name']) ?>" 
              data-price="<?= number_format($p['price'],2) ?>" 
              data-link="<?= $fullLink ?>">
              <i class="fas fa-bolt mr-2"></i> Order Now
            </button>
          </div>

          <!-- SHARE BUTTONS -->
          <div class="flex justify-center gap-4 mt-4 items-center">
            <a href="https://wa.me/?text=<?= urlencode($fullLink) ?>" 
               target="_blank"
               class="w-12 h-12 flex items-center justify-center bg-green-500 text-white rounded-full hover:bg-green-600 transition text-2xl">
               <i class="fab fa-whatsapp"></i>
            </a>

            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($fullLink) ?>" 
               target="_blank"
               class="w-12 h-12 flex items-center justify-center bg-blue-600 text-white rounded-full hover:bg-blue-700 transition text-2xl">
               <i class="fab fa-facebook-f"></i>
            </a>

            <button class="copyLinkBtn w-12 h-12 flex items-center justify-center bg-gray-600 text-white rounded-full hover:bg-gray-700 transition text-2xl" data-link="<?= $fullLink ?>">
              <i class="fas fa-link"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <p class="text-center text-gray-500 text-lg py-20">No products found.</p>
    <?php endif; ?>
  </div>
</section>

<!-- IMAGE LIGHTBOX -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50">
  <span id="closeLightbox" class="absolute top-5 right-5 text-white text-4xl cursor-pointer">&times;</span>
  <img id="lightboxImg" src="" class="max-h-[90%] max-w-[90%] rounded-2xl shadow-xl">
</div>

<?php include 'footer.php'; ?>

<script>
// COPY LINK
document.querySelectorAll('.copyLinkBtn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    navigator.clipboard.writeText(btn.dataset.link).then(()=>{ alert('Link copied!'); });
  });
});

// IMAGE LIGHTBOX
const lightbox = document.getElementById('lightbox');
const lightboxImg = document.getElementById('lightboxImg');
const closeLightbox = document.getElementById('closeLightbox');
document.querySelectorAll('.product-image').forEach(img=>{
  img.addEventListener('click', ()=>{
    lightboxImg.src = img.dataset.full;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
  });
});
closeLightbox?.addEventListener('click', ()=>{
  lightbox.classList.add('hidden');
  lightbox.classList.remove('flex');
});
lightbox?.addEventListener('click', e=>{
  if(e.target === lightbox){
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
  }
});

// MINI CART
let miniCart = JSON.parse(localStorage.getItem('miniCart') || '[]');
document.querySelectorAll('.addCartBtn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    miniCart.push({ name: btn.dataset.name, price: btn.dataset.price, link: btn.dataset.link });
    localStorage.setItem('miniCart', JSON.stringify(miniCart));
    alert(`${btn.dataset.name} added! Total items: ${miniCart.length}`);
  });
});

// BUY NOW → WHATSAPP (Admin Number)
const adminNumber = '<?= $adminNumber ?>';
document.querySelectorAll('.buyNowBtn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const card = btn.closest('div.flex.flex-col');
    let form = card.querySelector('.orderFormContainer');

    if(!form){
      form = document.createElement('div');
      form.className = 'orderFormContainer mt-3';
      form.innerHTML = `
        <input type="text" placeholder="Your Name" class="orderName w-full p-2 border rounded-lg mb-2">
        <input type="text" placeholder="Phone Number" class="orderPhone w-full p-2 border rounded-lg mb-2">
        <input type="text" placeholder="Delivery Address" class="orderAddress w-full p-2 border rounded-lg mb-2">
        <input type="number" min="1" value="1" class="orderQty w-full p-2 border rounded-lg mb-2">
        <button class="sendOrderBtn w-full py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">
          Send via WhatsApp
        </button>
      `;
      card.appendChild(form);

      form.querySelector('.sendOrderBtn').addEventListener('click', ()=>{
        const name = form.querySelector('.orderName').value.trim();
        const phone = form.querySelector('.orderPhone').value.trim();
        const address = form.querySelector('.orderAddress').value.trim();
        const qty = form.querySelector('.orderQty').value;

        if(!name || !phone || !address){
          alert('All fields required');
          return;
        }

        const message = `Hello! I'm interested in this product:
🛍 Product: ${btn.dataset.name}
💰 Price: Rs ${btn.dataset.price}
📦 Quantity: ${qty}
🏠 Address: ${address}
👤 Name: ${name}
📞 Phone: ${phone}
🔗 Link: ${btn.dataset.link}`;

        window.open(`https://wa.me/${adminNumber}?text=${encodeURIComponent(message)}`, '_blank');
      });
    } else { form.classList.toggle('hidden'); }
  });
});
</script>
