<?php
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../includes/auth.php';
requireRole('company_admin');
 $company_id = getCompanyId();

 $stmt = $pdo->prepare("SELECT 
    SUM(CASE WHEN status = 'pending_payment' THEN 1 ELSE 0 END) as pending_payments,
    SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing,
    SUM(CASE WHEN status = 'paid' AND created_at >= NOW() - INTERVAL 5 MINUTE THEN 1 ELSE 0 END) as recent_paid
FROM orders WHERE company_id = ?");
 $stmt->execute([$company_id]);
 $counts = $stmt->fetch();

header('Content-Type: application/json');
echo json_encode([
    'pending_payments' => (int)$counts['pending_payments'],
    'preparing' => (int)$counts['preparing'],
    'recent_paid' => (int)$counts['recent_paid'],
    'total_alerts' => ((int)$counts['pending_payments'] + (int)$counts['preparing'])
]);
