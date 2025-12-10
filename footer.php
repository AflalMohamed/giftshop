<?php
require 'config.php';
$adminWhatsapp = $settings['footer_whatsapp'] ?? '94700000000';
?>

<!-- ===================== MODERN TRENDING FOOTER ===================== -->
<footer class="relative bg-gradient-to-b from-orange-100 via-orange-50 to-white pt-16 pb-10 border-t border-orange-200">

    <div class="container mx-auto px-6 md:px-12 grid grid-cols-1 md:grid-cols-3 gap-10">

        <!-- Brand Section -->
        <div class="space-y-3 text-center md:text-left">
            <h2 class="text-4xl font-extrabold bg-gradient-to-r from-orange-600 to-red-500 bg-clip-text text-transparent tracking-wide drop-shadow">
                <?= htmlspecialchars($settings['site_name'] ?? 'Gift Shop') ?>
            </h2>
            <p class="text-gray-600 text-sm md:text-base leading-relaxed">
                <?= htmlspecialchars($settings['site_subtitle'] ?? '') ?>
            </p>
        </div>

        <!-- Social Media Buttons -->
        <div class="flex items-center justify-center md:justify-start gap-4">

            <!-- Instagram -->
            <?php if(!empty($settings['footer_instagram'])): ?>
            <a href="<?= $settings['footer_instagram'] ?>" target="_blank"
               class="w-12 h-12 rounded-2xl bg-white/60 backdrop-blur-xl shadow-md flex items-center justify-center
                      hover:scale-110 transition duration-300 text-pink-500 hover:text-white hover:bg-pink-500">
                <i class="bi bi-instagram text-2xl"></i>
            </a>
            <?php endif; ?>

            <!-- WhatsApp -->
            <a href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>" target="_blank"
               class="w-12 h-12 rounded-2xl bg-green-500 text-white shadow-md flex items-center justify-center
                      hover:bg-green-600 hover:scale-110 transition duration-300">
                <i class="bi bi-whatsapp text-2xl"></i>
            </a>

            <!-- Facebook -->
            <?php if(!empty($settings['footer_facebook'])): ?>
            <a href="<?= $settings['footer_facebook'] ?>" target="_blank"
               class="w-12 h-12 rounded-2xl bg-white/60 backdrop-blur-xl shadow-md flex items-center justify-center
                      hover:scale-110 transition duration-300 text-blue-600 hover:text-white hover:bg-blue-600">
                <i class="bi bi-facebook text-2xl"></i>
            </a>
            <?php endif; ?>
        </div>

        <!-- Contact Info -->
        <div class="space-y-2 text-gray-700 text-center md:text-left text-sm md:text-base">

            <?php if(!empty($settings['footer_address'])): ?>
            <p class="flex items-center justify-center md:justify-start gap-3">
                <i class="bi bi-geo-alt text-orange-500"></i>
                <?= htmlspecialchars($settings['footer_address']) ?>
            </p>
            <?php endif; ?>

            <?php if(!empty($settings['footer_phone'])): ?>
            <p class="flex items-center justify-center md:justify-start gap-3">
                <i class="bi bi-telephone text-orange-500"></i>
                <?= htmlspecialchars($settings['footer_phone']) ?>
            </p>
            <?php endif; ?>

            <?php if(!empty($settings['footer_email'])): ?>
            <p class="flex items-center justify-center md:justify-start gap-3">
                <i class="bi bi-envelope text-orange-500"></i>
                <?= htmlspecialchars($settings['footer_email']) ?>
            </p>
            <?php endif; ?>

        </div>

    </div>

    <!-- Divider -->
    <div class="border-t border-orange-200 my-6 w-11/12 mx-auto"></div>

    <!-- Credits -->
    <div class="text-center text-gray-400 text-xs">
        Developed by 
        <a href="https://aflalmohamed.github.io/MyPortFolio/" 
           target="_blank" 
           class="underline hover:text-orange-600 transition">
           Aflal
        </a>
    </div>

    <div class="text-center text-gray-500 text-xs mt-1">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['site_name'] ?? 'Gift Shop') ?> • All Rights Reserved
    </div>

</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>"
   target="_blank"
   class="fixed right-6 bottom-6 bg-green-500 hover:bg-green-600 shadow-xl rounded-full p-4 flex items-center justify-center
          transition transform hover:scale-110 hover:rotate-3 z-50">
    <i class="bi bi-whatsapp text-white text-3xl"></i>
</a>

<!-- Bootstrap Icons CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
