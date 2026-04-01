<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['slug'])));
    $phone = sanitize($_POST['phone']);
    $street_address = sanitize($_POST['street_address']);
    $vat_number = sanitize($_POST['vat_number']);
    $cr_number = sanitize($_POST['cr_number']);
    $website_url = sanitize($_POST['website_url']);
    $instagram_url = sanitize($_POST['instagram_url']);
    $whatsapp_number = sanitize($_POST['whatsapp_number']);
    $theme = sanitize($_POST['theme']);

    // Check if slug exists
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        redirect('dashboard.php?error=slug_exists');
    }

    $sql = "INSERT INTO companies (name, slug, phone, street_address, vat_number, cr_number, website_url, instagram_url, whatsapp_number, theme) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$name, $slug, $phone, $street_address, $vat_number, $cr_number, $website_url, $instagram_url, $whatsapp_number, $theme])) {
        redirect('dashboard.php?success=1');
    } else {
        redirect('dashboard.php?error=db_error');
    }
} else {
    redirect('dashboard.php');
}
?>
