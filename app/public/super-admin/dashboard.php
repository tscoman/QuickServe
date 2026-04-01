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
    <style>
        .section-title { border-bottom: 2px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 16px; font-weight: 600; color: #374151; }
    </style>
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

        <!-- Add Restaurant Form -->
        <div class="bg-white rounded-lg shadow p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-semibold mb-6 text-blue-800">Register New Restaurant</h2>
            <form action="add_company.php" method="POST" class="space-y-6">
                
                <!-- Section 1: Basic Info -->
                <div>
                    <div class="section-title">Basic Information</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="name" placeholder="Restaurant Name *" required class="border p-2 rounded focus:ring-blue-500">
                        <input type="text" name="slug" placeholder="URL Slug (e.g., joes-pizza) *" required class="border p-2 rounded focus:ring-blue-500">
                        <input type="text" name="phone" placeholder="Phone Number" class="border p-2 rounded focus:ring-blue-500">
                    </div>
                </div>

                <!-- Section 2: Location & Legal -->
                <div>
                    <div class="section-title">Location & Legal Compliance</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <textarea name="street_address" placeholder="Street Address" rows="2" class="border p-2 rounded w-full focus:ring-blue-500"></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="vat_number" placeholder="VAT Number (Optional)" class="border p-2 rounded focus:ring-blue-500">
                            <input type="text" name="cr_number" placeholder="CR Number (Optional)" class="border p-2 rounded focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Digital Presence -->
                <div>
                    <div class="section-title">Digital Presence & Receipts</div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="website_url" placeholder="Website URL (Optional)" class="border p-2 rounded focus:ring-blue-500">
                        <input type="text" name="instagram_url" placeholder="Instagram URL (Optional)" class="border p-2 rounded focus:ring-blue-500">
                        <input type="text" name="whatsapp_number" placeholder="WhatsApp Number (e.g., +96891914282)" class="border p-2 rounded focus:ring-blue-500">
                    </div>
                    <p class="text-xs text-gray-500 mt-2">* WhatsApp number is used for 1-click receipt sharing to customers and daily sales reports to owners.</p>
                </div>

                <!-- Section 4: Appearance -->
                <div>
                    <div class="section-title">Appearance</div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <select name="theme" class="border p-2 rounded focus:ring-blue-500">
                            <option value="midnight">Midnight Luxury (Black/Gold)</option>
                            <option value="garden">Garden Fresh (Green)</option>
                            <option value="classic">Classic Diner (Red)</option>
                            <option value="rustic">Rustic Bakery (Brown)</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold transition">Save Restaurant</button>
                    </div>
                </div>

            </form>
        </div>

        <!-- Team & Tables Below... -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- SA Team -->
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

            <!-- Restaurants List -->
            <div class="lg:col-span-2 bg-white rounded-lg shadow overflow-hidden border">
                <div class="p-4 border-b bg-gray-50"><h2 class="text-lg font-semibold">Restaurants (<?= count($companies) ?>)</h2></div>
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-gray-100 text-gray-600 uppercase text-xs">
                        <tr><th class="p-3">Name</th><th class="p-3">Slug</th><th class="p-3">CR / VAT</th><th class="p-3">Theme</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($companies as $c): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3 font-semibold"><?= sanitize($c['name']) ?></td>
                                <td class="p-3 text-gray-500"><?= sanitize($c['slug']) ?></td>
                                <td class="p-3 text-xs"><?= sanitize($c['cr_number'] ?? '-') ?> / <?= sanitize($c['vat_number'] ?? '-') ?></td>
                                <td class="p-3"><span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= $c['theme'] ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
