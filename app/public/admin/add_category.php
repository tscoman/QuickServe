<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin'); $company_id = getCompanyId();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']); $sort_order = (int)$_POST['sort_order'];
    $printer_id = (!empty($_POST['printer_id']) && $_POST['printer_id'] !== 'none') ? (int)$_POST['printer_id'] : null;
    $pdo->prepare("INSERT INTO categories (company_id, name, sort_order, printer_id) VALUES (?, ?, ?, ?)")->execute([$company_id, $name, $sort_order, $printer_id]);
}
redirect('menu.php');
?>
