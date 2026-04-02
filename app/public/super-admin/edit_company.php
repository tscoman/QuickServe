<?php
/**
 * QrServe Enterprise Restaurant Editor
 * Professional CRUD Interface for Super Admin
 * Version: 4.0.0 | Architecture: MIT-Grade
 */

require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";

// Strict role enforcement
requireRole("super_admin");

// Validate input
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: dashboard.php?error=invalid_request");
    exit;
}

 $company_id = (int)$_GET["id"];

// Fetch company with error handling
try {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$company) {
        throw new Exception("Company not found");
    }
} catch (Exception $e) {
    header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validate CSRF token would go here in production
    
    $update_fields = [
        'name' => $_POST["name"] ?? '',
        'slug' => $_POST["slug"] ?? '',
        'phone' => $_POST["phone"] ?? '',
        'street_address' => $_POST["street_address"] ?? '',
        'vat_number' => $_POST["vat_number"] ?? '',
        'cr_number' => $_POST["cr_number"] ?? '',
        'website_url' => $_POST["website_url"] ?? '',
        'instagram_url' => $_POST["instagram_url"] ?? '',
        'whatsapp_number' => $_POST["whatsapp_number"] ?? '',
        'custom_domain' => $_POST["custom_domain"] ?? '',
        'theme' => $_POST["theme"] ?? 'midnight'
    ];
    
    // Execute update
    $sql = "UPDATE companies SET name=?, slug=?, phone=?, street_address=?, vat_number=?, cr_number=?, website_url=?, instagram_url=?, whatsapp_number=?, custom_domain=?, theme=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $update_fields['name'],
        $update_fields['slug'],
        $update_fields['phone'],
        $update_fields['street_address'],
        $update_fields['vat_number'],
        $update_fields['cr_number'],
        $update_fields['website_url'],
        $update_fields['instagram_url'],
        $update_fields['whatsapp_number'],
        $update_fields['custom_domain'],
        $update_fields['theme'],
        $company_id
    ]);
    
    // Handle logo upload with security validation
    if (isset($_FILES["logo"]) && $_FILES["logo"]["error"] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["logo"]["tmp_name"]);
        finfo_close($finfo);
        
        if (in_array($mime, $allowed_types)) {
            $upload_dir = "/opt/QrServe/uploads/" . $company_id;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $ext = pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION);
            $filename = "logo." . ($ext ?: 'webp');
            $destination = $upload_dir . "/" . $filename;
            
            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $destination)) {
                $logo_path = "/uploads/" . $company_id . "/" . $filename;
                $pdo->prepare("UPDATE companies SET logo_url=? WHERE id=?")->execute([$logo_path, $company_id]);
            }
        }
    }
    
    header("Location: dashboard.php?updated=" . $company_id);
    exit;
}

// Fetch admin user for this company
 $stmt = $pdo->prepare("SELECT id, name, email, created_at FROM users WHERE company_id=? AND role='company_admin' LIMIT 1");
 $stmt->execute([$company_id]);
 $admin = $stmt->fetch(PDO::FETCH_ASSOC);

