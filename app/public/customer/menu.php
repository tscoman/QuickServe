<?php
require_once __DIR__ . "/../../includes/config.php";
$qr_token = $_GET['qr'] ?? null;
if (!$qr_token) die("Invalid QR");

$stmt = $pdo->prepare("SELECT qr.*, c.id as company_id, c.name, c.logo_url, c.theme FROM qr_codes qr JOIN companies c ON qr.company_id = c.id WHERE qr.qr_token = ? AND qr.is_active = 1");
$stmt->execute([$qr_token]);
$qr_data = $stmt->fetch();
if (!$qr_data) die("QR not found");

$company_id = $qr_data['company_id'];
$company_name = $qr_data['name'];
$table = $qr_data['table_number'];

$stmt = $pdo->prepare("SELECT * FROM categories WHERE company_id = ? ORDER BY sort_order");
$stmt->execute([$company_id]);
$categories = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM menu_items WHERE company_id = ? AND is_available = 1 ORDER BY category_id, name");
$stmt->execute([$company_id]);
$items = $stmt->fetchAll();

$items_by_cat = [];
foreach ($items as $item) $items_by_cat[$item['category_id']][] = $item;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($company_name); ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
<header class="bg-blue-600 text-white p-4">
<div class="max-w-6xl mx-auto flex justify-between">
<div><h1 class="text-2xl font-bold"><?php echo htmlspecialchars($company_name); ?></h1><p class="text-sm">Table <?php echo htmlspecialchars($table); ?></p></div>
<button onclick="toggleCart()" class="bg-white text-blue-600 px-4 py-2 rounded font-bold">🛒 <span id="cart-count">0</span></button>
</div>
</header>
<main class="max-w-6xl mx-auto p-4">
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
<div class="lg:col-span-3">
<?php foreach ($categories as $cat): ?>
<?php if (!empty($items_by_cat[$cat['id']])): ?>
<section class="mb-8">
<h2 class="text-2xl font-bold mb-4 text-blue-600"><?php echo htmlspecialchars($cat['name']); ?></h2>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<?php foreach ($items_by_cat[$cat['id']] as $item): ?>
<div class="bg-white rounded-lg shadow p-4">
<?php if ($item['image_url']): ?><img src="<?php echo htmlspecialchars($item['image_url']); ?>" class="w-full h-32 object-cover rounded mb-2"><?php endif; ?>
<h3 class="font-bold text-lg"><?php echo htmlspecialchars($item['name']); ?></h3>
<p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($item['description'] ?? ''); ?></p>
<div class="flex justify-between items-center">
<span class="text-lg font-bold text-blue-600">OMR <?php echo number_format($item['price'], 2); ?></span>
<button onclick="addCart(<?php echo $item['id']; ?>,'<?php echo addslashes($item['name']); ?>',<?php echo $item['price']; ?>)" class="bg-blue-600 text-white px-3 py-1 rounded text-sm font-bold">Add</button>
</div>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>
<?php endforeach; ?>
</div>
<aside class="hidden lg:block bg-white rounded-lg shadow p-4 h-fit sticky top-4">
<h3 class="text-xl font-bold mb-4">Your Order</h3>
<div id="cart-items" class="mb-4"><p class="text-gray-500">Empty</p></div>
<div class="border-t pt-4">
<div class="flex justify-between mb-2"><span>Subtotal:</span><span id="subtotal">OMR 0.00</span></div>
<div class="flex justify-between mb-4"><span>Tax:</span><span id="tax">OMR 0.00</span></div>
<div class="flex justify-between font-bold text-lg mb-4 text-blue-600"><span>Total:</span><span id="total">OMR 0.00</span></div>
<button onclick="checkout()" class="w-full bg-blue-600 text-white py-2 rounded font-bold">Checkout</button>
</div>
</aside>
</div>
</main>
<div id="modal" class="hidden fixed inset-0 bg-black/50 flex items-end lg:hidden z-50">
<div class="w-full bg-white rounded-t-2xl p-4 max-h-96 overflow-y-auto">
<div class="flex justify-between mb-4"><h3 class="text-xl font-bold">Order</h3><button onclick="toggleCart()" class="text-2xl">&times;</button></div>
<div id="mobile-cart"></div>
<div class="border-t pt-4"><div class="flex justify-between font-bold mb-4"><span>Total:</span><span id="mobile-total">OMR 0.00</span></div><button onclick="checkout()" class="w-full bg-blue-600 text-white py-2 rounded font-bold">Checkout</button></div>
</div>
</div>
<script>
let cart = [];
const qr = '<?php echo htmlspecialchars($qr_token); ?>';
function addCart(id, name, price) { let x = cart.find(i => i.id === id); if (x) x.qty++; else cart.push({id, name, price, qty: 1}); updateCart(); }
function removeCart(id) { cart = cart.filter(i => i.id !== id); updateCart(); }
function updateCart() {
  let cnt = cart.reduce((s, i) => s + i.qty, 0);
  document.getElementById('cart-count').textContent = cnt;
  let sub = cart.reduce((s, i) => s + i.price * i.qty, 0);
  let tax = sub * 0.05;
  let tot = sub + tax;
  document.getElementById('subtotal').textContent = 'OMR ' + sub.toFixed(2);
  document.getElementById('tax').textContent = 'OMR ' + tax.toFixed(2);
  document.getElementById('total').textContent = 'OMR ' + tot.toFixed(2);
  document.getElementById('mobile-total').textContent = 'OMR ' + tot.toFixed(2);
  let html = cart.length ? '' : '<p class="text-gray-500">Empty</p>';
  cart.forEach(i => { html += `<div class="border-b pb-2 mb-2"><div class="flex justify-between"><b>${i.name}</b><button onclick="removeCart(${i.id})" class="text-red-500 text-sm">✕</button></div><div class="flex gap-2 items-center"><input type="number" value="${i.qty}" min="1" onchange="cart.find(x=>x.id==${i.id}).qty=parseInt(this.value);updateCart();" class="w-12 border rounded px-1"><span>OMR ${(i.price*i.qty).toFixed(2)}</span></div></div>`; });
  document.getElementById('cart-items').innerHTML = html;
  document.getElementById('mobile-cart').innerHTML = html;
}
function toggleCart() { document.getElementById('modal').classList.toggle('hidden'); }
function checkout() {
  if (cart.length === 0) { alert('Cart is empty'); return; }
  fetch('/app/public/api/place_order.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({qr, items: cart}) })
    .then(r => r.json()).then(d => { if (d.success) window.location = `/app/public/customer/track_order.php?order_id=${d.order_id}&qr=${qr}`; else alert('Error: ' + d.message); }).catch(e => alert('Error'));
}
updateCart();
</script>
</body>
</html>
