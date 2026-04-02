<?php
require_once __DIR__ . '/../../includes/config.php'; require_once __DIR__ . '/../../includes/auth.php'; requireRole('company_id'); require_once __DIR__ . '/../../includes/functions.php';
 $company_id = getCompanyId();
if (isset($_POST['save_receipt'])) {
    $show_vat = isset($_POST['receipt_show_vat']) ? 1 : 0;
    $show_cr = isset($_POST['receipt_show_cr']) ? 1 : 0;
    $show_tax = isset($_POST['receipt_show_tax_breakdown']) ? 1 : 0;
    $logo_pos = sanitize($_POST['receipt_logo_position']);
    $pdo->prepare("UPDATE companies SET receipt_show_vat = ?, receipt_show_cr = ?, receipt_show_tax_breakdown = ?, receipt_logo_position = ? WHERE id = ?")->execute([$show_vat, $show_cr, $show_tax, $logo_pos, $company_id]);
}
redirect('dashboard.php');
?>