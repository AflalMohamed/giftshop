<?php
require 'config.php';

// --- Fetch site settings ---
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll(PDO::FETCH_ASSOC);
$settings = [];
foreach ($settingsRaw as $s) $settings[$s['key_name']] = $s['value_text'];

// --- Variables ---
$siteName = htmlspecialchars($settings['site_name'] ?? 'Gift Shop');
$siteLogo = htmlspecialchars($settings['site_logo'] ?? 'uploads/placeholder.png');
$heroTitle = htmlspecialchars($settings['hero_title'] ?? 'Where Love Meets Sweetness');
$heroSubtitle = htmlspecialchars($settings['hero_subtitle'] ?? 'Adorable Gifts to Brighten Anyone’s Day');
$adminWhatsapp = preg_replace("/\D/", "", $settings['footer_whatsapp'] ?? '94703720960');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= $siteName ?></title>

<!-- Favicon -->
<link rel="icon" type="image/png" sizes="16x16" href="https://nskgifts.online/uploads/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="https://nskgifts.online/uploads/favicon-32x32.png">
<link rel="apple-touch-icon" href="https://nskgifts.online/uploads/apple-touch-icon.png">
<link rel="icon" type="image/x-icon" href="https://nskgifts.online/favicon.ico">
<meta name="theme-color" content="#ffffff">
        
        <!-- JSON-LD Logo Schema -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Organization",
  "name": "<?= $siteName ?>",
  "url": "https://nskgifts.online",
  "logo": "https://nskgifts.online/<?= $siteLogo ?>"
}
</script>

<!-- Google tag -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-CLBSNJ1QXX"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-CLBSNJ1QXX');
</script>

<!-- Tailwind -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
</head>
<body class="bg-white text-gray-800 font-sans">

<!-- HEADER -->
<header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md shadow-md">
  <div class="container mx-auto px-6 md:px-12 flex items-center justify-between h-20">

    <!-- LOGO -->
    <div class="flex items-center gap-3 cursor-pointer">
      <img id="siteLogo" src="<?= $siteLogo ?>" alt="Logo" class="h-10 w-10 rounded object-cover">
      <span id="siteName" class="text-2xl font-bold text-orange-700 cursor-pointer"><?= $siteName ?></span>
    </div>

    <!-- Desktop nav + cart -->
    <div class="hidden md:flex items-center gap-6">
      <!-- Navigation -->
      <nav class="flex gap-8 font-medium items-center">
        <a href="#products" class="hover:text-orange-700">Products</a>
        <a href="#footer" class="hover:text-orange-700">Contact</a>
        <a href="embroidery.php" class="hover:text-orange-700">Embroidery</a>
      </nav>
      <!-- Cart Icon -->
      <div class="relative">
        <button id="cartBtn" class="text-2xl relative">
          <i class="fa-solid fa-cart-shopping"></i>
          <span id="cartCount" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">0</span>
        </button>
        <div id="cartDropdown" class="hidden absolute right-0 mt-2 w-80 md:w-96 bg-white shadow-lg rounded-xl p-4 z-50">
          <h4 class="font-bold mb-2">Cart Items</h4>
          <div id="cartItems" class="flex flex-col gap-2 max-h-64 overflow-y-auto"></div>
          <div id="cartEmpty" class="text-gray-500 text-center py-6">No items in cart.</div>
          <div class="mt-3 font-bold flex justify-between">
            <span>Total:</span>
            <span id="cartTotal">Rs 0.00</span>
          </div>
          <div class="mt-4 flex flex-col gap-2">
            <input id="userName" type="text" placeholder="Name" class="border px-3 py-2 rounded">
            <input id="userPhone" type="text" placeholder="Phone" class="border px-3 py-2 rounded">
            <input id="userAddress" type="text" placeholder="Address" class="border px-3 py-2 rounded">
          </div>
          <button id="checkoutBtn" class="mt-3 w-full py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">Order via WhatsApp</button>
        </div>
      </div>
    </div>

    <!-- Mobile hamburger + cart -->
    <div class="flex items-center gap-4 md:hidden">
      <!-- Cart Icon (mobile) -->
      <div class="relative">
        <button id="cartBtnMobile" class="text-2xl relative">
          <i class="fa-solid fa-cart-shopping"></i>
          <span id="cartCountMobile" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full text-xs w-5 h-5 flex items-center justify-center">0</span>
        </button>
        <div id="cartDropdownMobile" class="hidden absolute right-0 mt-2 w-80 bg-white shadow-lg rounded-xl p-4 z-50">
          <h4 class="font-bold mb-2">Cart Items</h4>
          <div id="cartItemsMobile" class="flex flex-col gap-2 max-h-64 overflow-y-auto"></div>
          <div id="cartEmptyMobile" class="text-gray-500 text-center py-6">No items in cart.</div>
          <div class="mt-3 font-bold flex justify-between">
            <span>Total:</span>
            <span id="cartTotalMobile">Rs 0.00</span>
          </div>
          <div class="mt-4 flex flex-col gap-2">
            <input id="userNameMobile" type="text" placeholder="Name" class="border px-3 py-2 rounded">
            <input id="userPhoneMobile" type="text" placeholder="Phone" class="border px-3 py-2 rounded">
            <input id="userAddressMobile" type="text" placeholder="Address" class="border px-3 py-2 rounded">
          </div>
          <button id="checkoutBtnMobile" class="mt-3 w-full py-2 bg-green-500 text-white rounded-xl hover:bg-green-600 transition font-bold">Order via WhatsApp</button>
        </div>
      </div>
      <!-- Hamburger -->
      <button id="mobileMenuBtn" class="text-2xl">
        <i class="fa-solid fa-bars"></i>
      </button>
    </div>

  </div>

  <!-- Mobile Nav Menu -->
  <nav id="mobileMenu" class="hidden md:hidden bg-white shadow-md absolute top-full left-0 w-full z-40">
    <div class="flex flex-col py-2">
      <a href="#products" class="px-6 py-3 border-b hover:bg-orange-50">Products</a>
      <a href="#footer" class="px-6 py-3 border-b hover:bg-orange-50">Contact</a>
      <a href="embroidery.php" class="px-6 py-3 hover:bg-orange-50">Embroidery</a>
    </div>
  </nav>
