<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

if (isset($_GET['id'])) {
    $company_id = (int) $_GET['id'];
    
    if ($company_id > 0) {
        // Deleting from 'companies' automatically cascades and deletes 
        // users, categories, menu_items, tables, orders, order_items, and taxes
        // thanks to ON DELETE CASCADE in the database schema.
        $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        
        if ($stmt->rowCount() > 0) {
            redirect('dashboard.php?success=deleted');
        } else {
            redirect('dashboard.php?error=not_found');
        }
    } else {
        redirect('dashboard.php?error=invalid_id');
    }
} else {
    redirect('dashboard.php');
}
?>
