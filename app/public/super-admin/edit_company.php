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
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        move_uploaded_file($_FILES["logo"]["tmp_name"], $dir . "/logo.webp");
        $pdo->prepare("UPDATE companies SET logo_url=? WHERE id=?")->execute(["/uploads/" . $company_id . "/logo.webp", $company_id]);
    }
    header("Location: dashboard.php?updated=1"); exit;
}

 $adm = $pdo->prepare("SELECT * FROM users WHERE company_id=? AND role=\"company_admin\" LIMIT 1");
 $adm->execute([$company_id]);
 $admin = $adm->fetch();
?>
<!DOCTYPE html>
<html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Edit <?= $company["name"] ?> - QrServe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head>
<body class="bg-gray-100 min-h-screen">
<nav class="bg-white shadow border-b px-6 py-3"><div class="flex items-center max-w-5xl mx-auto">
<a href="dashboard.php" class="text-blue-600 mr-4"><i class="fas fa-arrow-left"></i> Back</a>
<img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" class="h-8"><span class="font-bold text-xl ml-2">Edit: <?= htmlspecialchars($company["name"]) ?></span></div></nav>

<div class="max-w-4xl mx-auto p-6">
<?php if(isset($_GET["updated"])){ ?><div class="bg-green-100 text-green-700 p-3 rounded mb-4">Restaurant updated!</div><?php } ?>

<form method="POST" enctype="multipart/form-data" class="bg-white rounded-lg shadow p-6 space-y-4">
<div class="grid grid-cols-4 gap-4">
<div class="border-2 border-dashed rounded p-4 text-center cursor-pointer hover:bg-gray-50" onclick="document.getElementById(\"l\").click()">
<?php if($company["logo_url"]){ ?><img src="<?= $company["logo_url"] ?>" class="h-16 mx-auto"><?php }else{ ?><i class="fas fa-image text-3xl text-gray-300"><?php } ?>
<p class="text-xs text-blue-600 mt-2">Change Logo</p>
<input type="file" name="logo" id="l" class="hidden"></div>
<input type="text" name="name" value="<?= htmlspecialchars($company["name"]) ?>" required class="border rounded p-2" placeholder="Name">
<input type="text" name="slug" value="<?= htmlspecialchars($company["slug"]) ?>" required class="border rounded p-2" placeholder="Slug">
<input type="text" name="phone" value="<?= htmlspecialchars($company["phone"]??\"\") ?>" class="border rounded p-2" placeholder="Phone"></div>

<input type="text" name="custom_domain" value="<?= htmlspecialchars($company["custom_domain"]??\"\") ?>" class="border rounded p-2 mb-4" placeholder="Domain">
<textarea name="street_address" rows="2" class="w-full border rounded p-2 mb-4"><?= htmlspecialchars($company["street_address"]??\"\") ?></textarea>
<div class="grid grid-cols-2 gap-4 mb-4">
<input type="text" name="vat_number" value="<?= htmlspecialchars($company["vat_number"]??\"\") ?>" class="border rounded p-2" placeholder="VAT">
<input type="text" name="cr_number" value="<?= htmlspecialchars($company["cr_number"]??\"\") ?>" class="border rounded p-2" placeholder="CR"></div>
<div class="grid grid-cols-3 gap-4 mb-4">
<input type="text" name="website_url" value="<?= htmlspecialchars($company["website_url"]??\"\") ?>" class="border rounded p-2" placeholder="Website">
<input type="text" name="instagram_url" value="<?= htmlspecialchars($company["instagram_url"]??\"\") ?>" class="border rounded p-2" placeholder="Instagram">
<input type="text" name="whatsapp_number" value="<?= htmlspecialchars($company["whatsapp_number"]??\"\") ?>" class="border rounded p-2" placeholder="WhatsApp"></div>
<select name="theme" class="border rounded p-2 mb-4">
<option value="midnight" <?= ($company["theme"]??\"\")==\"midnight\"?"selected":"" ?>>Midnight Luxury</option>
<option value="garden" <?= ($company["theme"]??\"\")==\"garden\"?"selected":"" ?>>Garden Fresh</option>
<option value="classic" <?= ($company["theme"]??\"\")==\"classic\"?"selected":"" ?>>Classic Diner</option>
<option value="rustic" <?= ($company["theme"]??\"\")==\"rustic\"?"selected":"" ?>>Rustic Bakery</option></select>

<div class="bg-blue-50 p-4 rounded mb-4"><h4 class="font-bold text-blue-800 mb-2">Current Admin</h4>
<?php if($admin){ ?><p>Name: <?= htmlspecialchars($admin["name"]) ?></p><p>Email: <?= htmlspecialchars($admin["email"]) ?></p>
<?php }else{ ?><p class="text-red-600">No admin assigned</p><?php } ?></div>

<div class="flex justify-end pt-4 border-t">
<a href="dashboard.php" class="px-4 py-2 border rounded mr-2">Cancel</a>
<button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded"><i class="fas fa-save"></i> Save</button></div></form></div></body></html>