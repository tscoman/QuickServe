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
        if ($is_sa_portal) {
            redirect("super-admin/dashboard.php");
        } else {
            session_destroy();
            header("Location: index.php?error=unauthorized_role");
            exit();
        }
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
            if (!empty($company["logo_url"])) { $logo_url = $company["logo_url"]; }
            $portal_name = htmlspecialchars($company["name"]);
            $portal_tagline = "Welcome! Please sign in.";
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
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-blue-600">
        <div class="flex justify-center mb-6">
            <img src="<?= $logo_url ?>" alt="Logo" class="h-16 object-contain" onerror="this.src='https://via.placeholder.com/150x50?text=Logo'">
        </div>
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2"><?= $portal_name ?></h2>
        <p class="text-center text-gray-500 mb-8 text-sm"><?= $portal_tagline ?></p>
        
        <?php if (isset($_GET["error"])): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center font-semibold">
            <?php
            $errs = ["login_required"=>"Session expired","invalid_credentials"=>"Invalid credentials","unauthorized_role"=>"Access denied"];
            echo $errs[$_GET["error"]] ?? "Error";
            ?>
        </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST" class="space-y-4">
            <input type="email" name="email" required placeholder="Email" class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-blue-500">
            <input type="password" name="password" required placeholder="Password" minlength="6" class="w-full px-4 py-2 rounded border focus:ring-2 focus:ring-blue-500 mt-3">
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 font-bold mt-4">Sign In</button>
        </form>

        <div class="mt-4 text-center"><a href="forgot_password.php" class="text-sm text-blue-600">Forgot Password?</a></div>

        <div class="mt-6 pt-4 border-t text-center text-xs text-gray-400">
            <?php if ($is_sa_portal): ?>
                <p>Super Admin Portal | TSCO Group</p>
                <p>support@tscogroup.com</p>
            <?php else: ?>
                <p>Powered by <strong>QrServe</strong></p>
                <p><strong>Technology Solutions Company (TSCO Group)</strong></p>
                <p>support@tscogroup.com | WhatsApp: +968 91914282</p>
                <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png" alt="TSCO" class="h-3 mx-auto mt-1 opacity-50">
            <?php endif; ?>
            <p class="mt-1">&copy; <?= date("Y") ?></p>
        </div>
    </div>
</body>
</html>
