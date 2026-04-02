<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";
requireRole("super_admin");

 $companies = $pdo->query("SELECT * FROM companies ORDER BY id DESC")->fetchAll();
 $sa_users = $pdo->query("SELECT id, name, email, created_at FROM users WHERE role = \"super_admin\" ORDER BY id ASC")->fetchAll();

 $view_id = isset($_GET["view"]) ? (int)$_GET["view"] : null;
 $edit_id = isset($_GET["edit"]) ? (int)$_GET["edit"] : null;
 $del_id = isset($_GET["delete"]) ? (int)$_GET["delete"] : null;

if ($del_id) {
    $stmt = $pdo->prepare("DELETE FROM companies WHERE id = ?");
    $stmt->execute([$del_id]);
    header("Location: dashboard.php?deleted=1");
    exit;
}

 $view_company = null;
if ($view_id) {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$view_id]);
    $view_company = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QrServe Hub - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg border-b px-6 py-3">
        <div class="flex justify-between items-center max-w-7xl mx-auto">
            <div class="flex items-center space-x-3">
                <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-10">
                <div>
                    <span class="font-bold text-xl text-gray-800">QrServe</span>
                    <span class="text-xs text-gray-500 block">Super Admin Portal</span>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <a href="profile.php" class="text-sm text-blue-600 hover:text-blue-800"><i class="fas fa-user-cog mr-1"></i>Profile</a>
                <a href="../../index.php?logout=1" class="text-sm bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition"><i class="fas fa-sign-out-alt mr-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div><i class="fas fa-building text-3xl opacity-80"></i></div>
                    <div class="text-right">
                        <p class="text-3xl font-bold"><?= count($companies) ?></p>
                        <p class="text-xs opacity-80">Total Restaurants</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div><i class="fas fa-check-circle text-3xl opacity-80"></i></div>
                    <div class="text-right">
                        <p class="text-3xl font-bold"><?= count(array_filter($companies, fn($c) => $c["is_active"] ?? 1)) ?></p>
                        <p class="text-xs opacity-80">Active</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div><i class="fas fa-users text-3xl opacity-80"></i></div>
                    <div class="text-right">
                        <p class="text-3xl font-bold"><?= count($sa_users) ?></p>
                        <p class="text-xs opacity-80">SA Team Members</p>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-orange-500 to-orange-700 rounded-xl p-6 text-white shadow-lg">
                <div class="flex items-center justify-between">
                    <div><i class="fas fa-network-wired text-3xl opacity-80"></i></div>
                    <div class="text-right">
                        <p class="text-3xl font-bold"><?= count(array_filter($companies, fn($c) => !empty($c["port_number"]))) ?></p>
                        <p class="text-xs opacity-80">Ports Active</p>
                    </div>
                </div>
            </div>
        </div>

        <?php if ($view_company): ?>
        <!-- View Company Modal -->
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6 border-b flex justify-between items-center">
                    <h2 class="text-2xl font-bold"><i class="fas fa-eye mr-2 text-blue-500"></i><?= htmlspecialchars($view_company["name"]) ?> - Details</h2>
                    <a href="dashboard.php" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</a>
                </div>
                <div class="p-6 grid grid-cols-2 gap-6">
                    <div><p class="text-sm text-gray-500">Company ID</p><p class="font-semibold">#<?= $view_company["id"] ?></p></div>
                    <div><p class="text-sm text-gray-500">Name</p><p class="font-semibold"><?= htmlspecialchars($view_company["name"]) ?></p></div>
                    <div><p class="text-sm text-gray-500">Slug</p><p class="font-mono text-sm bg-gray-100 p-2 rounded"><?= htmlspecialchars($view_company["slug"]) ?></p></div>
                    <div><p class="text-sm text-gray-500">Port</p><p class="font-semibold text-green-600"><?= $view_company["port_number"] ?? "Not Assigned" ?></p></div>
                    <div><p class="text-sm text-gray-500">Phone</p><p class="font-semibold"><?= htmlspecialchars($view_company["phone"] ?? "N/A") ?></p></div>
                    <div><p class="text-sm text-gray-500">Theme</p><p class="font-semibold capitalize"><?= $view_company["theme"] ?? "default" ?></p></div>
                    <div class="col-span-2"><p class="text-sm text-gray-500">Address</p><p class="font-semibold"><?= htmlspecialchars($view_company["street_address"] ?? "N/A") ?></p></div>
                    <div><p class="text-sm text-gray-500">VAT Number</p><p class="font-mono"><?= htmlspecialchars($view_company["vat_number"] ?? "N/A") ?></p></div>
                    <div><p class="text-sm text-gray-500">CR Number</p><p class="font-mono"><?= htmlspecialchars($view_company["cr_number"] ?? "N/A") ?></p></div>
                    <div><p class="text-sm text-gray-500">WhatsApp</p><p class="font-semibold text-green-600"><?= htmlspecialchars($view_company["whatsapp_number"] ?? "N/A") ?></p></div>
                    <div><p class="text-sm text-gray-500">Website</p><a href="<?= htmlspecialchars($view_company["website_url"] ?? "#") ?>" target="_blank" class="text-blue-600 hover:underline"><?= htmlspecialchars($view_company["website_url"] ?? "N/A") ?></a></div>
                    <div class="col-span-2"><p class="text-sm text-gray-500">Logo</p><?php if ($view_company["logo_url"]): ?><img src="<?= htmlspecialchars($view_company["logo_url"]) ?>" class="h-20 object-contain border rounded p-2"><?php else: ?><p class="text-gray-400 italic">No logo uploaded</p><?php endif; ?></div>
                </div>
                <div class="p-6 border-t bg-gray-50 flex justify-end space-x-3">
                    <a href="dashboard.php?edit=<?= $view_company["id"] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition"><i class="fas fa-edit mr-1"></i>Edit Settings</a>
                    <a href="dashboard.php" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg transition">Close</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left: SA Team -->
            <div class="bg-white rounded-xl shadow-lg p-6 border">
                <h3 class="text-lg font-bold mb-4 pb-2 border-b"><i class="fas fa-users-cog mr-2 text-purple-500"></i>SA Team Management</h3>
                <form action="add_sa_user.php" method="POST" class="space-y-3 mb-4">
                    <input type="text" name="name" placeholder="Full Name" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="email" name="email" placeholder="Email Address" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <input type="password" name="password" placeholder="Password" required class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 rounded-lg transition"><i class="fas fa-user-plus mr-1"></i>Add Team Member</button>
                </form>
                <div class="space-y-2 max-h-64 overflow-y-auto">
                    <?php foreach ($sa_users as $u): ?>
                        <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-2">
                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 font-bold text-sm"><?= strtoupper(substr($u["name"], 0, 1)) ?></div>
                                <div><p class="font-medium text-sm"><?= htmlspecialchars($u["name"]) ?></p><p class="text-xs text-gray-400"><?= htmlspecialchars($u["email"]) ?></p></div>
                            </div>
                            <span class="text-xs text-green-500"><i class="fas fa-circle text-[8px]"></i> Active</span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Right: Restaurants Table -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow-lg overflow-hidden border">
                <div class="p-6 bg-gradient-to-r from-blue-600 to-blue-800 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white"><i class="fas fa-utensils mr-2"></i>Restaurant Instances (<?= count($companies) ?>)</h3>
                    <span class="text-blue-200 text-sm"><i class="fas fa-database mr-1"></i>Multi-Tenant SaaS</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Restaurant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Access URL</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Admin</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php 
                            foreach ($companies as $c): 
                                $adm = $pdo->prepare("SELECT email FROM users WHERE company_id = ? AND role=\"company_admin\" LIMIT 1");
                                $adm->execute([$c["id"]]);
                                $admin_email = $adm->fetchColumn();
                                $access_url = $c["custom_domain"] ? "https://" . $c["custom_domain"] : "http://prmx.omanwebhosting.com:" . ($c["port_number"] ?? "xxxx");
                            ?>
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-4 py-3">
                                        <div class="flex items-center space-x-3">
                                            <?php if ($c["logo_url"]): ?>
                                                <img src="<?= htmlspecialchars($c["logo_url"]) ?>" class="w-10 h-10 rounded-lg object-cover border">
                                            <?php else: ?>
                                                <div class="w-10 h-10 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400"><i class="fas fa-store"></i></div>
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-semibold text-gray-800"><?= htmlspecialchars($c["name"]) ?></p>
                                                <p class="text-xs text-gray-400">#<?= $c["id"] ?> | <?= htmlspecialchars($c["slug"]) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="<?= $access_url ?>" target="_blank" class="text-blue-600 hover:text-blue-800 font-mono text-sm font-medium hover:underline">
                                            <i class="fas fa-external-link-alt mr-1"></i><?= $access_url ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600"><?= htmlspecialchars($admin_email ?? "No Admin") ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-circle text-[6px] mr-1"></i>Active
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center justify-center space-x-2">
                                            <a href="?view=<?= $c["id"] ?>" class="bg-blue-100 hover:bg-blue-200 text-blue-700 p-2 rounded-lg transition" title="View Details"><i class="fas fa-eye"></i></a>
                                            <a href="?edit=<?= $c["id"] ?>" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 p-2 rounded-lg transition" title="Edit Settings"><i class="fas fa-edit"></i></a>
                                            <a href="delete_company.php?id=<?= $c["id"] ?>" class="bg-red-100 hover:bg-red-200 text-red-700 p-2 rounded-lg transition" title="Delete Restaurant" onclick="return confirm(\"Are you sure? This will DELETE everything!\")"><i class="fas fa-trash-alt"></i></a>
                                            <?php if ($c["port_number"]): ?>
                                                <a href="<?= $access_url ?>" target="_blank" class="bg-green-100 hover:bg-green-200 text-green-700 p-2 rounded-lg transition" title="Open Portal"><i class="fas fa-external-link-alt"></i></a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($companies)): ?>
                                <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400"><i class="fas fa-inbox text-4xl mb-3 block"></i>No restaurants registered yet</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Add Restaurant Form -->
        <div class="mt-8 bg-white rounded-xl shadow-lg p-6 border">
            <h3 class="text-xl font-bold mb-6 text-blue-800"><i class="fas fa-plus-circle mr-2"></i>Register New Restaurant Instance</h3>
            <form action="add_company.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div class="col-span-1 flex flex-col items-center justify-center border-2 border-dashed border-gray-300 rounded-xl p-4 bg-gray-50 cursor-pointer hover:bg-gray-100 transition" onclick="document.getElementById(\"logoInput\").click()">
                        <img id="logoPreview" src="https://via.placeholder.com/150x50?text=Logo" class="h-16 object-contain mb-2">
                        <label class="text-xs text-blue-600 font-semibold cursor-pointer"><i class="fas fa-upload mr-1"></i>Upload Logo</label>
                        <input type="file" name="logo" accept="image/*" id="logoInput" class="hidden" onchange="document.getElementById(\"logoPreview\").src = window.URL.createObjectURL(this.files[0])">
                    </div>
                    <input type="text" name="name" placeholder="Restaurant Name *" required class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="slug" placeholder="URL Slug *" required class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    <input type="text" name="phone" placeholder="Phone Number" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-globe mr-1"></i>Custom Domain / Subdomain</label>
                        <input type="text" name="custom_domain" placeholder="orders.restaurant.com" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-red-500 mt-1">* DNS A-Record must point to VPS IP before saving</p>
                    </div>
                    <div class="flex items-end text-sm text-gray-500 bg-gray-50 p-3 rounded-lg border"><i class="fas fa-info-circle mr-2 text-blue-500"></i>If left blank, system auto-assigns isolated port (e.g., :8081, :8082)</div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-map-marker-alt mr-1"></i>Location & Legal Information</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <textarea name="street_address" placeholder="Street Address" rows="2" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500"></textarea>
                        <div class="grid grid-cols-2 gap-4">
                            <input type="text" name="vat_number" placeholder="VAT Number" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <input type="text" name="cr_number" placeholder="CR Number" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-share-alt mr-1"></i>Digital Presence & Payment</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="website_url" placeholder="Website URL" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="instagram_url" placeholder="Instagram URL" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                        <input type="text" name="whatsapp_number" placeholder="WhatsApp (+968...)" class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div id="mobile-numbers-container" class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2"><i class="fas fa-mobile-alt mr-1"></i>Mobile Bank Transfer Numbers</label>
                    <div class="flex gap-2">
                        <input type="text" name="mobile_numbers[]" placeholder="+968 XXXX XXXX (Bank Name)" class="flex-1 border border-gray-300 rounded-lg px-4 py-2">
                        <button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded-lg hover:bg-red-200"><i class="fas fa-times"></i></button>
                    </div>
                    <button type="button" onclick="addMobileField()" class="text-sm bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 font-medium"><i class="fas fa-plus mr-1"></i>Add Another Bank Account</button>
                </div>

                <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                    <label class="block text-sm font-bold text-red-700 mb-3"><i class="fas fa-user-shield mr-1"></i>Restaurant Admin Login Credentials</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <input type="text" name="admin_name" placeholder="Admin Full Name *" required class="border border-red-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 bg-white">
                        <input type="email" name="admin_email" placeholder="Admin Email *" required class="border border-red-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 bg-white">
                        <input type="password" name="admin_password" placeholder="Admin Password *" required minlength="8" class="border border-red-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-red-500 bg-white">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1"><i class="fas fa-palette mr-1"></i>UI Theme</label>
                        <select name="theme" class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="midnight">🖤 Midnight Luxury (Fine Dining)</option>
                            <option value="garden">💚 Garden Fresh (Cafes/Vegan)</option>
                            <option value="classic">❤️ Classic Diner (Fast Food)</option>
                            <option value="rustic">🤎 Rustic Bakery (Coffee Shops)</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition transform hover:scale-[1.02] active:scale-[0.98]">
                            <i class="fas fa-server mr-2"></i>Provision Server & Create Instance
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-gray-800 text-gray-400 text-center py-4 mt-12 text-sm">
        <p>&copy; <?= date("Y") ?> <strong>Technology Solutions Company (TSCO Group)</strong> | QrServe Enterprise SaaS Platform</p>
    </footer>

    <script>
    function addMobileField() {
        const c = document.getElementById("mobile-numbers-container");
        const d = document.createElement("div");
        d.className = "flex gap-2";
        d.innerHTML = `<input type="text" name="mobile_numbers[]" placeholder="Another bank number..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2"><button type="button" onclick="this.parentElement.remove()" class="bg-red-100 text-red-600 px-3 rounded-lg hover:bg-red-200"><i class="fas fa-times"></i></button>`;
        c.insertBefore(d, c.lastElementChild);
    }
    </script>
</body>
</html>