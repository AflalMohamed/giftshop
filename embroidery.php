<?php
require 'config.php';

// Fetch designs
$designs = $pdo->query("SELECT * FROM embroidery_designs WHERE is_active=1 ORDER BY created_at DESC LIMIT 12")->fetchAll();

$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960';

include 'header1.php';
?>

<!-- Hero Section -->
<section class="relative bg-gradient-to-r from-orange-100 to-white py-20">
  <div class="container mx-auto px-6 md:px-12 text-center">
    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6 leading-tight">Beautiful Embroidery Designs</h1>
    <p class="text-lg md:text-xl text-gray-600 mb-8">Discover unique embroidery work and custom designs for your needs</p>
    <a href="#designs" class="inline-block px-8 py-4 bg-orange-600 text-white rounded-xl font-bold shadow-lg hover:bg-orange-700 transition">View Designs</a>
  </div>
</section>

<!-- Embroidery Designs -->
<section class="py-16 bg-gray-50" id="designs">
  <div class="container mx-auto px-6 md:px-12">
    <?php if(count($designs) > 0): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">
        <?php foreach($designs as $d): 
          $img = $d['image'] ?: 'uploads/placeholder.jpg';
        ?>
        <div class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-1 flex flex-col">
          <img src="<?= htmlentities($img) ?>" alt="<?= htmlentities($d['name']) ?>" class="w-full h-52 object-cover cursor-pointer design-image" data-full="<?= htmlentities($img) ?>">
          <div class="p-5 flex flex-col gap-2 flex-1">
            <h3 class="font-semibold text-lg"><?= htmlentities($d['name']) ?></h3>
            <p class="text-gray-500 text-sm"><?= htmlentities($d['short_desc']) ?></p>
            <?php if($d['price'] > 0): ?>
              <span class="font-bold text-orange-600 text-xl">Rs <?= number_format($d['price'],2) ?></span>
            <?php endif; ?>
            <form class="space-y-2 orderForm mt-auto" 
                  data-product="<?= htmlentities($d['name']) ?>" 
                  data-price="<?= number_format($d['price'],2) ?>" 
                  data-link="embroidery.php?id=<?= $d['id'] ?>">
              <input type="text" name="customer_name" placeholder="Your Name" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="text" name="customer_phone" placeholder="Phone" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="text" name="customer_address" placeholder="Delivery Address" required class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <input type="number" name="quantity" min="1" value="1" class="w-full p-2 border rounded-lg focus:ring focus:ring-orange-300">
              <button type="submit" class="w-full py-3 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">
                  Order via WhatsApp
              </button>
            </form>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    <?php else: ?>
      <p class="text-center text-gray-500 text-lg py-20">No embroidery designs found yet.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Lightbox -->
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

      const message = `Hello! I'm interested in this embroidery design:\n🧵 ${product}\n💰 Rs ${price}\n📦 Quantity: ${quantity}\n🏠 ${address}\n👤 ${name}\n📞 ${phone}\n🔗 ${link}`;
      window.open(`https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>?text=`+encodeURIComponent(message), '_blank');
  });
});

// Lightbox
const lightbox = document.getElementById('lightbox');
const lightboxImage = document.getElementById('lightboxImage');
const lightboxClose = document.getElementById('lightboxClose');
document.querySelectorAll('.design-image').forEach(img => {
  img.addEventListener('click', () => {
      lightboxImage.src = img.dataset.full;
      lightbox.classList.remove('hidden');
      lightbox.classList.add('flex');
  });
});
lightboxClose.addEventListener('click', () => {
  lightbox.classList.add('hidden');
  lightbox.classList.remove('flex');
});
lightbox.addEventListener('click', e => { if(e.target === lightbox) { lightbox.classList.add('hidden'); lightbox.classList.remove('flex'); } });
</script>

<?php include 'footer1.php'; ?>
