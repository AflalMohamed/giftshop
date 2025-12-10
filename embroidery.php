<?php
require 'config.php';

// Fetch designs
$designs = $pdo->query("SELECT * FROM embroidery_designs WHERE is_active=1 ORDER BY created_at DESC LIMIT 12")->fetchAll();
$totalDesigns = count($designs); // Total number of designs
$adminWhatsapp = $settings['footer_whatsapp'] ?? '94703720960';
include 'header1.php';
?>

<!-- HERO -->
<section class="relative bg-gradient-to-r from-orange-100 to-white py-20">
  <div class="container mx-auto px-6 md:px-12 text-center">
    <h1 class="text-5xl md:text-6xl font-extrabold text-gray-900 mb-6">Beautiful Embroidery Designs</h1>
    <p class="text-lg md:text-xl text-gray-600 mb-8">Discover unique embroidery work and custom designs</p>
    <a href="#designs" class="px-8 py-4 bg-orange-600 text-white rounded-xl font-bold shadow-lg hover:bg-orange-700">
      View Designs
    </a>
  </div>
</section>

<!-- DESIGNS -->
<section class="py-16 bg-gray-50" id="designs">
  <div class="container mx-auto px-6 md:px-12">

    <!-- TOTAL DESIGNS COUNT -->
    <p class="text-center text-gray-700 text-lg mb-8 font-semibold">
      Total Designs: <?= $totalDesigns ?>
    </p>

    <?php if($totalDesigns > 0): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8">

      <?php foreach($designs as $d): 
        $img = $d['image'] ?: 'uploads/placeholder.jpg';
        $productUrl = "embroidery-single.php?id=".$d['id'];
        $absoluteUrl = $_SERVER['REQUEST_SCHEME'] . "://" . $_SERVER['HTTP_HOST'] . "/" . $productUrl;
      ?>

      <div class="bg-white rounded-3xl shadow-lg overflow-hidden hover:shadow-2xl transition flex flex-col">

        <!-- CLICK → SINGLE PAGE -->
        <a href="<?= $productUrl ?>">
          <img src="<?= $img ?>" class="w-full h-52 object-cover" alt="<?= htmlentities($d['name']) ?>">
        </a>

        <div class="p-5 flex flex-col gap-2">

          <h3 class="font-semibold text-lg"><?= htmlentities($d['name']) ?></h3>
          <p class="text-gray-500 text-sm"><?= htmlentities($d['short_desc']) ?></p>

          <?php if($d['price'] > 0): ?>
          <span class="font-bold text-orange-600 text-xl">Rs <?= number_format($d['price'],2) ?></span>
          <?php endif; ?>

          <!-- SHARE BUTTONS -->
          <div class="flex items-center gap-5 mt-2">

            <!-- WhatsApp -->
            <button class="shareWhatsApp" data-link="<?= $absoluteUrl ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.52 3.48A11.86 11.86 0 0 0 12 .15 11.86 11.86 0 0 0 .15 12c0 2.09.54 4.13 1.58 5.94L0 24l6.22-1.63A11.85 11.85 0 0 0 12 23.85a11.86 11.86 0 0 0 11.85-11.85c0-3.18-1.24-6.17-3.33-8.52ZM12 21.7a9.7 9.7 0 0 1-4.96-1.37l-.36-.21-3.69.97.99-3.59-.23-.37a9.73 9.73 0 1 1 8.25 5.57Zm5.2-7.13c-.28-.14-1.64-.8-1.89-.9-.25-.1-.43-.14-.6.14-.17.28-.69.9-.85 1.08-.16.17-.31.19-.59.07-.28-.14-1.18-.43-2.25-1.37-.83-.74-1.39-1.64-1.55-1.92-.16-.28-.02-.43.12-.57.12-.12.28-.31.41-.46.14-.16.19-.28.28-.46.09-.17.05-.32-.02-.46-.07-.14-.6-1.46-.82-2-.22-.53-.44-.45-.6-.46h-.51c-.18 0-.46.07-.7.32-.24.25-.91.89-.91 2.16 0 1.27.93 2.5 1.06 2.67.13.17 1.84 3.01 4.66 4.2 2.82 1.18 2.82.79 3.32.74.5-.05 1.64-.67 1.88-1.32.24-.65.24-1.21.17-1.32-.07-.12-.25-.19-.53-.33Z"/>
              </svg>
            </button>

            <!-- Facebook -->
            <button class="shareFacebook" data-link="<?= $absoluteUrl ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22 12A10 10 0 1 0 10 21.95V14.31H7v-3h3v-2.3c0-3 1.8-4.7 4.6-4.7 1.3 0 2.7.2 2.7.2v3h-1.5c-1.5 0-2 .9-2 1.9v1.9h3.4l-.54 3H13V22A10 10 0 0 0 22 12Z"/>
              </svg>
            </button>

            <!-- Copy link -->
            <button class="copyLink" data-link="<?= $absoluteUrl ?>">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-width="2" d="M8 17H6a5 5 0 1 1 0-10h2m8 0h2a5 5 0 1 1 0 10h-2M8 12h8"/>
              </svg>
            </button>

          </div>

          <!-- SHOW ORDER FORM BUTTON -->
          <button class="showOrderFormBtn mt-3 py-2 px-4 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600">
            Order via WhatsApp
          </button>

          <!-- ORDER FORM (HIDDEN) -->
          <div class="orderFormContainer hidden mt-3">
            <form class="space-y-2 orderForm"
              data-product="<?= htmlentities($d['name']) ?>"
              data-price="<?= number_format($d['price'],2) ?>"
              data-link="<?= $absoluteUrl ?>">

              <input type="text" name="customer_name" placeholder="Your Name" required class="w-full p-2 border rounded-lg">
              <input type="text" name="customer_phone" placeholder="Phone" required class="w-full p-2 border rounded-lg">
              <input type="text" name="customer_address" placeholder="Delivery Address" required class="w-full p-2 border rounded-lg">
              <input type="number" name="quantity" min="1" value="1" class="w-full p-2 border rounded-lg">

              <button class="w-full py-3 bg-green-500 text-white font-bold rounded-xl hover:bg-green-600">
                Send Order via WhatsApp
              </button>
            </form>
          </div>

        </div>
      </div>

      <?php endforeach; ?>
    </div>

    <?php else: ?>
      <p class="text-center text-gray-500 text-lg py-24">No designs found yet.</p>
    <?php endif; ?>
  </div>
