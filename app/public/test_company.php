<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json');
echo json_encode([
    'APP_COMPANY_ID' => $_SERVER['APP_COMPANY_ID'] ?? 'NOT SET',
    'global_company_id' => $global_company_id ?? 'NOT SET',
    'HTTP_HOST' => $_SERVER['HTTP_HOST']
]);
?>