</header>

<div class="h-20"></div>

<!-- HERO -->
<section class="py-20 bg-gradient-to-r from-orange-50 to-white text-center">
  <h1 class="text-4xl md:text-6xl font-extrabold mb-6"><?= $heroTitle ?></h1>
  <p class="text-lg md:text-xl text-gray-700"><?= $heroSubtitle ?></p>
</section>

<!-- LOGO LIGHTBOX -->
<div id="logoLightbox" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
  <img id="logoPreview" src="" class="max-w-[80%] max-h-[80%] rounded-xl shadow-2xl">
  <button id="logoClose" class="absolute top-6 right-6 text-white text-3xl">&times;</button>
</div>

<script>
document.addEventListener("DOMContentLoaded",()=>{

  // CART DATA
  let miniCart=JSON.parse(localStorage.getItem('miniCart')||'[]');
  const parsePrice=price=>parseFloat(price.toString().replace(/,/g,''))||0;

  function renderCart(dropdownId,countId,totalId,itemsId,emptyId){
    const dropdown=document.getElementById(dropdownId);
    const items=document.getElementById(itemsId);
    const empty=document.getElementById(emptyId);
    const totalElem=document.getElementById(totalId);
    const count=document.getElementById(countId);
    items.innerHTML='';
    if(miniCart.length===0){ empty.style.display='block'; totalElem.textContent='Rs 0.00'; count.textContent='0'; return; }
    empty.style.display='none';
    count.textContent=miniCart.reduce((acc,i)=>acc+parseInt(i.qty||1),0);
    let total=0;
    miniCart.forEach((item,index)=>{
      const price=parsePrice(item.price);
      const qty=parseInt(item.qty)||1;
      total+=price*qty;
      const div=document.createElement('div');
      div.className="flex justify-between items-center border-b pb-2";
      div.innerHTML=`<span class="font-medium">${item.name} x${qty}</span>
        <div class="flex items-center gap-2">
          <span>Rs ${ (price*qty).toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2}) }</span>
          <button data-index="${index}" class="text-red-500 hover:text-red-700 removeBtn"><i class="fa-solid fa-trash"></i></button>
        </div>`;
      items.appendChild(div);
    });
    totalElem.textContent='Rs '+total.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2});
    dropdown.querySelectorAll('.removeBtn').forEach(btn=>{
      btn.addEventListener('click',()=>{
        miniCart.splice(btn.dataset.index,1);
        localStorage.setItem('miniCart',JSON.stringify(miniCart));
        renderCart(dropdownId,countId,totalId,itemsId,emptyId);
      });
    });
  }

  // INITIAL RENDER
  renderCart('cartDropdown','cartCount','cartTotal','cartItems','cartEmpty');
  renderCart('cartDropdownMobile','cartCountMobile','cartTotalMobile','cartItemsMobile','cartEmptyMobile');

  // CART TOGGLE
  document.getElementById('cartBtn').addEventListener('click',()=>document.getElementById('cartDropdown').classList.toggle('hidden'));
  document.getElementById('cartBtnMobile').addEventListener('click',()=>document.getElementById('cartDropdownMobile').classList.toggle('hidden'));

  // CHECKOUT
  function checkout(dropdownName){
    if(miniCart.length===0){ alert("Cart is empty!"); return; }
    const name=document.getElementById('userName'+dropdownName)?.value||document.getElementById('userName')?.value||'N/A';
    const phone=document.getElementById('userPhone'+dropdownName)?.value||document.getElementById('userPhone')?.value||'N/A';
    const address=document.getElementById('userAddress'+dropdownName)?.value||document.getElementById('userAddress')?.value||'N/A';
    let total=0;
    let msg=`Hello! I'm interested in these products:\n`;
    miniCart.forEach(item=>{
      const price=parsePrice(item.price);
      const qty=parseInt(item.qty)||1;
      total+=price*qty;
      msg+=`🛍 Product: ${item.name}\n💰 Price: Rs ${price.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}\n📦 Quantity: ${qty}\n🔗 Link: ${item.link||'N/A'}\n\n`;
    });
    msg+=`🏠 Address: ${address}\n👤 Name: ${name}\n📞 Phone: ${phone}\n💵 Total: Rs ${total.toLocaleString('en-US',{minimumFractionDigits:2,maximumFractionDigits:2})}`;
    window.open(`https://wa.me/<?= $adminWhatsapp ?>?text=${encodeURIComponent(msg)}`,"_blank");
  }

  document.getElementById('checkoutBtn').addEventListener('click',()=>checkout(''));
  document.getElementById('checkoutBtnMobile').addEventListener('click',()=>checkout('Mobile'));

  // ADD TO CART BUTTONS
  document.querySelectorAll('.addCartBtn').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const name=btn.dataset.name; const price=btn.dataset.price; const link=btn.dataset.link;
      const existing=miniCart.find(i=>i.name===name);
      if(existing){ existing.qty=(existing.qty||1)+1; }
      else{ miniCart.push({name,price,link,qty:1}); }
      localStorage.setItem('miniCart',JSON.stringify(miniCart));
      renderCart('cartDropdown','cartCount','cartTotal','cartItems','cartEmpty');
      renderCart('cartDropdownMobile','cartCountMobile','cartTotalMobile','cartItemsMobile','cartEmptyMobile');
      alert(`${name} added to cart!`);
    });
  });

  // MOBILE MENU
  const mobileMenuBtn=document.getElementById("mobileMenuBtn");
  const mobileMenu=document.getElementById("mobileMenu");
  mobileMenuBtn.addEventListener("click",()=>{
    mobileMenu.classList.toggle("hidden");
    const icon=mobileMenuBtn.querySelector("i");
    icon.classList.toggle("fa-bars");
    icon.classList.toggle("fa-xmark");
  });

  // LOGO LIGHTBOX
  const logo=document.getElementById("siteLogo");
  const lightbox=document.getElementById("logoLightbox");
  const preview=document.getElementById("logoPreview");
  const close=document.getElementById("logoClose");
  logo.addEventListener("click",()=>{
    preview.src=logo.src;
    lightbox.classList.remove("hidden");
    lightbox.classList.add("flex");
  });
  close.addEventListener("click",()=>{ lightbox.classList.add("hidden"); lightbox.classList.remove("flex"); });
  lightbox.addEventListener("click",e=>{ if(e.target===lightbox){ lightbox.classList.add("hidden"); lightbox.classList.remove("flex"); }});

  // SITE NAME → SCROLL TOP + REFRESH
  const siteName=document.getElementById("siteName");
  siteName.addEventListener("click",()=>{
    window.scrollTo({top:0,behavior:"smooth"});
    setTimeout(()=>location.reload(),300);
  });

});
</script>

</body>
</html>
