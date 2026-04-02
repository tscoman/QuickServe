<?php
class OrderHelper {
    public static $statuses = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'preparing' => 'Preparing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
    public static function createOrder($pdo, $company_id, $table_id, $items) {
        try {
            $pdo->beginTransaction();
            $total = 0;
            foreach ($items as $item) $total += $item['price'] * $item['quantity'];
            $stmt = $pdo->prepare("INSERT INTO orders (company_id, table_id, total_price, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$company_id, $table_id, $total]);
            $order_id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_item_id, quantity, price) VALUES (?, ?, ?, ?)");
            foreach ($items as $item) $stmt->execute([$order_id, $item['id'], $item['quantity'], $item['price']]);
            $stmt = $pdo->prepare("INSERT INTO order_status_log (order_id, status, timestamp) VALUES (?, 'pending', NOW())");
            $stmt->execute([$order_id]);
            $pdo->commit();
            return $order_id;
        } catch (Exception $e) { $pdo->rollBack(); throw $e; }
    }
    public static function updateStatus($pdo, $order_id, $new_status) {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$new_status, $order_id]);
        $stmt = $pdo->prepare("INSERT INTO order_status_log (order_id, status, timestamp) VALUES (?, ?, NOW())");
        $stmt->execute([$order_id, $new_status]);
    }
    public static function getOrder($pdo, $order_id, $company_id = null) {
        $sql = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, 'x ', mi.name) SEPARATOR ', ') as items_summary, COUNT(oi.id) as item_count FROM orders o LEFT JOIN order_items oi ON o.id = oi.order_id LEFT JOIN menu_items mi ON oi.menu_item_id = mi.id WHERE o.id = ?";
        $params = [$order_id];
        if ($company_id) { $sql .= " AND o.company_id = ?"; $params[] = $company_id; }
        $sql .= " GROUP BY o.id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
}
?>
