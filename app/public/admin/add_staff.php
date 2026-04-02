<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin'); $company_id = getCompanyId();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']); $email = sanitize($_POST['email']); $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?"); $stmt->execute([$email]); if ($stmt->fetch()) { redirect('dashboard.php?error=email_taken'); }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'staff')")->execute([$company_id, $name, $email, $hash]);
    redirect('dashboard.php?success=staff_added');
} else { redirect('dashboard.php'); }
?>
