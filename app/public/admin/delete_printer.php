<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; requireRole('company_admin'); $company_id = getCompanyId();
if (isset($_GET['id'])) { $id = (int)$_GET['id']; $pdo->prepare("DELETE FROM printers WHERE id = ? AND company_id = ?")->execute([$id, $company_id]); }
redirect('dashboard.php');
?>