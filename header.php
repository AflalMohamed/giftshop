<?php
require 'config.php';

// Fetch site settings
$settingsRaw = $pdo->query("SELECT key_name, value_text FROM site_settings")->fetchAll();
$settings = [];
foreach ($settingsRaw as $s) {
    $settings[$s['key_name']] = $s['value_text'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($settings['hero_title'] ?? 'Gift Shop') ?></title>

<!-- Tailwind CSS CDN (okay for dev) -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome Free CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

</head>
<body class="bg-white text-gray-800 font-sans">

<!-- Header -->
<header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md shadow-md">
  <div class="container mx-auto px-6 md:px-12 flex items-center justify-between h-20">

    <!-- Logo & Name -->
    <div class="flex items-center gap-3">
      <!-- Logo (click preview) -->
      <img id="siteLogo" src="<?= htmlspecialchars($settings['site_logo'] ?? 'uploads/placeholder.png') ?>" alt="Logo" class="h-10 w-10 object-cover rounded cursor-pointer" title="Click to preview">
      <!-- Site name (click reload) -->
      <span id="siteName" class="text-2xl font-bold text-orange-700 cursor-pointer"><?= htmlspecialchars($settings['site_name'] ?? 'Gift Shop') ?></span>
    </div>

    <!-- Desktop Navigation -->
    <nav class="hidden md:flex gap-6 font-medium text-gray-800">
      <a href="#products" class="hover:text-orange-700 transition">Products</a>
      <a href="#cta" class="hover:text-orange-700 transition">Shop</a>
      <a href="#footer" class="hover:text-orange-700 transition">Contact</a>
      <a href="embroidery.php" class="hover:text-orange-700 transition">Embroidery</a>
    </nav>

    <!-- Mobile Hamburger -->
    <div class="md:hidden">
      <button id="mobileMenuBtn" class="text-gray-800 focus:outline-none">
        <i class="fas fa-bars text-2xl"></i>
      </button>
    </div>

  </div>

  <!-- Mobile Menu -->
  <nav id="mobileMenu" class="hidden md:hidden absolute top-full left-0 w-full bg-white shadow-md z-50 transition-all duration-300">
    <div class="flex flex-col py-2">
      <a href="#products" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Products</a>
      <a href="#cta" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Shop</a>
      <a href="#footer" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Contact</a>
      <a href="embroidery.php" class="block px-6 py-3 hover:bg-orange-50 transition">Embroidery</a>
    </div>
  </nav>
</header>

<!-- Spacer -->
<div class="h-20 md:h-20 lg:h-24"></div>

<!-- Page Hero -->
<section class="py-20 bg-gradient-to-r from-orange-50 to-white text-center">
  <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold text-gray-900 mb-6"><?= htmlspecialchars($settings['hero_title'] ?? 'Where Love Meets Sweetness') ?></h1>
  <p class="text-base sm:text-lg md:text-xl text-gray-700 mb-8"><?= htmlspecialchars($settings['hero_subtitle'] ?? 'Adorable Gifts to Brighten Anyone’s Day') ?></p>
</section>

<!-- Logo Lightbox -->
<div id="logoLightbox" class="fixed inset-0 bg-black/80 hidden items-center justify-center z-50">
  <img id="logoPreview" src="" alt="Logo Preview" class="max-h-[80%] max-w-[80%] rounded-xl shadow-2xl">
  <button id="logoClose" class="absolute top-6 right-6 text-white text-3xl font-bold hover:text-orange-400">&times;</button>
</div>

<!-- Scripts -->
<script>
document.addEventListener("DOMContentLoaded", () => {
  // Mobile menu toggle
  const mobileBtn = document.getElementById('mobileMenuBtn');
  const mobileMenu = document.getElementById('mobileMenu');
  mobileBtn.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      const icon = mobileBtn.querySelector('i');
      icon.classList.toggle('fa-bars');
      icon.classList.toggle('fa-xmark');
  });

  // Logo preview lightbox
  const logo = document.getElementById('siteLogo');
  const lightbox = document.getElementById('logoLightbox');
  const preview = document.getElementById('logoPreview');
  const closeBtn = document.getElementById('logoClose');

  logo.addEventListener('click', () => {
    preview.src = logo.src;
    lightbox.classList.remove('hidden');
    lightbox.classList.add('flex');
  });

  closeBtn.addEventListener('click', () => {
    lightbox.classList.add('hidden');
    lightbox.classList.remove('flex');
  });

  lightbox.addEventListener('click', e => {
    if(e.target === lightbox) {
      lightbox.classList.add('hidden');
      lightbox.classList.remove('flex');
    }
  });

  // Site name refresh
  document.getElementById('siteName').addEventListener('click', () => {
    location.reload();
  });
});
</script>

</body>
</html>
