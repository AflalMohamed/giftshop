<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>NSK Embroidery - Where Love Meets Sweetness</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <style>
    /* Hide Scrollbar */
    .scrollbar-hide::-webkit-scrollbar { display: none; }
    .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
  </style>
</head>
<body class="bg-white text-gray-800 font-sans">

  <!-- Header -->
  <header class="fixed top-0 left-0 w-full z-50 bg-white/95 backdrop-blur-md shadow-md">
    <div class="container mx-auto px-6 md:px-12 flex items-center justify-between h-20">

      <!-- Logo & Site Name -->
      <div class="flex items-center gap-3">
        <img id="headerLogo" src="uploads/aari logo.png" alt="NSK Embroidery Logo" class="h-10 w-10 object-cover rounded cursor-pointer" title="Click to preview logo">
        <span id="headerName" class="text-2xl font-bold text-orange-700 cursor-pointer" title="Click to refresh page">NSK Embroidery</span>
      </div>

      <!-- Desktop Navigation -->
      <nav class="hidden md:flex gap-6 font-medium text-gray-800">
        <a href="index.php" class="hover:text-orange-700 transition">Gift Home</a>
        <a href="#products" class="hover:text-orange-700 transition">Products</a>
        <a href="#cta" class="hover:text-orange-700 transition">Shop</a>
        <a href="#footer" class="hover:text-orange-700 transition">Contact</a>
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
        <a href="index.php" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Gift Home</a>
        <a href="#products" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Products</a>
        <a href="#cta" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Shop</a>
        <a href="#footer" class="block px-6 py-3 border-b border-gray-200 hover:bg-orange-50 transition">Contact</a>
      </div>
    </nav>
  </header>

  <!-- Spacer for Fixed Header -->
  <div class="h-20 md:h-20 lg:h-24"></div>

  <!-- Logo Preview Lightbox -->
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

      // Logo lightbox
      const headerLogo = document.getElementById('headerLogo');
      const lightbox = document.getElementById('logoLightbox');
      const preview = document.getElementById('logoPreview');
      const closeBtn = document.getElementById('logoClose');

      headerLogo.addEventListener('click', () => {
        preview.src = headerLogo.src;
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
      document.getElementById('headerName').addEventListener('click', () => {
        location.reload();
      });
    });
  </script>

</body>
</html>
