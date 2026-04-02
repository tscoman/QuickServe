<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');
 $companies = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC")->fetchAll();
 $sa_users = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'super_admin' ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Super Admin - QrServe</title><script src="https://cdn.tailwindcss.com"></script><style>.section-title { border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 16px; font-weight: 600; color: #374151; }</style></head>
<body class="bg-gray-50 h-screen">
    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-3"><img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-8"><span class="font-bold text-gray-800 text-lg">QrServe Hub</span></div>
        <div class="flex items-center space-x-4"><a href="profile.php" class="text-sm text-blue-600 hover:underline">Profile</a><a href="../../index.php?logout=1" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">Logout</a></div>
    </nav>
    <div class="container mx-auto px-4 py-8 max-w-7xl overflow-y-auto h-[calc(100vh-80px)]">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">System Management</h1>
        <div class="bg-white rounded-lg shadow p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-semibold mb-6 text-blue-800">Register New Restaurant</h2>
            <form action="add_company.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div><div class="section-title">Branding & Basic Information</div><div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-lg p-4 bg-gray-50 h-40 cursor-pointer" onclick="document.getElementById('logoInput').click()">
                        <img id="logoPreview" src="https://via.placeholder.com/150x50?text=Logo" class="h-16 object-contain mb-2">
                        <label class="text-xs text-blue-600 hover:underline font-semibold">Upload Logo</label>
                        <input type="file" name="logo" accept="image/*" id="logoInput" class="hidden" onchange="document.getElementById('logoPreview').src = window.URL.createObjectURL(this.files[0])">
                    </div>
                    <input type="text" name="name" placeholder="Restaurant Name *" required class="border p-2 rounded">
                    <input type="text" name="slug" placeholder="URL Slug *" required class="border p-2 rounded">
                    <input type="text" name="phone" placeholder="Phone" class="border p-2 rounded">
                </div></div>
                <div><div class="section-title">Network & Access</div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="text-xs text-gray-500 block mb-1">Custom Domain/Subdomain</label><input type="text" name="custom_domain" placeholder="orders.joespizza.com" class="border p-2 rounded w-full"><p class="text-xs text-red-400 mt-1">* DNS must point to VPS IP</p></div><div class="flex items-end text-sm text-gray-500 bg-gray-50 p-2 rounded">If blank, auto-assigns isolated port.</div></div></div>
                <div><div class="section-title">Location & Legal</div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><textarea name="street_address" placeholder="Street Address" rows="2" class="border p-2 rounded w-full"></textarea><div class="grid grid-cols-2 gap-4"><input type="text" name="vat_number" placeholder="VAT Number" class="border p-2 rounded"><input type="text" name="cr_number" placeholder="CR Number" class="border p-2 rounded"></div></div></div>
                <div><div class="section-title">Digital Presence</div><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><input type="text" name="website_url" placeholder="Website" class="border p-2 rounded"><input type="text" name="instagram_url" placeholder="Instagram" class="border p-2 rounded"><input type="text" name="whatsapp_number" placeholder="WhatsApp Number" class="border p-2 rounded"></div></div>
                <div><div class="section-title">Mobile Bank Numbers</div><div id="mobile-numbers-container" class="space-y-2 mb-2"><div class="flex gap-2"><input type="text" name="mobile_numbers[]" placeholder="Bank Number" class="flex-1 border p-2 rounded"><button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-700 px-3 rounded hover:bg-red-200 text-sm font-bold">X</button></div></div><button type="button" onclick="addMobileField()" class="text-sm bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200 font-semibold">+ Add Bank</button></div>
                <div><div class="section-title text-red-700">Admin Credentials</div><div class="grid grid-cols-1 md:grid-cols-3 gap-4"><input type="text" name="admin_name" placeholder="Admin Name *" required class="border p-2 rounded border-red-200"><input type="email" name="admin_email" placeholder="Admin Email *" required class="border p-2 rounded border-red-200"><input type="password" name="admin_password" placeholder="Password *" required minlength="8" class="border p-2 rounded border-red-200"></div></div>
                <div><div class="section-title">Appearance</div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><select name="theme" class="border p-2 rounded"><option value="midnight">Midnight Luxury</option><option value="garden">Garden Fresh</option><option value="classic">Classic Diner</option><option value="rustic">Rustic Bakery</option></select><button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Provision & Save</button></div></div>
            </form>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            <div class="lg:col-span-1 bg-white rounded-lg shadow p-4 border"><h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">SA Team</h2><form action="add_sa_user.php" method="POST" class="space-y-2 mb-4"><input type="text" name="name" placeholder="Name" required class="w-full border p-2 rounded text-sm"><input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded text-sm"><input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded text-sm"><button type="submit" class="w-full bg-gray-800 text-white p-2 rounded text-sm hover:bg-gray-900">Add</button></form>
            <?php foreach ($sa_users as $u): ?><div class="text-sm border-b py-1"><span class="font-semibold"><?= sanitize($u['name']) ?></span><span class="text-gray-400 text-xs block"><?= sanitize($u['email']) ?></span></div><?php endforeach; ?></div>
            <div class="lg:col-span-3 bg-white rounded-lg shadow overflow-hidden border"><div class="p-4 border-b bg-gray-50"><h2 class="text-lg font-semibold">Active Instances (<?= count($companies) ?>)</h2></div>
            <table class="w-full text-left border-collapse text-sm"><thead class="bg-gray-100 text-gray-600 uppercase text-xs"><tr><th class="p-3">Client</th><th class="p-3">Access URL</th><th class="p-3">Admin</th><th class="p-3">Actions</th></tr></thead>
            <tbody><?php foreach ($companies as $c): $adm = $pdo->prepare("SELECT email FROM users WHERE company_id = ? AND role='company_admin' LIMIT 1"); $adm->execute([$c['id']]); $admin_email = $adm->fetchColumn(); $access_url = $c['custom_domain'] ? "https://".$c['custom_domain'] : "http://prmx.omanwebhosting.com:".$c['port_number']; ?>
                <tr class="border-b hover:bg-gray-50"><td class="p-3 font-semibold"><?= sanitize($c['name']) ?></td><td class="p-3"><a href="<?= $access_url ?>" target="_blank" class="text-blue-600 hover:underline font-mono text-xs"><?= $access_url ?></a></td><td class="p-3 text-gray-500 text-xs"><?= sanitize($admin_email ?? 'None') ?></td><td class="p-3"><a href="delete_company.php?id=<?= $c['id'] ?>" class="text-red-600 hover:underline text-xs font-bold" onclick="return confirm('Delete?')">Destroy</a></td></tr>
            <?php endforeach; ?></tbody></table></div>
        </div>
    </div>
    <script>document.getElementById('logoPreview').addEventListener('click', function() { document.getElementById('logoInput').click(); }); function addMobileField(){const c=document.getElementById('mobile-numbers-container');const d=document.createElement('div');d.className='flex gap-2';d.innerHTML=`<input type="text" name="mobile_numbers[]" placeholder="Number..." class="flex-1 border p-2 rounded"><button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-700 px-3 rounded hover:bg-red-200 text-sm font-bold">X</button>`;c.appendChild(d);}</script>
</body></html>