<?php
require_once 'config.php';

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../index.php?error=login_required");
        exit();
    }
}

// Strict Role Check (e.g., 'super_admin', 'company_admin', 'staff')
function requireRole($role) {
    requireLogin();
    if ($_SESSION['role'] !== $role) {
        header("Location: ../index.php?error=unauthorized_role");
        exit();
    }
}

// Allow multiple roles (e.g., ['company_admin', 'staff'])
function requireAnyRole($roles) {
    requireLogin();
    if (!in_array($_SESSION['role'], $roles)) {
        header("Location: ../index.php?error=unauthorized_role");
        exit();
    }
}

// Get current user's company ID (Crucial for Multi-Tenancy Data Isolation)
function getCompanyId() {
    return $_SESSION['company_id'] ?? null;
}

// Fetch current user details from DB
function getCurrentUser($pdo) {
    if (!isLoggedIn()) return null;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}
?>
