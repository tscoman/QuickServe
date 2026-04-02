<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin'); $company_id = getCompanyId();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']); $price = floatval($_POST['price']); $category_id = (int)$_POST['category_id']; $description = sanitize($_POST['description']); $image_url = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $fileInfo = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $_FILES['image']['tmp_name']);
        if (in_array($fileInfo, $allowed)) {
            $targetDir = realpath(__DIR__ . '/../../../uploads') . "/{$company_id}/menu/";
            if (!is_dir($targetDir)) mkdir($targetDir, 0755, true);
            $fileName = uniqid() . '.webp'; $targetPath = $targetDir . $fileName;
            switch ($fileInfo) { case 'image/jpeg': $img = imagecreatefromjpeg($_FILES['image']['tmp_name']); break; case 'image/png': $img = imagecreatefrompng($_FILES['image']['tmp_name']); break; case 'image/gif': $img = imagecreatefromgif($_FILES['image']['tmp_name']); break; case 'image/webp': $img = imagecreatefromwebp($_FILES['image']['tmp_name']); break; default: $img = false; }
            if ($img) { $width = imagesx($img); $height = imagesy($img); $newWidth = 800; if ($width > $newWidth) { $newHeight = ($height / $width) * $newWidth; $tmpImg = imagecreatetruecolor($newWidth, $newHeight); imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height); imagedestroy($img); $img = $tmpImg; } imagewebp($img, $targetPath, 80); imagedestroy($img); $image_url = "/uploads/{$company_id}/menu/{$fileName}"; }
        }
    }
    $pdo->prepare("INSERT INTO menu_items (company_id, category_id, name, description, price, image_url) VALUES (?, ?, ?, ?, ?, ?)")->execute([$company_id, $category_id, $name, $description, $price, $image_url]);
}
redirect('menu.php');
?>
