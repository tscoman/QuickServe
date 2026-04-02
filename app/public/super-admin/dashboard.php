<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";
requireRole("super_admin");

 $companies = $pdo->query("SELECT * FROM companies ORDER BY id DESC")->fetchAll();
 $sa_users = $pdo->query("SELECT id,name,email,created_at FROM users WHERE role='super_admin' ORDER BY id ASC")->fetchAll();

if(isset($_GET["delete"]) && is_numeric($_GET["delete"])) {
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$_GET["delete"]]);
    header("Location: dashboard.php?deleted=1");
    exit;
}
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><title>QrServe Hub</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body class="bg-gray-100 min-h-screen">
<nav class="bg-white shadow-lg border-b px-6 py-3 sticky top-0 z-50"><div class="max-w-7xl mx-auto flex justify-between items-center">
<div class="flex items-center space-x-3"><img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-10"><span class="font-black text-xl">QrServe <span class="text-blue-600 font-bold">Hub</span></span></div>
<div class="flex items-center space-x-4"><a href="profile.php" class="text-sm text-gray-600 hover:text-blue-600 font-semibold">Profile</a><a href="../../index.php?logout=1" class="text-sm bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 font-bold">Logout</a></div></div></nav>

<div class="max-w-7xl mx-auto p-6">

<!-- STATS -->
<div class="grid grid-cols-4 gap-6 mb-8">
<div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-6 text-white shadow-lg">
<p class="text-blue-100 text-sm font-bold uppercase">Total Restaurants</p>
<p class="text-4xl font-black mt-2"><?php echo count($companies); ?></p></div>
<div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl p-6 text-white shadow-lg">
<p class="text-green-100 text-sm font-bold uppercase">Active Today</p>
<p class="text-4xl font-black mt-2"><?php echo count(array_filter($companies,function($c){return $c["is_active"];})); ?></p></div>
<div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-6 text-white shadow-lg">
<p class="text-purple-100 text-sm font-bold uppercase">SA Team</p>
<p class="text-4xl font-black mt-2"><?php echo count($sa_users); ?></p></div>
<div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl p-6 text-white shadow-lg">
<p class="text-orange-100 text-sm font-bold uppercase">Ports Active</p>
<p class="text-4xl font-black mt-2"><?php echo count(array_filter($companies,function($c){return !empty($c["port_number"]);})); ?></p></div>
</div>

