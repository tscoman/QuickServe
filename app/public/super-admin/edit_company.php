<?php
require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";
requireRole("super_admin");

if (!isset($_GET["id"])) { header("Location: dashboard.php"); exit; }

 $company_id = (int)$_GET["id"];
 $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
 $stmt->execute([$company_id]);
 $company = $stmt->fetch();

if (!$company) { header("Location: dashboard.php?error=not_found"); exit; }

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $pdo->prepare("UPDATE companies SET name=?, slug=?, phone=?, street_address=?, vat_number=?, cr_number=?, website_url=?, instagram_url=?, whatsapp_number=?, custom_domain=?, theme=? WHERE id=?")->execute([
        $_POST["name"], $_POST["slug"], $_POST["phone"], $_POST["street_address"],
        $_POST["vat_number"], $_POST["cr_number"], $_POST["website_url"], $_POST["instagram_url"],
        $_POST["whatsapp_number"], $_POST["custom_domain"], $_POST["theme"], $company_id
    ]);
    
    if (!empty($_FILES["logo"]["tmp_name"])) {
        $dir = "/opt/QrServe/uploads/" . $company_id;
        if (!is_dir($dir)) { mkdir($dir, 0755, true); }
        move_uploaded_file($_FILES["logo"]["tmp_name"], $dir . "/logo.webp");
        $pdo->prepare("UPDATE companies SET logo_url=? WHERE id=?")->execute(["/uploads/" . $company_id . "/logo.webp", $company_id]);
    }
    
    header("Location: dashboard.php?updated=" . $company_id);
    exit;
}

 $stmt = $pdo->prepare("SELECT * FROM users WHERE company_id=? AND role='company_admin' LIMIT 1");
 $stmt->execute([$company_id]);
 $admin = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Restaurant</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen">
<nav class="bg-white shadow px-6 py-4">
<div class="max-w-6xl mx-auto flex items-center">
<a href="dashboard.php" class="text-blue-600 font-bold mr-4"><i class="fas fa-arrow-left"></i> Back</a>
<img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-8 mr-3">
<span class="text-xl font-bold">Edit: <?= htmlspecialchars($company["name"]) ?></span>
</div>
</nav>

<div class="max-w-4xl mx-auto p-8 mt-8 bg-white rounded-lg shadow-lg">

<?php if(isset($_GET["updated"])){ ?>
<div class="bg-green-100 border-l-4 border-green-500 p-4 mb-6 rounded font-bold text-green-800">
<i class="fas fa-check-circle mr-2"></i>Restaurant updated successfully!
</div>
<?php } ?>

<form method="POST" enctype="multipart/form-data" class="space-y-6">

<div class="grid grid-cols-4 gap-4 mb-6">
<div class="border-2 border-dashed rounded-lg p-6 text-center cursor-pointer hover:bg-gray-50" onclick="document.getElementById('l').click()">
<?php if($company["logo_url"]){ ?>
<img src="<?= $company['logo_url'] ?>" class="h-20 mx-auto object-contain">
<?php }else{ ?>
<i class="fas fa-image text-4xl text-gray-300"></i>
<?php } ?>
<p class="text-sm text-blue-600 mt-2 font-semibold">Change Logo</p>
<input type="file" name="logo" id="l" class="hidden" accept="image/*">
</div>

<input type="text" name="name" value="<?= htmlspecialchars($company['name']) ?>" required placeholder="Restaurant Name *" class="border p-3 rounded focus:ring-2 focus:ring-blue-500">
<input type="text" name="slug" value="<?= htmlspecialchars($company['slug']) ?>" required placeholder="URL Slug *" class="border p-3 rounded focus:ring-2 focus:ring-blue-500">
<input type="text" name="phone" value="<?= htmlspecialchars($company['phone'] ?? '') ?>" placeholder="Phone Number" class="border p-3 rounded focus:ring-2 focus:ring-blue-500">
</div>

<div>
<label class="font-bold block mb-2 text-gray-700"><i class="fas fa-map-marker-alt mr-2"></i>Address & Legal</label>
<textarea name="street_address" rows="2" class="w-full border p-3 rounded focus:ring-2 focus:ring-blue-500"><?= htmlspecialchars($company['street_address'] ?? '') ?></textarea>
<div class="grid grid-cols-2 gap-4 mt-2">
<input type="text" name="vat_number" value="<?= htmlspecialchars($company['vat_number'] ?? '') ?>" placeholder="VAT Number" class="border p-3 rounded">
<input type="text" name="cr_number" value="<?= htmlspecialchars($company['cr_number'] ?? '') ?>" placeholder="CR Number" class="border p-3 rounded">
</div>
</div>

<div>
<label class="font-bold block mb-2 text-gray-700"><i class="fas fa-globe mr-2"></i>Digital Presence</label>
<div class="grid grid-cols-3 gap-4">
<input type="text" name="website_url" value="<?= htmlspecialchars($company['website_url'] ?? '') ?>" placeholder="Website URL" class="border p-3 rounded">
<input type="text" name="instagram_url" value="<?= htmlspecialchars($company['instagram_url'] ?? '') ?>" placeholder="Instagram" class="border p-3 rounded">
<input type="text" name="whatsapp_number" value="<?= htmlspecialchars($company['whatsapp_number'] ?? '') ?>" placeholder="WhatsApp" class="border p-3 rounded">
</div>
</div>

<div>
<label class="font-bold block mb-2 text-gray-700"><i class="fas fa-server mr-2"></i>Network & Theme</label>
<input type="text" name="custom_domain" value="<?= htmlspecialchars($company['custom_domain'] ?? '') ?>" placeholder="Custom Domain (optional)" class="w-full border p-3 rounded mb-4">
<select name="theme" class="w-full border p-3 rounded bg-white">
<option value="midnight" <?= $company['theme']=='midnight'?'selected':'' ?>>Midnight Luxury</option>
<option value="garden" <?= $company['theme']=='garden'?'selected':'' ?>>Garden Fresh</option>
<option value="classic" <?= $company['theme']=='classic'?'selected':'' ?>>Classic Diner</option>
<option value="rustic" <?= $company['theme']=='rustic'?'selected':'' ?>>Rustic Bakery</option>
</select>
</div>

<button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-lg font-bold text-lg hover:bg-blue-700 shadow mt-6">
<i class="fas fa-save mr-2"></i>SAVE ALL CHANGES
</button>
</form>

<?php if($admin){ ?>
<div class="mt-8 bg-gray-50 p-6 rounded-lg border">
<h3 class="font-bold mb-2"><i class="fas fa-user-shield mr-2"></i>Admin Account Info</h3>
<p><strong>Email:</strong> <code><?= htmlspecialchars($admin['email']) ?></code></p>
<p><strong>Created:</strong> <?= $admin['created_at'] ?></p>
</div>
<?php } ?>

</div>
<footer class="text-center py-6 text-gray-500 text-sm mt-12 border-t">
<p>&copy; 2026 Technology Solutions Company (TSCO Group) | QrServe Enterprise System</p>
</footer>
</body>
</html>
