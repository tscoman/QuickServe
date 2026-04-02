<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['company_id'] = $user['company_id'];

        if ($user['role'] === 'super_admin') redirect('super-admin/dashboard.php');
        if ($user['role'] === 'company_admin') redirect('../admin/dashboard.php');
        if ($user['role'] === 'staff') redirect('../staff/orders.php');
    } else {
        redirect('index.php?error=invalid_credentials');
    }
} else {
    redirect('index.php');
}
?>
