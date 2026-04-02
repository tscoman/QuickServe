<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin');
 $company_id = getCompanyId();
 $printers = $pdo->prepare("SELECT * FROM printers WHERE company_id = ? AND is_active = 1"); $printers->execute([$company_id]); $printers_list = $printers->fetchAll();
 $categories = $pdo->prepare("SELECT c.*, p.printer_name FROM categories c LEFT JOIN printers p ON c.printer_id = p.id WHERE c.company_id = ? ORDER BY c.sort_order ASC"); $categories->execute([$company_id]); $cats = $categories->fetchAll();
 $items = $pdo->prepare("SELECT mi.*, c.name as cat_name FROM menu_items mi JOIN categories c ON mi.category_id = c.id WHERE mi.company_id = ? ORDER BY mi.id DESC"); $items->execute([$company_id]); $menu_items = $items->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Manager - QrServe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="h-full flex flex-col text-gray-100">
    <header class="bg-gray-800 shadow-lg px-6 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-4"><a href="dashboard.php" class="text-blue-400 hover:text-blue-300 font-bold">← Dashboard</a><span class="text-2xl font-bold">🍔 Menu Manager</span></div>
    </header>
    <main class="flex-1 overflow-y-auto p-8">
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <div class="xl:col-span-1 space-y-6">
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700">
                    <h3 class="text-xl font-bold mb-4 text-yellow-400">📂 Categories & Printers</h3>
                    <form action="add_category.php" method="POST" class="space-y-3">
                        <input type="text" name="name" placeholder="Category Name" required class="w-full bg-gray-900 border border-gray-700 p-2 rounded">
                        <input type="number" name="sort_order" placeholder="Sort Order" value="0" class="w-full bg-gray-900 border border-gray-700 p-2 rounded">
                        <select name="printer_id" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-gray-400">
                            <option value="">-- Assign Printer (Optional) --</option>
                            <?php foreach($printers_list as $p): ?><option value="<?= $p['id'] ?>"><?= sanitize($p['printer_name']) ?></option><?php endforeach; ?>
                            <option value="none">No Auto-Print</option>
                        </select>
                        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-400 text-black font-bold p-2 rounded">Add Category</button>
                    </form>
                    <div class="mt-6 space-y-2">
                        <?php foreach ($cats as $cat): ?>
                            <div class="flex justify-between items-center bg-gray-900 p-3 rounded border-l-4 border-yellow-500">
                                <div><span class="font-bold"><?= sanitize($cat['cat_name']) ?></span><?php if ($cat['printer_name']): ?><span class="text-xs text-blue-400 block">🖨️ <?= sanitize($cat['printer_name']) ?></span><?php endif; ?></div>
                                <a href="toggle_category.php?id=<?= $cat['id'] ?>&s=<?= $cat['is_active'] ? 0 : 1 ?>" class="text-xs px-2 py-1 rounded <?= $cat['is_active'] ? 'bg-green-600' : 'bg-red-600' ?>"><?= $cat['is_active'] ? 'Active' : 'Hidden' ?></a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="xl:col-span-2">
                <div class="bg-gray-800 p-6 rounded-xl border border-gray-700 mb-6">
                    <h3 class="text-xl font-bold mb-4 text-green-400">⊕ Add New Item</h3>
                    <form action="add_item.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <input type="text" name="name" placeholder="Item Name *" required class="bg-gray-900 border border-gray-700 p-2 rounded">
                        <input type="number" step="0.01" name="price" placeholder="Price *" required class="bg-gray-900 border border-gray-700 p-2 rounded">
                        <select name="category_id" required class="bg-gray-900 border border-gray-700 p-2 rounded"><option value="">Select Category *</option><?php foreach ($cats as $cat): ?><option value="<?= $cat['id'] ?>"><?= sanitize($cat['cat_name']) ?></option><?php endforeach; ?></select>
                        <input type="file" name="image" accept="image/*" class="bg-gray-900 border border-gray-700 p-2 rounded file:text-green-400">
                        <textarea name="description" placeholder="Description" class="col-span-2 bg-gray-900 border border-gray-700 p-2 rounded h-20"></textarea>
                        <button type="submit" class="col-span-2 bg-green-600 hover:bg-green-500 text-white font-bold p-3 rounded text-lg">Save Item</button>
                    </form>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($menu_items as $item): ?>
                        <div class="bg-gray-800 rounded-xl border border-gray-700 overflow-hidden <?= !$item['is_available'] ? 'opacity-50' : '' ?>">
                            <div class="h-40 bg-gray-700 overflow-hidden"><?php if ($item['image_url']): ?><img src="<?= $item['image_url'] ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center text-gray-500 text-4xl">🍽️</div><?php endif; ?></div>
                            <div class="p-4 flex justify-between items-start">
                                <div><h4 class="text-lg font-bold"><?= sanitize($item['name']) ?></h4><p class="text-sm text-gray-400"><?= sanitize($item['cat_name']) ?></p><p class="text-xl font-black text-green-400 mt-1"><?= formatMoney($item['price']) ?></p></div>
                                <a href="toggle_item.php?id=<?= $item['id'] ?>&s=<?= $item['is_available'] ? 0 : 1 ?>" class="px-4 py-2 rounded-lg font-black text-sm <?= $item['is_available'] ? 'bg-green-600 hover:bg-red-600' : 'bg-red-600 hover:bg-green-600' ?>"><?= $item['is_available'] ? '✓ Available' : '⛔ 86\'D' ?></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($menu_items)): ?><div class="col-span-2 text-center py-20 text-gray-600 border-4 border-dashed border-gray-800 rounded-2xl"><p class="text-5xl mb-4">🛒</p><p class="text-xl font-bold">Menu is Empty</p></div><?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
