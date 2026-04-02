<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

 $companies = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC")->fetchAll();
 $sa_users = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = 'super_admin' ORDER BY id ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - QrServe</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>.section-title { border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 16px; font-weight: 600; color: #374151; }</style>
</head>
<body class="bg-gray-50 h-screen">
    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-8">
            <span class="font-bold text-gray-800 text-lg">QrServe Hub</span>
        </div>
        <div class="flex items-center space-x-4">
            <a href="profile.php" class="text-sm text-blue-600 hover:underline">Profile</a>
            <a href="../../index.php?logout=1" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">System Management</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-semibold mb-6 text-blue-800">Register New Restaurant</h2>
            <form action="add_company.php" method="POST" class="space-y-6">
                
                <div>
                    <div class="section-title">Basic Information</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="name" placeholder="Restaurant Name *" required class="border p-2 rounded">
                        <input type="text" name="slug" placeholder="URL Slug *" required class="border p-2 rounded">
                        <input type="text" name="phone" placeholder="Phone Number" class="border p-2 rounded">
                    </div>
                </div>

                <div>
                    <div class="section-title">Location & Legal</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <textarea name="street_address" placeholder="Street Address" rows="2" class="border p-2 rounded w-full"></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="vat_number" placeholder="VAT Number" class="border p-2 rounded">
                            <input type="text" name="cr_number" placeholder="CR Number" class="border p-2 rounded">
                        </div>
                    </div>
                </div>

                <div>
                    <div class="section-title">Digital Presence & Receipts</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="website_url" placeholder="Website URL" class="border p-2 rounded">
                        <input type="text" name="instagram_url" placeholder="Instagram URL" class="border p-2 rounded">
                        <input type="text" name="whatsapp_number" placeholder="WhatsApp Number (e.g. +968...)" class="border p-2 rounded">
                    </div>
                </div>

                <div>
                    <div class="section-title">Mobile Bank Transfer Numbers</div>
                    <p class="text-xs text-gray-500 mb-3">Add one or multiple numbers where customers can send money. Customer will upload a screenshot for cashier to verify.</p>
                    <div id="mobile-numbers-container" class="space-y-2 mb-2">
                        <div class="flex gap-2">
                            <input type="text" name="mobile_numbers[]" placeholder="e.g., +968 9000 0000 (Bank Muscat)" class="flex-1 border p-2 rounded">
                            <button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-700 px-3 rounded hover:bg-red-200 text-sm font-bold">X</button>
                        </div>
                    </div>
                    <button type="button" onclick="addMobileField()" class="text-sm bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200 font-semibold">+ Add Another Number</button>
                </div>

                <div>
                    <div class="section-title text-red-700">Restaurant Admin Login Credentials</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="admin_name" placeholder="Admin Name *" required class="border p-2 rounded border-red-200">
                        <input type="email" name="admin_email" placeholder="Admin Email *" required class="border p-2 rounded border-red-200">
                        <input type="password" name="admin_password" placeholder="Admin Password *" required minlength="8" class="border p-2 rounded border-red-200">
                    </div>
                </div>

                <div>
                    <div class="section-title">Appearance</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select name="theme" class="border p-2 rounded">
                            <option value="midnight">Midnight Luxury</option>
                            <option value="garden">Garden Fresh</option>
                            <option value="classic">Classic Diner</option>
                            <option value="rustic">Rustic Bakery</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Save Restaurant & Admin</button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Tables Below -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-1 bg-white rounded-lg shadow p-6 border">
                <h2 class="text-lg font-semibold mb-4 text-gray-800 border-b pb-2">SA Team</h2>
                <form action="add_sa_user.php" method="POST" class="space-y-2 mb-4">
                    <input type="text" name="name" placeholder="Name" required class="w-full border p-2 rounded text-sm">
                    <input type="email" name="email" placeholder="Email" required class="w-full border p-2 rounded text-sm">
                    <input type="password" name="password" placeholder="Password" required class="w-full border p-2 rounded text-sm">
                    <button type="submit" class="w-full bg-gray-800 text-white p-2 rounded text-sm hover:bg-gray-900">Add User</button>
                </form>
                <?php foreach ($sa_users as $u): ?>
                    <div class="text-sm border-b py-1 flex justify-between"><span><?= sanitize($u['name']) ?></span><span class="text-gray-400 text-xs"><?= sanitize($u['email']) ?></span></div>
                <?php endforeach; ?>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden border">
                <div class="p-4 border-b bg-gray-50"><h2 class="text-lg font-semibold">Restaurants (<?= count($companies) ?>)</h2></div>
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr><th class="p-3">Name</th><th class="p-3">Admin Email</th><th class="p-3">Theme</th><th class="p-3">Port</th><th class="p-3">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $c): 
                            $adm = $pdo->prepare("SELECT email FROM users WHERE company_id = ? AND role='company_admin' LIMIT 1");
                            $adm->execute([$c['id']]);
                            $admin_email = $adm->fetchColumn();
                        ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-semibold"><?= sanitize($c['name']) ?></td>
                                <td class="p-3 text-gray-500"><?= sanitize($admin_email ?? 'No Admin') ?></td>
                                <td class="p-3 text-blue-600 font-bold">:8080</td></tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3"><span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= $c['theme'] ?></span></td>
                                <td class="p-3 text-blue-600 font-bold">:8080</td></tr>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3" style="display:none"></td>
                                <td class="p-3">
                                    <a href="view_company.php?id=<?= $c['id'] ?>" class="text-blue-600 hover:underline text-xs mr-2">Manage</a> 
                                    <a href="delete_company.php?id=<?= $c['id'] ?>" class="text-red-600 hover:underline text-xs font-semibold" onclick="return confirm('⚠️ Are you sure? This will permanently delete this restaurant!')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Simple JS to add/remove fields -->
    <script>
        function addMobileField() {
            const container = document.getElementById('mobile-numbers-container');
            const div = document.createElement('div');
            div.className = 'flex gap-2';
            div.innerHTML = `
                <input type="text" name="mobile_numbers[]" placeholder="Another number..." class="flex-1 border p-2 rounded">
                <button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-700 px-3 rounded hover:bg-red-200 text-sm font-bold">X</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
