<?php
require_once __DIR__ . "/../includes/config.php";
require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../includes/functions.php";

if (isset($_GET["logout"])) { 
    session_destroy(); 
    header("Location: index.php"); 
    exit(); 
}

if (isLoggedIn()) {
    $role = $_SESSION["role"] ?? "";
    if ($role === "super_admin") {
        if ($is_sa_portal) { redirect("super-admin/dashboard.php"); }
        else { session_destroy(); header("Location: index.php?error=unauthorized_role"); exit(); }
    }
    if ($role === "company_admin") {
        if (!$is_sa_portal && $global_company_id) {
            if (($_SESSION["company_id"] ?? null) != $global_company_id) {
                session_destroy();
                header("Location: index.php?error=unauthorized_role");
                exit();
            }
        }
        redirect("admin/dashboard.php");
    }
    if ($role === "staff") { redirect("staff/orders.php"); }
    redirect("index.php?error=unauthorized_role");
}

 $company = null;
 $logo_url = "https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png";
 $portal_name = "QrServe Admin";
 $portal_tagline = "A revolutionary app for restaurants and cafes";

if (!$is_sa_portal && $global_company_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
        $stmt->execute([$global_company_id]);
        $company = $stmt->fetch();
        
        if ($company) {
            // Determine base URL dynamically
            $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $base_url .= $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'];
            
            // Logo priority: DB field > uploads folder > default
            $db_logo = trim($company["logo_url"] ?? "");
            
            if (!empty($db_logo)) {
                // If it starts with /uploads/, make it absolute
                if (strpos($db_logo, "/uploads/") === 0) {
                    $logo_url = $base_url . $db_logo;
                } elseif (strpos($db_logo, "http") === 0) {
                    $logo_url = $db_logo; // Already full URL
                } else {
                    $logo_url = $base_url . "/uploads/" . $db_logo;
                }
            }
            
            $portal_name = htmlspecialchars($company["name"]);
            $portal_tagline = "Welcome! Please sign in to manage your restaurant.";
        }
    } catch (Exception $e) {
        error_log("Error: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $portal_name ?> - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex justify-center items-center p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md border-t-4 border-blue-600">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <?php if (!$is_sa_portal): ?>
            <div class="w-32 h-32 rounded-full bg-gray-100 border-4 border-gray-200 p-2 flex items-center justify-center overflow-hidden">
                <img src="<?= $logo_url ?>" alt="<?= $portal_name ?>" 
                     class="max-w-full max-h-full object-contain"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <span class="text-2xl font-bold text-gray-400 hidden"><?= substr($portal_name, 0, 1) ?></span>
            </div>
            <?php else: ?>
            <img src="<?= $logo_url ?>" alt="Logo" class="h-16 object-contain" onerror="this.src='https://via.placeholder.com/150x50?text=TSCO'">
            <?php endif; ?>
        </div>
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2"><?= $portal_name ?></h2>
        <p class="text-center text-gray-500 mb-8 text-sm"><?= $portal_tagline ?></p>
        
        <?php if (isset($_GET["error"])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center font-semibold">
            <?= ["login_required"=>"Session expired","invalid_credentials"=>"Invalid credentials","unauthorized_role"=>"Access denied"][$_GET["error"]] ?? "Error" ?>
        </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="space-y-4">
            <input type="email" name="email" required placeholder="Email" autocomplete="email" class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" required placeholder="Password" minlength="6" autocomplete="current-password" class="w-full px-4 py-3 rounded-lg border focus:ring-2 focus:ring-blue-500 mt-3">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg mt-4">Sign In</button>
        </form>

        <div class="mt-4 text-center"><a href="forgot_password.php" class="text-sm text-blue-600">Forgot Password?</a></div>

        <div class="mt-6 pt-4 border-t text-center text-xs text-gray-400 space-y-1">
            <?php if ($is_sa_portal): ?>
                <p class="font-semibold">Super Admin Portal</p>
                <p>Technology Solutions Company (TSCO Group)</p>
                <p>support@tscogroup.com</p>
                <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-5 mx-auto mt-2 opacity-70">
            <?php else: ?>
                <p>Powered by <strong>QrServe</strong></p>
                <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png" alt="TSCO" class="h-4 mx-auto my-1 opacity-60">
                <p><strong>Technology Solutions Company (TSCO Group)</strong></p>
                <p>support@tscogroup.com | WhatsApp: +968 91914282</p>
            <?php endif; ?>
            <p>&copy; <?= date("Y") ?></p>
        </div>
    </div>
</body>
</html>