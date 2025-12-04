<?php
// Ensure $settings is loaded from config.php
require 'config.php';

// WhatsApp number from settings
$adminWhatsapp = $settings['footer_whatsapp'] ?? '94700000000';
?>

<!-- Footer -->
<footer id="footer" class="bg-gradient-to-r from-orange-50 to-white border-t border-orange-200 py-12">
    <div class="container mx-auto px-6 md:px-12 flex flex-col md:flex-row justify-between items-start md:items-center gap-10">

        <!-- Brand Section -->
        <div class="flex flex-col items-center md:items-start text-center md:text-left">
            <span class="font-extrabold text-3xl text-orange-600 tracking-wide">
                <?= htmlspecialchars($settings['NSK Embroidery'] ?? 'NSK Embroidery') ?>
            </span>
            <p class="text-gray-600 mt-2 text-sm md:text-base">
                <?= htmlspecialchars($settings['Explore your designs'] ?? 'Explore your designs') ?>
            </p>
        </div>

        <!-- Social Media Links -->
        <div class="flex gap-4 justify-center md:justify-start">
            <!-- Instagram -->
            <?php if(!empty($settings['footer_instagram'])): ?>
            <a href="<?= $settings['footer_instagram'] ?>" target="_blank" class="w-12 h-12 flex items-center justify-center rounded-full bg-pink-100 text-pink-500 hover:bg-pink-500 hover:text-white transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5A4.25 4.25 0 007.75 20.5h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zM12 7a5 5 0 110 10 5 5 0 010-10zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zm4.75-.88a1.12 1.12 0 110 2.24 1.12 1.12 0 010-2.24z"/>
                </svg>
            </a>
            <?php endif; ?>

            <!-- WhatsApp -->
            <a href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>" target="_blank" class="w-12 h-12 flex items-center justify-center rounded-full bg-green-500 hover:bg-green-600 text-white transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M16.54 11.5c-.26-.13-1.54-.76-1.78-.85-.23-.09-.4-.13-.57.13-.16.26-.63.85-.77 1.02-.14.16-.28.18-.52.06-.26-.13-1.1-.41-2.1-1.29-.78-.69-1.3-1.54-1.46-1.8-.15-.26-.02-.4.11-.53.12-.12.26-.28.38-.41.12-.13.16-.22.26-.37.09-.15.05-.28-.03-.41-.09-.13-.57-1.37-.78-1.88-.21-.49-.43-.42-.57-.43l-.49-.01c-.16 0-.41.06-.63.28-.22.22-.83.81-.83 1.97s.85 2.29.97 2.45c.12.15 1.67 2.54 4.04 3.57.56.24.99.38 1.33.49.56.18 1.07.15 1.47.09.45-.07 1.54-.63 1.76-1.24.23-.61.23-1.13.16-1.24-.07-.11-.26-.18-.52-.31zM12 2C6.48 2 2 6.48 2 12c0 2.11.55 4.08 1.52 5.78L2 22l4.39-1.45C8.92 21.45 10.95 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
                </svg>
            </a>

            <!-- Facebook -->
            <?php if(!empty($settings['footer_facebook'])): ?>
            <a href="<?= $settings['footer_facebook'] ?>" target="_blank" class="w-12 h-12 flex items-center justify-center rounded-full bg-blue-100 text-blue-600 hover:bg-blue-600 hover:text-white transition duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M22 12a10 10 0 10-11.5 9.95v-7.05h-2.08V12h2.08v-1.76c0-2.06 1.23-3.2 3.11-3.2.9 0 1.84.16 1.84.16v2.02h-1.03c-1.01 0-1.32.63-1.32 1.27V12h2.24l-.36 2.9h-1.88v7.05A10 10 0 0022 12z"/>
                </svg>
            </a>
            <?php endif; ?>
        </div>

        <!-- Contact Info -->
        <div class="flex flex-col text-gray-700 text-sm md:text-base gap-1 text-center md:text-left">
            <?php if(!empty($settings['footer_address'])): ?>
            <p class="flex items-center gap-2"><span>📍</span> <?= htmlspecialchars($settings['footer_address']) ?></p>
            <?php endif; ?>
            <?php if(!empty($settings['footer_phone'])): ?>
            <p class="flex items-center gap-2"><span>📞</span> <?= htmlspecialchars($settings['footer_phone']) ?></p>
            <?php endif; ?>
            <?php if(!empty($settings['footer_email'])): ?>
            <p class="flex items-center gap-2"><span>✉️</span> <?= htmlspecialchars($settings['footer_email']) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="mt-4 text-center text-gray-500 text-xs">
        &copy; <?= date('Y') ?> <?= htmlspecialchars($settings['NSK Embroidery'] ?? 'NSK Embroidery') ?>. All rights reserved.
    </div>
</footer>

<!-- Floating WhatsApp Button -->
<a href="https://wa.me/<?= preg_replace('/\D/', '', $adminWhatsapp) ?>" 
   target="_blank" 
   class="fixed right-6 bottom-6 bg-green-500 hover:bg-green-600 shadow-lg rounded-full p-4 flex items-center justify-center z-50 transition"
   title="Contact via WhatsApp">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
        <path d="M16.54 11.5c-.26-.13-1.54-.76-1.78-.85-.23-.09-.4-.13-.57.13-.16.26-.63.85-.77 1.02-.14.16-.28.18-.52.06-.26-.13-1.1-.41-2.1-1.29-.78-.69-1.3-1.54-1.46-1.8-.15-.26-.02-.4.11-.53.12-.12.26-.28.38-.41.12-.13.16-.22.26-.37.09-.15.05-.28-.03-.41-.09-.13-.57-1.37-.78-1.88-.21-.49-.43-.42-.57-.43l-.49-.01c-.16 0-.41.06-.63.28-.22.22-.83.81-.83 1.97s.85 2.29.97 2.45c.12.15 1.67 2.54 4.04 3.57.56.24.99.38 1.33.49.56.18 1.07.15 1.47.09.45-.07 1.54-.63 1.76-1.24.23-.61.23-1.13.16-1.24-.07-.11-.26-.18-.52-.31zM12 2C6.48 2 2 6.48 2 12c0 2.11.55 4.08 1.52 5.78L2 22l4.39-1.45C8.92 21.45 10.95 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2z"/>
    </svg>
</a>

