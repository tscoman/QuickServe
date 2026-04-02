<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
requireRole('super_admin');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id']; $new_password = $_POST['new_password'];
    if (strlen($new_password) >= 8) {
        $hash = password_hash($new_password, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")->execute([$hash, $user_id]);
    }
}
header("Location: dashboard.php"); exit();
?>
