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

// Fetch company data
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
            // Try multiple logo sources in order of priority
            $logo_sources = [];
            
            // 1. Database logo_url field
            if (!empty($company["logo_url"])) {
                $logo_sources[] = $company["logo_url"];
            }
            
            // 2. Check uploads directory for this company
            $upload_paths = [
                "/opt/QrServe/uploads/company_" . $global_company_id . "_logo.png",
                "/opt/QrServe/uploads/company_" . $global_company_id . "_logo.jpg",
                "/opt/QrServe/uploads/logo_" . $global_company_id . ".png",
                "/opt/QrServe/uploads/" . $global_company_id . ".png"
            ];
            
            foreach ($upload_paths as $path) {
                if (file_exists($path)) {
                    // Convert local path to URL
                    $logo_sources[] = "/uploads/" . basename($path);
                }
            }
            
            // Use first available logo, fallback to default
            foreach ($logo_sources as $src) {
                if (!empty($src)) {
                    $logo_url = $src;
                    break;
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
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-blue-600">
        <!-- Logo Section -->
        <div class="flex flex-col items-center mb-6">
            <?php if (!$is_sa_portal && $company): ?>
                <!-- Restaurant Portal: Show larger logo area -->
                <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center border-4 border-gray-200 overflow-hidden mb-2">
                    <img src="<?= $logo_url ?>" alt="<?= $portal_name ?>" 
                         class="max-w-full max-h-full object-contain p-2"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/128x128?text=' . urlencode($portal_name[0]) . ''">
                </div>
            <?php else: ?>
                <!-- SA Portal: Standard size -->
                <img src="<?= $logo_url ?>" alt="Logo" class="h-16 object-contain" 
                     onerror="this.onerror=null; this.src='https://via.placeholder.com/150x50?text=TSCO'">
            <?php endif; ?>
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
            <input type="email" name="email" required placeholder="Email Address" 
                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                   autocomplete="email">
            <input type="password" name="password" required placeholder="Password" minlength="6" 
                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent mt-3"
                   autocomplete="current-password">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition duration-200 mt-4 text-lg shadow-md hover:shadow-lg">
                Sign In
            </button>
        </form>

        <div class="mt-5 text-center">
            <a href="forgot_password.php" class="text-sm text-blue-600 hover:text-blue-800 hover:underline">Forgot Password?</a>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-5 border-t border-gray-200">
            <?php if ($is_sa_portal): ?>
                <!-- Super Admin Footer -->
                <div class="space-y-2 text-center">
                    <p class="text-xs font-semibold text-gray-600 uppercase tracking-wide">Super Admin Portal</p>
                    <p class="text-xs text-gray-500">Technology Solutions Company (TSCO Group)</p>
                    <p class="text-xs text-gray-400">support@tscogroup.com</p>
                    
                    <!-- TSCO Logo for SA portal -->
                    <div class="flex justify-center mt-3 pt-2">
                        <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO Group" class="h-6 opacity-70">
                    </div>
                </div>
            <?php else: ?>
                <!-- Restaurant Footer -->
                <div class="space-y-2 text-center">
                    <!-- Powered by QrServe -->
                    <p class="text-xs text-gray-400">Powered by <strong class="text-gray-600">QrServe</strong></p>
                    
                    <!-- TSCO Small Logo - ALWAYS VISIBLE -->
                    <div class="flex items-center justify-center gap-2 mt-1">
                        <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png" alt="TSCO" class="h-4 opacity-60">
                    </div>
                    
                    <!-- Company Name -->
                    <p class="text-xs font-semibold text-gray-500">Design & Development:</p>
                    <p class="text-sm font-bold text-gray-700">Technology Solutions Company (TSCO Group)</p>
                    
                    <!-- Contact Info -->
                    <div class="mt-2 pt-2 border-t border-dashed border-gray-300">
                        <p class="text-xs text-gray-500">
                            <span class="inline-block mx-1">📧</span> support@tscogroup.com
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            <span class="inline-block mx-1">💬</span> WhatsApp: <strong>+968 91914282</strong> | <strong>+968 72289890</strong>
                        </p>
                    </div>
                    
                    <!-- Copyright -->
                    <p class="text-xs text-gray-300 mt-3">&copy; <?= date("Y") ?> All Rights Reserved</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
