<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize Inputs
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
    
    // Admin Inputs
    $admin_name = sanitize($_POST['admin_name']);
    $admin_email = sanitize($_POST['admin_email']);
    $admin_password = $_POST['admin_password'];

    // Check if slug or admin email exists
    $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = ?");
    $stmt->execute([$slug]);
    if ($stmt->fetch()) { redirect('dashboard.php?error=slug_exists'); }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$admin_email]);
    if ($stmt->fetch()) { redirect('dashboard.php?error=email_exists'); }

    // Begin Transaction (If one fails, both fail)
    try {
        $pdo->beginTransaction();

        // 1. Insert Company
        $sql = "INSERT INTO companies (name, slug, phone, street_address, vat_number, cr_number, website_url, instagram_url, whatsapp_number, theme) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $slug, $phone, $street_address, $vat_number, $cr_number, $website_url, $instagram_url, $whatsapp_number, $theme]);
        
        // Get the new Company ID
        $company_id = $pdo->lastInsertId();

        // 2. Hash Password & Insert Admin User
        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'company_admin')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$company_id, $admin_name, $admin_email, $hashed_password]);

        // Commit both to database
        $pdo->commit();
        
        redirect('dashboard.php?success=1');

    } catch (Exception $e) {
        // If anything goes wrong, revert all changes
        $pdo->rollBack();
        redirect('dashboard.php?error=db_error');
    }
} else {
    redirect('dashboard.php');
}
?>