<!-- REGISTER FORM -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-200">
<h2 class="text-2xl font-bold text-gray-800 mb-4"><i class="fas fa-plus-circle text-blue-600 mr-3"></i>Register New Restaurant</h2>
<form action="add_company.php" method="POST" enctype="multipart/form-data" class="space-y-4">
<div class="grid grid-cols-4 gap-4">
<div class="border-2 border-dashed rounded-lg p-4 text-center cursor-pointer hover:border-blue-400 bg-gray-50" onclick="document.getElementById('logoInput').click()">
<img id="logoPreview" src="https://via.placeholder.com/150x80?text=Logo&bg=f3f4f6" class="h-16 mx-auto object-contain mb-2">
<label class="text-xs text-blue-600 font-semibold cursor-pointer">Upload Logo</label>
<input type="file" name="logo" accept="image/*" id="logoInput" class="hidden" onchange="document.getElementById('logoPreview').src=window.URL.createObjectURL(this.files[0])"></div>
<input type="text" name="name" placeholder="Restaurant Name *" required class="border p-3 rounded-lg w-full">
<input type="text" name="slug" placeholder="URL Slug *" required class="border p-3 rounded-lg w-full">
<input type="text" name="phone" placeholder="Phone Number" class="border p-3 rounded-lg w-full"></div>
<div class="grid grid-cols-2 gap-4">
<div><label class="block text-sm font-bold text-gray-700 mb-1">Custom Domain (Optional)</label>
<input type="text" name="custom_domain" placeholder="orders.restaurant.com" class="w-full border p-3 rounded-lg">
<p class="text-xs text-red-400 mt-1">DNS must point to VPS IP</p></div>
<div class="flex items-end pb-3"><p class="text-sm text-gray-500 bg-gray-50 p-3 rounded-lg">If blank, auto-assigns port number</p></div></div>
<textarea name="street_address" placeholder="Street Address" rows="2" class="w-full border p-3 rounded-lg"></textarea>
<div class="grid grid-cols-2 gap-4">
<input type="text" name="vat_number" placeholder="VAT Number" class="border p-3 rounded-lg">
<input type="text" name="cr_number" placeholder="CR Number" class="border p-3 rounded-lg"></div>
<div class="grid grid-cols-3 gap-4">
<input type="url" name="website_url" placeholder="Website URL" class="border p-3 rounded-lg">
<input type="url" name="instagram_url" placeholder="Instagram URL" class="border p-3 rounded-lg">
<input type="tel" name="whatsapp_number" placeholder="WhatsApp (+968...)" class="border p-3 rounded-lg"></div>
<div id="mobileContainer" class="space-y-2 mb-2">
<div class="flex gap-2">
<input type="text" name="mobile_numbers[]" placeholder="+968 XXXX XXXX (Bank Name)" class="flex-1 border p-3 rounded-lg">
<button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded-lg font-bold">X</button>
</div>
</div>
<button type="button" onclick="addMobileField()" class="text-sm bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg font-bold text-gray-700">+ Add Bank</button>
<div class="bg-red-50 border border-red-200 rounded-lg p-4">
<h4 class="font-bold text-red-800 mb-2"><i class="fas fa-key mr-1"></i>Admin Login Credentials</h4>
<div class="grid grid-cols-3 gap-4">
<input type="text" name="admin_name" placeholder="Admin Name *" required class="border p-3 rounded-lg bg-white">
<input type="email" name="admin_email" placeholder="Admin Email *" required class="border p-3 rounded-lg bg-white">
<input type="password" name="admin_password" placeholder="Password *" required minlength="8" class="border p-3 rounded-lg bg-white"></div>
</div>
<select name="theme" class="w-full border p-3 rounded-lg bg-white">
<option value="midnight">Midnight Luxury</option>
<option value="garden">Garden Fresh</option>
<option value="classic">Classic Diner</option>
<option value="rustic">Rustic Bakery</option>
</select>
<button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-4 rounded-lg font-bold text-lg shadow-lg"><i class="fas fa-rocket mr-2"></i>Provision Server & Save Restaurant</button>
</form>
</div>

<!-- TABLE WITH ENTERPRISE BUTTONS -->
<div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
<div class="p-4 bg-gray-50 border-b flex justify-between items-center">
<h2 class="text-lg font-bold text-gray-800"><i class="fas fa-building mr-2 text-blue-600"></i>Registered Restaurants (<?php echo count($companies); ?>)</h2>
</div>
<table class="w-full text-left">
<thead class="bg-gray-100 text-gray-600 uppercase text-xs font-bold">
<tr><th class="px-4 py-3">Restaurant</th><th class="px-4 py-3">Access</th><th class="px-4 py-3">Admin</th><th class="px-4 py-3">Theme</th><th class="px-4 py-3 text-center">Actions</th></tr>
</thead>
<tbody class="divide-y divide-gray-200">
<?php foreach ($companies as $c):
 $adm = $pdo->prepare("SELECT email FROM users WHERE company_id=? AND role='company_admin' LIMIT 1");
 $adm->execute([$c['id']]);
 $admin_email = $adm->fetchColumn();
 $access_url = $c['custom_domain'] ? 'https://' . $c['custom_domain'] : 'http://prmx.omanwebhosting.com:' . $c['port_number'];
?>
<tr class="hover:bg-gray-50 transition">
<td class="px-4 py-3">
<?php if($c['logo_url']): ?>
<img src="<?php echo htmlspecialchars($c['logo_url']); ?>" class="h-10 w-10 object-cover rounded-lg shadow">
<?php else: ?>
<div class="h-10 w-10 bg-gray-200 rounded-lg flex items-center justify-center"><i class="fas fa-store text-gray-400"></i></div>
<?php endif; ?>
<span class="ml-3 font-bold text-gray-800"><?php echo htmlspecialchars($c['name']); ?></span>
<br><small class="text-gray-500">Port: <?php echo $c['port_number'] ?? 'Not assigned'; ?></small>
</td>
<td class="px-4 py-3">
<a href="<?php echo $access_url; ?>" target="_blank" class="text-blue-600 hover:underline font-mono text-xs font-bold"><?php echo $access_url; ?></a>
<br><a href="<?php echo $access_url; ?>" target="_blank" class="text-xs text-gray-500"><i class="fas fa-external-link-alt mr-1"></i>Open Portal</a>
</td>
<td class="px-4 py-3"><code class="text-xs bg-gray-100 px-2 py-1 rounded"><?php echo htmlspecialchars($admin_email ?? 'No Admin'); ?></code></td>
<td class="px-4 py-3"><?php
 $themes = ['midnight'=>'gray-900 text-white','garden'=>'green-100 text-green-800','classic'=>'red-100 text-red-800','rustic'=>'amber-100 text-amber-800'];
 $tc = $c['theme'] ?? 'midnight';
