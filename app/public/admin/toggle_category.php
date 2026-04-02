<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; requireRole('company_admin'); $company_id = getCompanyId();
if (isset($_GET['id']) && isset($_GET['s'])) { $id = (int)$_GET['id']; $s = (int)$_GET['s']; $pdo->prepare("UPDATE categories SET is_active = ? WHERE id = ? AND company_id = ?")->execute([$s, $id, $company_id]); }
redirect('menu.php');
?>
