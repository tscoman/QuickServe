<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['slug'])));
    $phone = sanitize($_POST['phone']);
    $tax = floatval($_POST['tax']);
    $theme = sanitize($_POST['theme']);

    // Check if slug exists
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) {
        redirect('dashboard.php?error=slug_exists');
    }

    $stmt = $pdo->prepare("INSERT INTO companies (name, slug, phone, tax_percentage, theme) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$name, $slug, $phone, $tax, $theme])) {
        redirect('dashboard.php?success=1');
    } else {
        redirect('dashboard.php?error=db_error');
    }
} else {
    redirect('dashboard.php');
}
?>