</section>

<script>
// SHOW/HIDE ORDER FORM
document.querySelectorAll('.showOrderFormBtn').forEach(btn=>{
  btn.addEventListener('click', ()=>{
    const formContainer = btn.nextElementSibling;
    formContainer.classList.toggle('hidden');
  });
});

// ORDER TO WHATSAPP
document.querySelectorAll('.orderForm').forEach(form => {
  form.addEventListener('submit', e => {
    e.preventDefault();

    let n=form.customer_name.value.trim(),
        p=form.customer_phone.value.trim(),
        a=form.customer_address.value.trim(),
        q=form.quantity.value,
        product=form.dataset.product,
        price=form.dataset.price,
        link=form.dataset.link;

    const msg =
`Hello! I'm interested:
🧵 ${product}
💰 Rs ${price}
📦 Qty: ${q}
🏠 ${a}
👤 ${n}
📞 ${p}
🔗 ${link}`;

    window.open(`https://wa.me/<?= preg_replace('/\D/','',$adminWhatsapp) ?>?text=`+encodeURIComponent(msg));
  });
});

// SHARE BUTTON LOGIC
document.querySelectorAll(".shareWhatsApp").forEach(btn=>{
  btn.onclick=()=>window.open(`https://wa.me/?text=${encodeURIComponent(btn.dataset.link)}`);
});
document.querySelectorAll(".shareFacebook").forEach(btn=>{
  btn.onclick=()=>window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(btn.dataset.link)}`);
});
document.querySelectorAll(".copyLink").forEach(btn=>{
  btn.onclick=()=>{
    navigator.clipboard.writeText(btn.dataset.link);
    alert("Link Copied!");
  };
});
</script>

<?php include 'footer1.php'; ?>