// Get order statistics for this company
 $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE company_id=?");
 $stmt->execute([$company_id]);
 $order_stats = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?= htmlspecialchars($company["name"]) ?> - QrServe Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .card-shadow { box-shadow: 0 10px 40px rgba(0,0,0,0.12); }
        .input-focus:focus { box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); border-color: #6366f1; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Top Navigation -->
    <nav class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="dashboard.php" class="text-indigo-600 hover:text-indigo-800 font-semibold flex items-center transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                    </a>
                    <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-10">
                    <h1 class="text-xl font-bold text-gray-800">Edit Restaurant</h1>
                </div>
                <div class="text-sm text-gray-500">
                    <i class="fas fa-clock mr-1"></i><?= date("Y-m-d H:i") ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-6 py-8">
        
        <!-- Success Message -->
        <?php if(isset($_GET["updated"])){ ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r flex items-center">
            <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
            <div>
                <p class="font-bold text-green-800">Restaurant Updated Successfully!</p>
                <p class="text-green-600 text-sm">All changes have been saved to the database.</p>
            </div>
        </div>
        <?php } ?>
        
        <!-- Error Message -->
        <?php if(isset($_GET["error"])){ ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r flex items-center">
            <i class="fas fa-exclamation-circle text-red-500 text-xl mr-3"></i>
            <div>
                <p class="font-bold text-red-800">Error</p>
                <p class="text-red-600 text-sm"><?= htmlspecialchars(urldecode($_GET["error"])) ?></p>
            </div>
        </div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-8">
            
            <!-- Header Section with Logo -->
            <div class="bg-white rounded-2xl shadow-lg p-8 card-shadow">
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
                    
                    <!-- Logo Upload Area -->
                    <div class="lg:col-span-1">
                        <label class="block font-bold text-gray-700 mb-3 text-sm uppercase tracking-wide">
                            <i class="fas fa-image mr-2"></i>Restaurant Logo
                        </label>
                        <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center cursor-pointer hover:border-indigo-400 hover:bg-indigo-50 transition-all group" onclick="document.getElementById('logo_upload').click()">
                            <div class="space-y-3">
                                <?php if(!empty($company['logo_url'])){ ?>
                                    <img src="<?= htmlspecialchars($company['logo_url']) ?>" alt="Logo" class="w-full h-24 object-contain mx-auto rounded-lg bg-gray-50 p-2">
                                <?php }else{ ?>
                                    <i class="fas fa-cloud-upload-alt text-5xl text-gray-300 group-hover:text-indigo-400 transition"></i>
                                <?php } ?>
                                <p class="text-sm font-semibold text-indigo-600 group-hover:text-indigo-800">
                                    <i class="fas fa-camera mr-1"></i>Click to Change Logo
                                </p>
                                <p class="text-xs text-gray-400">JPG, PNG or WebP</p>
                            </div>
                            <input type="file" name="logo" id="logo_upload" class="hidden" accept="image/*" onchange="document.getElementById('logo_preview').src=window.URL.createObjectURL(this.files[0])">
                        </div>
                    </div>

                    <!-- Basic Info -->
                    <div class="lg:col-span-4 grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="md:col-span-2">
                            <label class="block font-bold text-gray-700 mb-2 text-sm uppercase tracking-wide">
                                Restaurant Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="name" value="<?= htmlspecialchars($company['name']) ?>" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition" 
                                   placeholder="e.g., The Grill House">
                        </div>
                        
                        <div>
                            <label class="block font-bold text-gray-700 mb-2 text-sm uppercase tracking-wide">
                                URL Slug <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="slug" value="<?= htmlspecialchars($company['slug']) ?>" required 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition" 
                                   placeholder="the-grill-house">
                        </div>
                        
                        <div>
                            <label class="block font-bold text-gray-700 mb-2 text-sm uppercase tracking-wide">
                                Phone Number
                            </label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition" 
                                   placeholder="+968 XXXX XXXX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact & Legal Section -->
            <div class="bg-white rounded-2xl shadow-lg p-8 card-shadow">
                <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-address-card text-indigo-600 mr-3 text-xl"></i>Contact & Legal Information
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <textarea name="street_address" rows="3" 
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus transition resize-none" 
                                  placeholder="Full street address..."><?= htmlspecialchars($company['street_address'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2"><i class="fas fa-file-invoice-dollar mr-2 text-green-600"></i>VAT Number</label>
                            <input type="text" name="vat_number" value="<?= htmlspecialchars($company['vat_number'] ?? '') ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="VAT registration number">
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-2"><i class="fas fa-file-contract mr-2 text-blue-600"></i>CR Number</label>
                            <input type="text" name="cr_number" value="<?= htmlspecialchars($company['cr_number'] ?? '') ?>" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="Commercial Registration">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Digital Presence -->
            <div class="bg-white rounded-2xl shadow-lg p-8 card-shadow">
                <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-globe text-blue-600 mr-3 text-xl"></i>Digital Presence & Social Media
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2"><i class="fas fa-globe mr-2"></i>Website URL</label>
                        <input type="url" name="website_url" value="<?= htmlspecialchars($company['website_url'] ?? '') ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="https://...">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2"><i class="fab fa-instagram mr-2 text-pink-600"></i>Instagram</label>
                        <input type="url" name="instagram_url" value="<?= htmlspecialchars($company['instagram_url'] ?? '') ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="@username">
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2"><i class="fab fa-whatsapp mr-2 text-green-600"></i>WhatsApp</label>
                        <input type="tel" name="whatsapp_number" value="<?= htmlspecialchars($company['whatsapp_number'] ?? '') ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="+968 XXXX XXXX">
                    </div>
                </div>
            </div>

            <!-- Network & Theme -->
            <div class="bg-white rounded-2xl shadow-lg p-8 card-shadow">
                <h2 class="text-lg font-bold text-gray-800 mb-6 flex items-center">
                    <i class="fas fa-server text-purple-600 mr-3 text-xl"></i>Network & Appearance
                </h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2"><i class="fas fa-link mr-2"></i>Custom Domain (Optional)</label>
                        <input type="text" name="custom_domain" value="<?= htmlspecialchars($company['custom_domain'] ?? '') ?>" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus" placeholder="orders.restaurant.com">
                        <p class="text-xs text-gray-500 mt-2"><i class="fas fa-info-circle mr-1"></i>Leave blank to use auto-assigned port number</p>
                    </div>
                    
                    <div>
                        <label class="block font-semibold text-gray-700 mb-2"><i class="fas fa-palette mr-2"></i>UI Theme</label>
                        <select name="theme" class="w-full px-4 py-3 border border-gray-300 rounded-lg input-focus bg-white">
                            <option value="midnight" <?= $company['theme']=='midnight'?'selected':'' ?>>🌙 Midnight Luxury - Fine Dining</option>
                            <option value="garden" <?= $company['theme']=='garden'?'selected:'' ?>>🌿 Garden Fresh - Cafe/Vegan</option>
                            <option value="classic" <?= $company['theme']=='classic'?'selected':'' ?>>❤️ Classic Diner - Fast Food</option>
                            <option value="rustic" <?= $company['theme']=='rustic'?'selected':'' ?>>🤎 Rustic Bakery - Coffee Shop</option>
                        </select>
                    </div>
                </div>
                
                <!-- Stats Display -->
                <div class="mt-6 pt-6 border-t border-gray-200 grid grid-cols-3 gap-4 text-center">
                    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-lg p-4">
                        <p class="text-2xl font-black text-indigo-600"><?= $order_stats['total_orders'] ?? 0 ?></p>
                        <p class="text-xs text-gray-600 font-semibold uppercase mt-1">Total Orders</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-4">
                        <p class="text-2xl font-black text-green-600"><?= $company['port_number'] ?? 'N/A' ?></p>
                        <p class="text-xs text-gray-600 font-semibold uppercase mt-1">Port Number</p>
                    </div>
                    <div class="bg-gradient-to-br from-orange-50 to-yellow-50 rounded-lg p-4">
                        <p class="text-2xl font-black text-orange-600"><?= ucfirst($company['theme'] ?? 'midnight') ?></p>
                        <p class="text-xs text-gray-600 font-semibold uppercase mt-1">Active Theme</p>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end space-x-4">
                <a href="dashboard.php" class="px-8 py-4 border-2 border-gray-300 rounded-lg font-bold text-gray-700 hover:bg-gray-50 transition">
                    <i class="fas fa-times mr-2"></i>Cancel
                </a>
                <button type="submit" class="px-10 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-bold text-lg hover:from-indigo-700 hover:to-purple-700 transition shadow-lg transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i>SAVE ALL CHANGES
                </button>
            </div>
        </form>

        <!-- Admin Account Info Card -->
        <?php if($admin){ ?>
        <div class="mt-8 bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl shadow-xl p-8 text-white">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-xl font-bold mb-2 flex items-center">
                        <i class="fas fa-user-shield mr-3 text-yellow-400"></i>Restaurant Administrator
                    </h3>
                    <div class="mt-4 space-y-2 text-sm">
                        <p><span class="text-gray-400">Name:</span> <?= htmlspecialchars($admin['name'] ?? 'N/A') ?></p>
                        <p><span class="text-gray-400">Email:</span> <code class="bg-slate-700 px-2 py-1 rounded"><?= htmlspecialchars($admin['email'] ?? 'N/A') ?></code></p>
                        <p><span class="text-gray-400">Created:</span> <?= $admin['created_at'] ?? 'N/A' ?></p>
                    </div>
                </div>
                <div class="text-right">
                    <i class="fas fa-shield-alt text-6xl text-yellow-400 opacity-20"></i>
                </div>
            </div>
        </div>
        <?php } ?>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12 py-6">
        <div class="max-w-7xl mx-auto px-6 text-center text-gray-500 text-sm">
            <p>&copy; <?= date('Y') ?> Technology Solutions Company (TSCO Group) | QrServe Enterprise System</p>
        </div>
    </footer>

</body>
</html>