echo '<span class="px-2 py-1 rounded text-xs font-bold bg-'.$themes[$tc].'">'.ucfirst($tc).'</span>';
?></td>
<td class="px-4 py-3">
<div class="flex flex-wrap gap-1 justify-center">
<a href="view_company.php?id=<?php echo $c['id']; ?>" class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded text-xs font-bold transition"><i class="fas fa-eye mr-1"></i>View</a>
<a href="edit_company.php?id=<?php echo $c['id']; ?>" class="inline-flex items-center px-2 py-1 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 rounded text-xs font-bold transition"><i class="fas fa-edit mr-1"></i>Edit</a>
<a href="tools/backup_company.php?id=<?php echo $c['id']; ?>" class="inline-flex items-center px-2 py-1 bg-green-50 text-green-700 hover:bg-green-100 rounded text-xs font-bold transition"><i class="fas fa-database mr-1"></i>Backup</a>
<a href="tools/restore_company.php?id=<?php echo $c['id']; ?>" class="inline-flex items-center px-2 py-1 bg-orange-50 text-orange-700 hover:bg-orange-100 rounded text-xs font-bold transition"><i class="fas fa-history mr-1"></i>Restore</a>
<a href="tools/clone_company.php?id=<?php echo $c['id']; ?>" class="inline-flex items-center px-2 py-1 bg-purple-50 text-purple-700 hover:bg-purple-100 rounded text-xs font-bold transition"><i class="fas fa-copy mr-1"></i>Clone</a>
<a href="dashboard.php?delete=<?php echo $c['id']; ?>" onclick="return confirm('Permanently delete this restaurant?')" class="inline-flex items-center px-2 py-1 bg-red-50 text-red-700 hover:bg-red-100 rounded text-xs font-bold transition"><i class="fas fa-trash-alt mr-1"></i>Delete</a>
</div>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div>

<!-- SA TEAM -->
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-8">
<div class="bg-white rounded-xl shadow-md p-6 border border-gray-200">
<h3 class="font-bold text-gray-800 mb-4 pb-2 border-b"><i class="fas fa-users-cog mr-2 text-purple-600"></i>SA Team Management</h3>
<form action="add_sa_user.php" method="POST" class="mb-4 space-y-2">
<input type="text" name="name" placeholder="Full Name" required class="w-full border p-2 rounded text-sm">
<input type="email" name="email" placeholder="Email Address" required class="w-full border p-2 rounded text-sm">
<input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded text-sm">
<button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded font-bold text-sm transition"><i class="fas fa-user-plus mr-1"></i>Add Team Member</button>
</form>
<div class="border-t pt-4 mt-4 space-y-2 max-h-48 overflow-y-auto">
<?php foreach($sa_users as $u): ?>
<div class="flex justify-between items-center text-sm p-2 bg-gray-50 rounded hover:bg-gray-100">
<div>
<p class="font-semibold text-gray-800"><?php echo htmlspecialchars($u['name']); ?></p>
<p class="text-xs text-gray-500"><?php echo htmlspecialchars($u['email']); ?></p>
</div>
<span class="text-xs text-gray-400"><?php echo date("M j", strtotime($u['created_at'])); ?></span>
</div>
<?php endforeach; ?>
</div>
</div>
</div>

<footer class="mt-12 pt-6 border-t border-gray-200 text-center text-gray-500 text-sm">
<p>&copy; 2026 Technology Solutions Company (TSCO Group) | QrServe Enterprise SaaS Platform</p>
</footer>

<script>
function addMobileField() {
const container = document.getElementById("mobileContainer");
const div = document.createElement("div");
div.className = "flex gap-2";
div.innerHTML = '<input type="text" name="mobile_numbers[]" placeholder="Another bank number..." class="flex-1 border p-3 rounded-lg"><button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded-lg hover:bg-red-200 font-bold">X</button>';
container.appendChild(div);
}
</script>
</body>
</html>