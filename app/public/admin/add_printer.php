<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin'); $company_id = getCompanyId();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['printer_name']); $type = sanitize($_POST['printer_type']); $identifier = sanitize($_POST['identifier']);
    $pdo->prepare("INSERT INTO printers (company_id, printer_name, printer_type, identifier, is_active) VALUES (?, ?, ?, ?, 1)")->execute([$company_id, $name, $type, $identifier]);
}
redirect('dashboard.php');
?>