<?php
require '_auth_check.php';
require '../config.php';

// Get ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    die("Invalid ID");
}

// Fetch the design to delete the image
$stmt = $pdo->prepare("SELECT image FROM embroidery_designs WHERE id=?");
$stmt->execute([$id]);
$design = $stmt->fetch();

if ($design) {
    // Delete image file if exists
    if ($design['image'] && file_exists("../" . $design['image'])) {
        unlink("../" . $design['image']);
    }

    // Delete DB record
    $stmt = $pdo->prepare("DELETE FROM embroidery_designs WHERE id=?");
    $stmt->execute([$id]);
}

// Redirect back to admin page
header("Location: embroidery.php");
exit;
