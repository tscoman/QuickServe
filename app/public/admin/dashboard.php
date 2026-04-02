<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";
requireRole("company_admin");

 $company_id = getCompanyId();

// Fetch company data
 $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
 $stmt->execute([$company_id]);
 $company = $stmt->fetch();

// Fetch additional data
 $stmt2 = $pdo->prepare("SELECT phone_number FROM mobile_payment_numbers WHERE company_id = ?");
 $stmt2->execute([$company_id]);
 $mobile_numbers = $stmt2->fetchAll();

 $stmt3 = $pdo->prepare("SELECT id, name, email FROM users WHERE company_id = ? AND role = 'staff'");
 $stmt3->execute([$company_id]);
 $staff_list = $stmt3->fetchAll();

// Handle form submissions
 $message = "";
 $error = "";

// Handle logo upload
if (isset($_POST['upload_logo'])) {
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] == UPLOAD_ERR_OK) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['logo']['type'], $allowed)) {
            $ext = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $filename = 'logo_' . time() . '.' . $ext;
            $upload_dir = '/opt/QrServe/uploads/' . $company_id . '/';
            
            if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
            
            if (move_uploaded_file($_FILES['logo']['tmp_name'], $upload_dir . $filename)) {
                $logo_path = '/uploads/' . $company_id . '/' . $filename;
                $upd = $pdo->prepare("UPDATE companies SET logo_url = ? WHERE id = ?");
                $upd->execute([$logo_path, $company_id]);
                $company['logo_url'] = $logo_path;
                $message = "✅ Logo updated successfully!";
            } else {
                $error = "❌ Failed to save file.";
            }
        } else {
            $error = "❌ Invalid file type. Use JPG, PNG, GIF, or WebP.";
        }
    } else {
        $error = "❌ No file selected or upload error.";
    }
}

