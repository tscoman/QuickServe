<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php'; // FIXED: Added missing functions file
requireRole('super_admin');

// Fetch all companies
 $companies = $pdo->query("SELECT * FROM companies ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - QrServe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen">
    <!-- Top Navbar -->
    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-8">
            <span class="font-bold text-gray-800 text-lg">QrServe Hub</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600"><?= sanitize($_SESSION['name']) ?></span>
            <a href="../../index.php?logout=1" class="text-sm bg-red-100 text-red-700 px-3 py-1 rounded hover:bg-red-200">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-6xl">
        <h1 class="text-3xl font-bold text-gray-800 mb-6">Restaurant Management</h1>

        <div class="bg-white rounded-lg shadow p-6 mb-8 border border-gray-200">
            <h2 class="text-xl font-semibold mb-4">Add New Restaurant</h2>
            <form action="add_company.php" method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input type="text" name="name" placeholder="Restaurant Name" required class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                <input type="text" name="slug" placeholder="URL Slug (e.g., joes-pizza)" required class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                <input type="text" name="phone" placeholder="Phone Number" class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                <input type="number" step="0.01" name="tax" placeholder="Tax % (e.g., 15.00)" required class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                <select name="theme" class="border p-2 rounded focus:ring-blue-500 focus:border-blue-500">
                    <option value="midnight">Midnight Luxury (Black/Gold)</option>
                    <option value="garden">Garden Fresh (Green)</option>
                    <option value="classic">Classic Diner (Red)</option>
                    <option value="rustic">Rustic Bakery (Brown)</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Add Restaurant</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm">
                        <th class="p-3">ID</th>
                        <th class="p-3">Restaurant Name</th>
                        <th class="p-3">Slug</th>
                        <th class="p-3">Theme</th>
                        <th class="p-3">Status</th>
                        <th class="p-3">Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($companies) > 0): ?>
                        <?php foreach ($companies as $c): ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-3"><?= $c['id'] ?></td>
                                <td class="p-3 font-semibold"><?= sanitize($c['name']) ?></td>
                                <td class="p-3 text-gray-500"><?= sanitize($c['slug']) ?></td>
                                <td class="p-3"><span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded"><?= $c['theme'] ?></span></td>
                                <td class="p-3"><span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded"><?= $c['status'] ?></span></td>
                                <td class="p-3 text-sm text-gray-500"><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="p-4 text-center text-gray-500">No restaurants added yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
