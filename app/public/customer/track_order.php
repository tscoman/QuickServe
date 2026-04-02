<?php
require_once __DIR__ . "/../../includes/config.php";
$order_id = $_GET['order_id'] ?? null;
$qr_token = $_GET['qr'] ?? null;
if (!$order_id) die("Invalid");
$stmt = $pdo->prepare("SELECT o.*, c.name as company_name FROM orders o JOIN companies c ON o.company_id = c.id WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
if (!$order) die("Not found");
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Order #<?php echo $order_id; ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
<div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
<h1 class="text-2xl font-bold mb-2">Order #<?php echo $order_id; ?></h1>
<p class="text-gray-600 mb-4"><?php echo htmlspecialchars($order['company_name']); ?></p>
<div class="bg-blue-50 p-4 rounded mb-4">
<p class="text-sm text-gray-600">Status</p>
<p class="text-2xl font-bold text-blue-600"><?php echo ucfirst($order['status']); ?></p>
</div>
<div class="mb-4">
<p class="font-bold">Total: OMR <?php echo number_format($order['total_price'], 2); ?></p>
</div>
<div class="text-center text-sm">
<?php if ($order['status'] === 'ready'): ?>
<p class="text-green-600 font-bold text-lg">✅ Ready!</p>
<?php else: ?>
<p class="text-gray-600">Your order is being prepared...</p>
<?php endif; ?>
</div>
<a href="/app/public/customer/menu.php?qr=<?php echo urlencode($qr_token); ?>" class="block mt-4 text-center bg-blue-600 text-white py-2 rounded">Back to Menu</a>
</div>
<?php if ($order['status'] !== 'ready' && $order['status'] !== 'completed'): ?><script>setTimeout(() => location.reload(), 3000);</script><?php endif; ?>
</body>
</html>
