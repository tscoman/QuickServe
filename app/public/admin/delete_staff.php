<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; requireRole('company_admin'); $company_id = getCompanyId();
if (isset($_GET['id'])) { $staff_id = (int) $_GET['id']; $pdo->prepare("DELETE FROM users WHERE id = ? AND company_id = ? AND role = 'staff'")->execute([$staff_id, $company_id]); }
redirect('dashboard.php');
?>
