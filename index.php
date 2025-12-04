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
    'small' => ['4x4','4x6', '5x7', '8x10'], 
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

if ($order === 'price_asc') $sql .= " ORDER BY p.price ASC"; 
elseif ($order === 'price_desc') $sql .= " ORDER BY p.price DESC"; 
else $sql .= " ORDER BY p.created_at DESC"; 

$sql .= " LIMIT 8"; 
$stmt = $pdo->prepare($sql); 
$stmt->execute($params); 
$products = $stmt->fetchAll(PDO::FETCH_ASSOC); 

$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960'; 
include 'header.php'; 
?> 

<!-- HERO --> 
<section class="bg-gradient-to-r from-orange-100 to-white py-20 relative overflow-hidden"> 
  <div class="container mx-auto px-6 md:px-12 flex flex-col-reverse md:flex-row items-center gap-12 relative z-10"> 
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

<!-- FEATURES --> 
<section class="py-16 bg-white relative z-10"> 
  <div class="container mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-center"> 
    <div class="bg-orange-50 p-8 rounded-2xl shadow hover:scale-105 transition transform"> 
      <i class="fas fa-gift text-4xl text-orange-500 mb-4"></i> 
      <h3 class="font-semibold text-xl mb-2">Curated Collections</h3> 
      <p class="text-gray-500 text-sm">Handpicked gifts & cakes for every celebration</p> 
    </div> 
    <div class="bg-orange-50 p-8 rounded-2xl shadow hover:scale-105 transition transform"> 
      <i class="fas fa-truck text-4xl text-orange-500 mb-4"></i> 
      <h3 class="font-semibold text-xl mb-2">Quick Delivery</h3> 
      <p class="text-gray-500 text-sm">Ensure smiles reach your loved ones on time</p> 
    </div> 
    <div class="bg-orange-50 p-8 rounded-2xl shadow hover:scale-105 transition transform"> 
      <i class="fas fa-heart text-4xl text-orange-500 mb-4"></i> 
      <h3 class="font-semibold text-xl mb-2">Personal Touch</h3> 
      <p class="text-gray-500 text-sm">Add custom messages, packaging, and surprises</p> 
    </div> 
  </div> 
</section> 

<!-- SEARCH & FILTER --> 
<section class="py-8 bg-gray-50 relative z-10" id="search-panel"> 
  <div class="container mx-auto px-6 md:px-12"> 
    <form id="searchForm" method="get" class="flex flex-wrap gap-3 items-center"> 
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

<!-- PRODUCTS --> 
<section class="py-16 bg-gray-50 relative z-10" id="products"> 
  <div class="container mx-auto px-6 md:px-12"> 
    <h2 class="text-4xl font-bold mb-8 text-center md:text-left">Today's Gift Picks</h2> 

    <?php if(count($products)>0): ?> 
      <div class="relative"> 
        <button id="scrollLeft" class="absolute left-0 top-1/2 -translate-y-1/2 bg-orange-600 text-white p-3 rounded-full shadow-lg z-10 hover:bg-orange-700 transition">&#8592;</button> 
        <button id="scrollRight" class="absolute right-0 top-1/2 -translate-y-1/2 bg-orange-600 text-white p-3 rounded-full shadow-lg z-10 hover:bg-orange-700 transition">&#8594;</button> 

        <div id="productScroll" class="overflow-x-auto flex gap-6 snap-x snap-mandatory scrollbar-hide scroll-smooth px-12"> 
          <?php foreach($products as $p): 
            $img = $p['image'] ?: 'uploads/placeholder.jpg'; 
            $product_link = "product.php?id={$p['id']}"; 
          ?> 
            <div class="flex-none w-80 bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-1 snap-start flex flex-col"> 
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
      </div> 

      <div class="text-center mt-6"> 
        <a href="all_products.php" class="inline-block px-8 py-3 bg-orange-600 text-white rounded-xl font-bold hover:bg-orange-700 transition shadow-lg"> 
          See More Products 
        </a> 
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

