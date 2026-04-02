<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
requireRole("company_admin");
$company_id = $_SESSION['company_id'] ?? null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $num_tables = (int)$_POST['num_tables'];
  if ($num_tables > 0 && $num_tables <= 100) {
    $pdo->prepare("DELETE FROM qr_codes WHERE company_id = ?")->execute([$company_id]);
    $stmt = $pdo->prepare("INSERT INTO qr_codes (company_id, table_id, table_number, qr_token, qr_url, is_active) VALUES (?, ?, ?, ?, ?, 1)");
    for ($i = 1; $i <= $num_tables; $i++) {
      $token = bin2hex(random_bytes(32));
      $url = "http://" . $_SERVER['HTTP_HOST'] . "/app/public/customer/menu.php?qr=" . $token;
      $stmt->execute([$company_id, $i, "T$i", $token, $url]);
    }
    $_SESSION['success'] = "Generated $num_tables QR codes";
    header("Location: qr_codes.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html><head><title>Generate QR</title><script src="https://cdn.tailwindcss.com"></script></head>
<body class="bg-gray-50"><div class="max-w-md mx-auto mt-8 bg-white p-6 rounded shadow">
<h1 class="text-2xl font-bold mb-4">Generate QR Codes</h1>
<form method="POST" class="space-y-4">
<div><label class="block font-bold mb-2">Tables:</label><input type="number" name="num_tables" min="1" max="100" required class="w-full border rounded px-3 py-2"></div>
<button type="submit" class="w-full bg-blue-600 text-white py-2 rounded font-bold">Generate</button>
</form>
</div></body></html>