// Handle company info update
if (isset($_POST['update_info'])) {
    $fields = ['street_address', 'website_url', 'instagram_url', 'whatsapp_number', 'phone'];
    $updates = [];
    foreach ($fields as $f) {
        if (isset($_POST[$f])) {
            $updates[$f] = $_POST[$f];
        }
    }
    
    if (!empty($updates)) {
        $set_parts = array_map(function($k){ return "$k = ?"; }, array_keys($updates));
        $sql = "UPDATE companies SET " . implode(", ", $set_parts) . " WHERE id = ?";
        $vals = array_values($updates);
        $vals[] = $company_id;
        $pdo->prepare($sql)->execute($vals);
        $message = "✅ Company information updated!";
        
        // Refresh company data
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$company_id]);
        $company = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($company['name']) ?> - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen text-white">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 p-4 flex flex-col">
            <!-- Company Info -->
            <div class="mb-6 pb-4 border-b border-gray-700">
                <?php if ($company['logo_url']): ?>
                <img src="<?= $company['logo_url'] ?>" alt="Logo" class="h-16 mx-auto object-contain mb-2" onerror="this.src='https://via.placeholder.com/150x50?text=Logo'">
                <?php else: ?>
                <div class="w-16 h-16 bg-gray-700 rounded mx-auto mb-2 flex items-center justify-center text-2xl"><?= strtoupper(substr($company['name'], 0, 1)) ?></div>
                <?php endif; ?>
                <h1 class="text-lg font-bold text-center truncate"><?= htmlspecialchars($company['name']) ?></h1>
                <p class="text-xs text-gray-400 text-center mt-1"><?= htmlspecialchars($company['street_address'] ?? 'Address not set') ?></p>
            </div>

            <!-- Navigation Tabs -->
            <nav class="space-y-2 flex-1 overflow-y-auto">
                <a href="?tab=dashboard" class="block px-4 py-2 rounded <?= ($_GET['tab']=='dashboard' || !isset($_GET['tab'])) ? 'bg-blue-600' : 'hover:bg-gray-700' ?> transition">
                    📊 Dashboard
                </a>
                <a href="?tab=settings" class="block px-4 py-2 rounded <?= ($_GET['tab']=='settings') ? 'bg-blue-600' : 'hover:bg-gray-700' ?> transition">
                    ⚙️ Company Settings
                </a>
                <a href="menu.php" class="block px-4 py-2 rounded hover:bg-gray-700 transition">🍔 Menu Manager</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-gray-700 transition">🖨️ Printers</a>
                <a href="#" class="block px-4 py-2 rounded hover:bg-gray-700 transition">📱 QR Codes</a>
            <a href="../index.php?logout=1" class="text-sm bg-red-100 text-red-700 px-4 py-2 rounded-lg hover:bg-red-200 font-bold ml-auto"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a></nav>

            <!-- Staff Section -->
            <div class="mt-auto pt-4 border-t border-gray-700">
                <h3 class="text-xs font-semibold text-gray-500 uppercase mb-2">Staff Members</h3>
                <form action="add_staff.php" method="POST" class="space-y-2 mb-3">
                    <input type="text" name="name" required placeholder="Name" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm">
                    <input type="email" name="email" required placeholder="Email" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm">
                    <input type="password" name="password" required placeholder="Password" class="w-full bg-gray-900 border border-gray-700 rounded px-3 py-2 text-sm">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-sm font-bold">+ Add Staff</button>
                </form>
                
                <?php foreach ($staff_list as $s): ?>
                <div class="flex justify-between items-center text-sm bg-gray-900 p-2 rounded mt-1">
                    <span><?= htmlspecialchars($s['name']) ?></span>
                    <a href="delete_staff.php?id=<?= $s['id'] ?>" class="text-red-400 hover:text-red-300">✕</a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer -->
            <div class="pt-4 border-t border-gray-700 text-center opacity-50">
                <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png" class="h-5 mx-auto mb-1">
                <p class="text-xs">Powered by QrServe</p>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8 overflow-y-auto">
            <?php if ($message): ?>
            <div class="bg-green-600 text-white p-4 rounded-lg mb-6"><?= $message ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
            <div class="bg-red-600 text-white p-4 rounded-lg mb-6"><?= $error ?></div>
            <?php endif; ?>

            <?php $current_tab = $_GET['tab'] ?? 'dashboard'; ?>

            <?php if ($current_tab == 'settings'): ?>
            <!-- COMPANY SETTINGS TAB -->
            <div>
                <h2 class="text-2xl font-bold mb-6 flex items-center gap-2">
                    ⚙️ Company Settings
                </h2>

                <!-- Logo Upload Section -->
                <div class="bg-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-yellow-400">📷 Restaurant Logo</h3>
                    
                    <div class="flex items-start gap-6">
                        <div class="flex-shrink-0">
                            <?php if ($company['logo_url']): ?>
                            <img src="<?= $company['logo_url'] ?>" alt="Current Logo" 
                                 class="w-32 h-32 object-cover rounded-lg border-2 border-gray-600"
                                 onerror="this.src='https://via.placeholder.com/128x128?text=No+Logo'">
                            <?php else: ?>
                            <div class="w-32 h-32 bg-gray-700 rounded-lg border-2 border-dashed border-gray-600 flex items-center justify-center text-gray-500">
                                No Logo
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <form action="" method="POST" enctype="multipart/form-data" class="flex-1">
                            <input type="hidden" name="upload_logo" value="1">
                            <label class="block w-full">
                                <span class="text-sm text-gray-400 mb-2 block">Upload New Logo</span>
                                <input type="file" name="logo" accept="image/*" 
                                       class="w-full text-sm text-gray-300 file:mr-4 file:py-2 px-4 py-2 bg-gray-900 border border-gray-700 rounded cursor-pointer hover:border-blue-500">
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, WebP (Max 2MB)</p>
                            </label>
                            <button type="submit" class="mt-3 bg-yellow-500 hover:bg-yellow-600 text-black font-bold py-2 px-6 rounded">
                                Upload New Logo
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Basic Information -->
                <form action="" method="POST" class="bg-gray-800 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold mb-4 text-blue-400">📋 Basic Information</h3>
                    <input type="hidden" name="update_info" value="1">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">Restaurant Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($company['name']) ?>" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2" readonly
                                   class="opacity-50 cursor-not-allowed">
                            <p class="text-xs text-gray-500">Contact support to change name</p>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">Phone Number</label>
                            <input type="text" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2">
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">Street Address</label>
                            <textarea name="street_address" rows="2" 
                                      class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2"><?= htmlspecialchars($company['street_address'] ?? '') ?></textarea>
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">Website</label>
                            <input type="url" name="website_url" value="<?= htmlspecialchars($company['website_url'] ?? '') ?>" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2" placeholder="https://...">
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">Instagram</label>
                            <input type="url" name="instagram_url" value="<?= htmlspecialchars($company['instagram_url'] ?? '') ?>" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2" placeholder="@username">
                        </div>
                        
                        <div>
                            <label class="text-sm text-gray-400 block mb-1">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" value="<?= htmlspecialchars($company['whatsapp_number'] ?? '') ?>" 
                                   class="w-full bg-gray-900 border border-gray-700 rounded px-4 py-2" placeholder="+968...">
                        </div>
                    </div>
                    
                    <button type="submit" class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-lg">
                        💾 Save Information
                    </button>
                </form>

                <!-- Bank Accounts -->
                <div class="bg-gray-800 rounded-lg p-6">
                    <h3 class="text-lg font-semibold mb-4 text-green-400">🏦 Bank Accounts</h3>
                    <div class="space-y-2">
                        <?php foreach ($mobile_numbers as $mn): ?>
                        <div class="flex justify-between items-center bg-gray-900 p-3 rounded border border-gray-700">
                            <span class="font-mono text-sm"><?= htmlspecialchars($mn['phone_number']) ?></span>
                            <span class="text-green-400 text-xs">● Active</span>
                        </div>
                        <?php endforeach; ?>
                        <?php if (empty($mobile_numbers)): ?>
                        <p class="text-gray-500 text-sm text-center py-4">No bank accounts added yet. Add them from Super Admin portal.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- DEFAULT DASHBOARD TAB -->
            <div>
                <h2 class="text-2xl font-bold mb-6">📊 Live Sales Tracker</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-gradient-to-br from-green-900/50 to-green-800/50 border-2 border-green-500 rounded-xl p-6 text-center">
                        <p class="text-sm text-green-400 uppercase tracking-wide">Total Revenue</p>
                        <p class="text-5xl font-black text-green-400 mt-2">0.000</p>
                        <p class="text-xs text-green-600 mt-1">OMR</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-900/50 to-blue-800/50 border-2 border-blue-500 rounded-xl p-6 text-center">
                        <p class="text-sm text-blue-400 uppercase tracking-wide">Total Orders</p>
                        <p class="text-5xl font-black text-blue-400 mt-2">0</p>
                        <p class="text-xs text-blue-600 mt-1">Today</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-900/50 to-orange-800/50 border-2 border-orange-500 rounded-xl p-6 text-center">
                        <p class="text-sm text-orange-400 uppercase tracking-wide">Pending Payments</p>
                        <p class="text-5xl font-black text-orange-400 mt-2">0</p>
                        <p class="text-xs text-orange-600 mt-1">Awaiting Proof</p>
                    </div>
                </div>

                <div class="bg-yellow-500 text-black p-6 rounded-2xl mb-8 flex justify-between items-center">
                    <div>
                        <h3 class="text-3xl font-black tracking-wider">⚠️ KITCHEN ALERTS</h3>
                        <p class="text-lg font-bold opacity-80 mt-1">Waiting for new orders...</p>
                    </div>
                    <div class="text-6xl font-black">0</div>
                </div>

                <div class="grid grid-cols-2 gap-8">
                    <div>
                        <h3 class="text-2xl font-bold mb-4 text-red-400">💳 Pending Payments</h3>
                        <div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center">
                            <p class="text-5xl mb-4">🧾</p>
                            <p class="text-xl font-bold text-gray-600">No Pending Payments</p>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold mb-4 text-green-400">👨‍🍳 Active Orders</h3>
                        <div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center">
                            <p class="text-5xl mb-4">🍽️</p>
                            <p class="text-xl font-bold text-gray-600">Kitchen is Ready</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>