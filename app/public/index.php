<?php
/**
 * QrServe Multi-Tenant Login Portal
 * Detects which portal (SA or Restaurant) and shows appropriate login
 */

// Load core systems
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Handle logout
if(isset($_GET["logout"])){ 
    session_destroy(); 
    header("Location: index.php"); 
    exit(); 
}

// If already logged in, redirect appropriately
if (isLoggedIn()) {
    $role = $_SESSION['role'] ?? '';
    
    // Super Admin always goes to SA dashboard
    if ($role === 'super_admin') {
        redirect('super-admin/dashboard.php');
    }
    
    // Company Admin/Staff go to their dashboards
    if ($role === 'company_admin') {
        redirect('admin/dashboard.php');
    }
    if ($role === 'staff') {
        redirect('staff/orders.php');
    }
    
    // Fallback for any other role
    redirect('index.php?error=unauthorized_role');
}

// ============================================
// DETERMINE WHICH PORTAL WE'RE ON
// ============================================
 $is_super_admin_portal = empty($global_company_id);
 $company = null;
 $logo_url = 'https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png';
 $portal_name = 'QrServe Admin';
 $portal_tagline = 'A revolutionary app for restaurants & cafes';

// If we have a company ID, fetch its details
if (!$is_super_admin_portal && $global_company_id) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ? AND is_active = 1");
        $stmt->execute([$global_company_id]);
        $company = $stmt->fetch();
        
        if ($company) {
            // Use restaurant's logo if available
            if (!empty($company['logo_url'])) {
                $logo_url = $company['logo_url'];
            }
            $portal_name = htmlspecialchars($company['name']);
            $portal_tagline = 'Welcome! Please sign in to manage your restaurant.';
        }
    } catch (Exception $e) {
        // If DB fails, show generic login
        error_log("QrServe: Failed to fetch company info - " . $e->getMessage());
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
    <style>
        /* Dynamic theming based on company */
        <?php if ($company && !empty($company['theme'])): ?>
            <?php if ($company['theme'] == 'midnight'): ?>
                body { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); }
                .login-box { border-color: #f59e0b !important; }
                .btn-primary { background: #f59e0b; }
                .btn-primary:hover { background: #d97706; }
            <?php elseif ($company['theme'] == 'garden'): ?>
                body { background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); }
                .login-box { border-color: #10b981 !important; }
                .btn-primary { background: #10b981; }
                .btn-primary:hover { background: #059669; }
            <?php elseif ($company['theme'] == 'classic'): ?>
                body { background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); }
                .login-box { border-color: #dc2626 !important; }
                .btn-primary { background: #dc2626; }
                .btn-primary:hover { background: #b91c1c; }
            <?php elseif ($company['theme'] == 'rustic'): ?>
                body { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%); }
                .login-box { border-color: #d97706 !important; }
                .btn-primary { background: #d97706; }
                .btn-primary:hover { background: #b45309; }
            <?php endif; ?>
        <?php endif; ?>
        
        .login-box {
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,0.95);
        }
    </style>
</head>
<body class="min-h-screen flex justify-center items-center p-4">
    
    <div class="login-box bg-white p-8 rounded-xl shadow-2xl w-full max-w-md border-t-8">
        
        <!-- Logo Section -->
        <div class="flex flex-col items-center mb-6">
            <img src="<?= $logo_url ?>" alt="Logo" class="h-20 object-contain mb-3"
                 onerror="this.src='https://via.placeholder.com/200x80?text=Logo'">
            
            <h1 class="text-2xl font-bold text-gray-800"><?= $portal_name ?></h1>
            <p class="text-sm text-gray-500 mt-1"><?= $portal_tagline ?></p>
        </div>

        <!-- Error Messages -->
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
                <?php
                $errors = [
                    'login_required' => '⚠️ Session expired. Please log in again.',
                    'invalid_credentials' => '❌ Invalid email or password.',
                    'unauthorized_role' => '🔒 You do not have permission.',
                    'session_expired' => '⏰ Your session has expired.'
                ];
                echo $errors[$_GET['error']] ?? 'An error occurred.';
                ?>
            </div>
        <?php endif; ?>

        <!-- Success Messages -->
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 text-green-700 p-3 rounded-lg mb-4 text-sm font-semibold text-center">
                ✅ <?= $_GET['success'] == 'password_reset' ? 'Password reset successfully!' : 'Success!' ?>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form action="login_process.php" method="POST" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" required autofocus
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                       placeholder="your@email.com" autocomplete="email">
            </div>
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Password</label>
                <input type="password" name="password" required minlength="6"
                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                       placeholder="••••••••" autocomplete="current-password">
            </div>

            <button type="submit" 
                    class="btn-primary w-full text-white font-bold py-3 rounded-lg transition duration-200 text-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                🔓 Sign In
            </button>
        </form>

        <!-- Forgot Password -->
        <div class="mt-5 text-center">
            <a href="forgot_password.php" class="text-sm text-blue-600 hover:underline hover:text-blue-800">
                🔑 Forgot Password?
            </a>
        </div>

        <!-- Footer -->
        <div class="mt-6 pt-4 border-t border-gray-200">
            <?php if ($is_super_admin_portal): ?>
                <!-- Super Admin Footer -->
                <p class="text-xs text-center text-gray-400">
                    👤 <strong>Super Admin Portal</strong><br>
                    Technology Solutions Company (TSCO Group)
                </p>
            <?php else: ?>
                <!-- Restaurant Footer -->
                <p class="text-xs text-center text-gray-400">
                    Powered by <strong>QrServe</strong><br>
                    Design & Development:<br>
                    <strong>Technology Solutions Company (TSCO Group)</strong><br>
                    📧 support@tscogroup.com<br>
                    💬 WhatsApp: +968 91914282 | +968 72289890
                </p>
                
                <!-- Small TSCO Logo -->
                <div class="flex justify-center mt-2">
                    <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png" alt="TSCO" class="h-4 opacity-50">
                </div>
            <?php endif; ?>
            
            <p class="text-xs text-center text-gray-300 mt-2">
                &copy; <?= date('Y') ?> All Rights Reserved
            </p>
        </div>
    </div>

    <!-- Debug Info (remove in production) -->
    <?php if (isset($_GET['debug'])): ?>
        <pre class="fixed bottom-0 left-0 bg-black text-green-400 p-4 text-xs m-2 rounded opacity-75">
Company ID: <?= $global_company_id ?? 'NULL' ?>
Is SA Portal: <?= $is_super_admin_portal ? 'YES' : 'NO' ?>
Company Data: <?= print_r($company, true) ?>
        </pre>
    <?php endif; ?>

</body>
</html>
