<?php
require 'config.php';

// --- FETCH CATEGORIES ---
$cats = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

// --- SITE SETTINGS ---
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll();
$settings = [];
foreach ($settingsRaw as $s) $settings[$s['key_name']] = $s['value_text'];

// --- USER INPUTS ---
$search = trim($_GET['q'] ?? '');
$cat = isset($_GET['cat']) ? (int)$_GET['cat'] : 0;
$order = $_GET['order'] ?? 'new';
$sizeFilter = trim($_GET['size'] ?? '');

// --- FRAME SIZE GROUPS ---
$frameGroups = [
    'small' => ['4x6','5x7','8x10'],
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
$distinctSizes = array_map(fn($r) => $r['frame_size'], $sizeStmt->fetchAll(PDO::FETCH_ASSOC));

// --- MAIN PRODUCT QUERY ---
$sql = "SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_active = 1";
$params = [];

// SEARCH: product name, category name, short_desc, description
if ($search !== '') {
    $sql .= " AND (
        p.name LIKE :s1 OR 
        c.name LIKE :s2 OR 
        p.short_desc LIKE :s3 OR 
        p.description LIKE :s4
    )";
    $params[':s1'] = "%$search%";
    $params[':s2'] = "%$search%";
    $params[':s3'] = "%$search%";
    $params[':s4'] = "%$search%";
}

// CATEGORY FILTER
if ($cat) {
    $sql .= " AND p.category_id = :cat";
    $params[':cat'] = $cat;
}

// SIZE FILTER
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
                foreach ($groupSizes as $i => $val) {
                    $ph = ":fs_{$lf}_{$i}";
                    $placeholders[] = $ph;
                    $params[$ph] = $val;
                }
                $sql .= " AND p.frame_size IN (" . implode(',', $placeholders) . ")";
            }
        }
    } else {
        $sql .= " AND LOWER(p.frame_size) = :size_exact";
        $params[':size_exact'] = strtolower($sizeFilter);
    }
}

// ORDER
if ($order === 'price_asc') $sql .= " ORDER BY p.price ASC";
elseif ($order === 'price_desc') $sql .= " ORDER BY p.price DESC";
else $sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// WhatsApp admin
$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960';

// HEADER
include 'header.php';
?>

<!-- PAGE HEADER -->
<section class="py-12 bg-orange-50">
  <div class="container mx-auto px-6 md:px-12 text-center">
    <!-- Stylish Back to Home Button -->
    <a href="index.php" class="inline-block mb-6 px-8 py-3 font-semibold text-white rounded-full shadow-lg bg-gradient-to-r from-orange-500 to-yellow-400 hover:from-yellow-400 hover:to-orange-500 transition-all transform hover:-translate-y-1 hover:scale-105">
      &larr; Back to Home
    </a>

    <h1 class="text-5xl font-bold text-orange-600 mb-4">All Products</h1>
    <p class="text-gray-600 text-lg">Browse all products, search, filter, and order easily.</p>
  </div>
</section>

<!-- SEARCH & FILTER -->
<section class="py-8 bg-gray-50">
  <div class="container mx-auto px-6 md:px-12 flex flex-col md:flex-row gap-4 justify-center md:justify-start">
    <form id="searchForm" method="get" class="flex flex-wrap gap-3 w-full md:w-auto items-center">
      <input name="q" value="<?= htmlentities($search) ?>" placeholder="Search products..." class="p-3 border rounded-xl flex-1 focus:ring focus:ring-orange-300">

      <select name="cat" class="p-3 border rounded-xl">
        <option value="0">All categories</option>
        <?php foreach($cats as $c): ?>
          <option value="<?= $c['id'] ?>" <?= $cat==$c['id']?'selected':'' ?>><?= htmlentities($c['name']) ?></option>
        <?php endforeach; ?>
      </select>

      <!-- Size filter -->
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

<!-- ALL PRODUCTS -->
<section class="py-16 bg-gray-50">
  <div class="container mx-auto px-6 md:px-12">
    <?php if(count($products) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php foreach($products as $p):
          $img = $p['image'] ?: 'uploads/placeholder.jpg';
          $product_link = "product.php?id={$p['id']}";
        ?>
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-1 flex flex-col">
          <img src="<?= htmlspecialchars($img) ?>" alt="<?= htmlentities($p['name']) ?>" class="w-full h-52 object-cover cursor-pointer product-image" data-full="<?= htmlspecialchars($img) ?>">
          <div class="p-5 flex flex-col gap-2 flex-1">
            <h3 class="font-semibold text-lg"><?= htmlentities($p['name']) ?></h3>
            <p class="text-gray-500 text-sm"><?= htmlentities($p['short_desc']) ?></p>
            <?php if($p['frame_size']): ?>
              <p class="text-sm text-gray-600">Frame: <span class="font-medium"><?= htmlentities($p['frame_size']) ?></span></p>
            <?php endif; ?>
            <span class="font-bold text-orange-600 text-xl">Rs <?= number_format($p['price'],2) ?></span>

            <form class="space-y-2 orderForm mt-auto"
                  data-product="<?= htmlentities($p['name']) ?>"
                  data-price="<?= number_format($p['price'],2) ?>"
                  data-link="<?= $product_link ?>">
              <input type="text" name="customer_name" placeholder="Your Name" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="text" name="customer_phone" placeholder="Phone" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="text" name="customer_address" placeholder="Delivery Address" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="number" name="quantity" min="1" value="1" class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <button type="submit" class="w-full py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">
                  Buy via WhatsApp
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
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

<style>
.scrollbar-hide::-webkit-scrollbar { display: none; }
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
</style>

<script>
// WhatsApp Order Form
document.querySelectorAll('.orderForm').forEach(form => {
  form.addEventListener('submit', function(e) {
      e.preventDefault();
      const product = this.dataset.product;
      const price = this.dataset.price;
      const link = this.dataset.link;
      const name = this.customer_name.value.trim();
      const phone = this.customer_phone.value.trim();
      const address = this.customer_address.value.trim();
      const quantity = this.quantity.value;
      if(!name || !phone || !address) { alert('Please fill all fields'); return; }
      const message = `Hello! I'm interested in this product:\n🛍 ${product}\n💰 Rs ${price}\n📦 Quantity: ${quantity}\n🏠 ${address}\n👤 ${name}\n📞 ${phone}\n🔗 ${link}`;
      window.open(`https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>?text=`+encodeURIComponent(message), '_blank');
  });
});

// Lightbox
const lightbox=document.getElementById('lightbox');
const lightboxImage=document.getElementById('lightboxImage');
const lightboxClose=document.getElementById('lightboxClose');
document.querySelectorAll('.product-image').forEach(img=>{
  img.addEventListener('click',()=>{lightboxImage.src=img.dataset.full;lightbox.classList.remove('hidden');lightbox.classList.add('flex');});
});
lightboxClose.addEventListener('click',()=>{lightbox.classList.add('hidden');lightbox.classList.remove('flex');});
lightbox.addEventListener('click',e=>{if(e.target===lightbox){lightbox.classList.add('hidden');lightbox.classList.remove('flex');}});

// Preserve anchor on search
document.getElementById('searchForm').addEventListener('submit',function(e){
  e.preventDefault();
  const params=new URLSearchParams(new FormData(this));
  window.location.href=window.location.pathname+'?'+params.toString();
});
</script>

<?php include 'footer.php'; ?>