<!-- Snow Canvas --> 
<canvas id="snowCanvas" style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>

<style> 
.scrollbar-hide::-webkit-scrollbar { display: none; } 
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; } 
</style> 

<script>
// --- Scroll Buttons ---
const scrollContainer = document.getElementById('productScroll');
document.getElementById('scrollLeft').addEventListener('click',()=>scrollContainer.scrollBy({left:-300,behavior:'smooth'}));
document.getElementById('scrollRight').addEventListener('click',()=>scrollContainer.scrollBy({left:300,behavior:'smooth'}));

// --- WhatsApp Order Form ---
document.querySelectorAll('.orderForm').forEach(form=>{
  form.addEventListener('submit',function(e){
    e.preventDefault();
    const product=this.dataset.product;
    const price=this.dataset.price;
    const link=this.dataset.link;
    const name=this.customer_name.value.trim();
    const phone=this.customer_phone.value.trim();
    const address=this.customer_address.value.trim();
    const quantity=this.quantity.value;
    if(!name||!phone||!address){alert('Please fill all fields'); return;}
    const message=`Hello! I'm interested in this product:\n🛍 ${product}\n💰 Rs ${price}\n📦 Quantity: ${quantity}\n🏠 ${address}\n👤 ${name}\n📞 ${phone}\n🔗 ${link}`;
    window.open(`https://wa.me/<?= preg_replace('/\D/','',$adminWhatsapp) ?>?text=`+encodeURIComponent(message),'_blank');
  });
});

// --- Lightbox ---
const lightbox=document.getElementById('lightbox');
const lightboxImage=document.getElementById('lightboxImage');
const lightboxClose=document.getElementById('lightboxClose');
let snowActive = true;

document.querySelectorAll('.product-image').forEach(img=>{
  img.addEventListener('click',()=>{
    lightboxImage.src = img.dataset.full;
    lightbox.classList.remove('hidden'); lightbox.classList.add('flex');
    snowActive = false; // stop snow
  });
});

function closeLightbox(){
  lightbox.classList.add('hidden'); lightbox.classList.remove('flex');
  snowActive = true; drawSnow();
}
lightboxClose.addEventListener('click', closeLightbox);
lightbox.addEventListener('click', e=>{ if(e.target===lightbox) closeLightbox(); });

// --- Preserve Anchor ---
document.getElementById('searchForm').addEventListener('submit',function(e){
  e.preventDefault();
  const params=new URLSearchParams(new FormData(this));
  window.location.href=window.location.pathname+'?'+params.toString()+'#products';
});

// --- Snow Effect ---
const canvas = document.getElementById('snowCanvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth; canvas.height = window.innerHeight;
const particles=[];
for(let i=0;i<100;i++){ // fewer particles = lighter snow
  particles.push({x:Math.random()*canvas.width, y:Math.random()*canvas.height, r:Math.random()*2+1, d:Math.random()*2});
}

function drawSnow(){
  ctx.clearRect(0,0,canvas.width,canvas.height);
  if(!snowActive) return;
  ctx.fillStyle="white";
  ctx.beginPath();
  for(let p of particles){
    ctx.moveTo(p.x,p.y);
    ctx.arc(p.x,p.y,p.r,0,Math.PI*2,true);
  }
  ctx.fill();
  updateSnow();
  if(snowActive) requestAnimationFrame(drawSnow);
}

function updateSnow(){
  for(let p of particles){
    p.y += Math.cos(p.d)+0.3; // slower
    p.x += Math.sin(p.d)*0.3;
    if(p.y>canvas.height){ p.x=Math.random()*canvas.width; p.y=0; }
  }
}

drawSnow();
window.addEventListener('resize',()=>{canvas.width=window.innerWidth;canvas.height=window.innerHeight;});
</script>

<?php include 'footer.php'; ?>
