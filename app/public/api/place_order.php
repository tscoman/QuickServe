<?php
header('Content-Type: application/json');
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/helpers/order_helper.php";
try {
  $data = json_decode(file_get_contents('php://input'), true);
  if (!$data || !isset($data['qr'])) throw new Exception("Invalid");
  $stmt = $pdo->prepare("SELECT qr.*, t.id as table_id, c.id as company_id FROM qr_codes qr JOIN tables t ON qr.table_id = t.id JOIN companies c ON qr.company_id = c.id WHERE qr.qr_token = ?");
  $stmt->execute([$data['qr']]);
  $qr = $stmt->fetch();
  if (!$qr) throw new Exception("QR invalid");
  $order_id = OrderHelper::createOrder($pdo, $qr['company_id'], $qr['table_id'], $data['items'] ?? []);
  echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) { http_response_code(400); echo json_encode(['success' => false, 'message' => $e->getMessage()]); }
?>
