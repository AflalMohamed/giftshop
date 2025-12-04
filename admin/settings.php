<?php
require '_auth_check.php'; // Ensure admin is logged in
require '../config.php';    // PDO connection

$message = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'site_name','site_subtitle',
        'footer_instagram','footer_whatsapp','footer_facebook',
        'footer_address','footer_phone','footer_email',
        'map_lat','map_lng'
    ];

    try {
        $pdo->beginTransaction();

        // Save all text settings
        foreach ($fields as $f) {
            $value = $_POST[$f] ?? '';
            $stmt = $pdo->prepare("
                INSERT INTO site_settings (key_name, value_text) 
                VALUES (:k, :v) 
                ON DUPLICATE KEY UPDATE value_text = :v2
            ");
            $stmt->execute([':k'=>$f, ':v'=>$value, ':v2'=>$value]);
        }

        // Logo upload
        if (!empty($_FILES['site_logo']['name'])) {
            $allowedTypes = ['image/jpeg','image/png','image/gif','image/webp'];
            if (in_array($_FILES['site_logo']['type'], $allowedTypes)) {
                $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                $newFileName = 'logo_' . time() . '.' . $ext;
                $target = '../uploads/' . $newFileName;
                if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $target)) {
                    $stmt = $pdo->prepare("
                        INSERT INTO site_settings (key_name, value_text) 
                        VALUES ('site_logo', :v) 
                        ON DUPLICATE KEY UPDATE value_text = :v2
                    ");
                    $stmt->execute([':v'=>'uploads/'.$newFileName, ':v2'=>'uploads/'.$newFileName]);
                } else {
                    throw new Exception('Failed to upload logo.');
                }
            } else {
                throw new Exception('Invalid file type for logo.');
            }
        }

        $pdo->commit();
        $message = "<div class='text-green-600 mb-4 font-semibold'>Settings updated successfully!</div>";

    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "<div class='text-red-600 mb-4 font-semibold'>Error: " . $e->getMessage() . "</div>";
    }
}

// Fetch current settings
$settingsRaw = $pdo->query("SELECT key_name,value_text FROM site_settings")->fetchAll();
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
<title>Site Settings</title>
<script src="https://cdn.tailwindcss.com"></script>

<body class="bg-gray-50 min-h-screen py-6">

<div class="max-w-5xl mx-auto bg-white p-8 rounded-3xl shadow-xl">

    <a href="dashboard.php" class="inline-flex items-center mb-6 text-orange-600 font-semibold hover:text-orange-700 transition">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 18l-6-6 6-6"></path>
        </svg>
        Back to Dashboard
    </a>

    <h1 class="text-3xl font-bold mb-6 text-orange-600">Site Settings</h1>

    <?= $message ?>

    <form action="" method="post" enctype="multipart/form-data" class="space-y-6">

        <!-- Site Name & Subtitle -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block font-semibold mb-2">Site Name</label>
                <input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-orange-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">Site Subtitle</label>
                <input type="text" name="site_subtitle" value="<?= htmlspecialchars($settings['site_subtitle'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-orange-300"/>
            </div>
        </div>

        <!-- Footer Socials & Contact -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label class="block font-semibold mb-2">Instagram</label>
                <input type="text" name="footer_instagram" value="<?= htmlspecialchars($settings['footer_instagram'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-pink-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">WhatsApp</label>
                <input type="text" name="footer_whatsapp" value="<?= htmlspecialchars($settings['footer_whatsapp'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-green-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">Facebook</label>
                <input type="text" name="footer_facebook" value="<?= htmlspecialchars($settings['footer_facebook'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-blue-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">Address</label>
                <input type="text" name="footer_address" value="<?= htmlspecialchars($settings['footer_address'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-gray-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">Phone</label>
                <input type="text" name="footer_phone" value="<?= htmlspecialchars($settings['footer_phone'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-gray-300"/>
            </div>
            <div>
                <label class="block font-semibold mb-2">Email</label>
                <input type="email" name="footer_email" value="<?= htmlspecialchars($settings['footer_email'] ?? '') ?>" class="w-full p-3 border rounded-lg focus:ring focus:ring-gray-300"/>
            </div>
        </div>

       

        <!-- Logo Upload -->
        <div>
            <label class="block font-semibold mb-2">Site Logo</label>
            <?php if(!empty($settings['site_logo'])): ?>
                <img src="../<?= htmlspecialchars($settings['site_logo']) ?>" alt="Logo" class="h-24 mb-2 object-contain border rounded-lg p-1 bg-gray-50">
            <?php endif; ?>
            <input type="file" name="site_logo" accept="image/*" class="w-full"/>
        </div>

        <button type="submit" class="w-full py-3 bg-orange-600 text-white rounded-xl font-semibold hover:bg-orange-700 transition text-lg">Save Settings</button>
    </form>
</div>

</body>
</html>
