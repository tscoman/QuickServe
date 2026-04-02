<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
if(isset($_GET["logout"])){ session_destroy(); header("Location: index.php"); exit(); }
if (isLoggedIn()) {
    if ($_SESSION['role'] === 'super_admin') redirect('super-admin/dashboard.php');
    if ($_SESSION['role'] === 'company_admin') redirect('admin/dashboard.php');
    if ($_SESSION['role'] === 'staff') redirect('staff/orders.php');
}

// Fetch Company Details if we are on a restaurant port
 $company = null;
if ($global_company_id) {
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?");
    $stmt->execute([$global_company_id]);
    $company = $stmt->fetch();
}

 $is_sa_portal = (!$company) ? true : false;
 $top_logo = $is_sa_portal ? TSCO_LOGO_DARK : ($company['logo_url'] ?? 'https://via.placeholder.com/150x50?text=My+Logo');
 $title = $is_sa_portal ? 'Super Admin - QrServe' : $company['name'] . ' - QrServe';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex flex-col justify-center items-center">
    
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 <?= $is_sa_portal ? 'border-blue-600' : 'border-gray-800' ?> mb-6">
        <!-- Top Logo -->
        <div class="flex justify-center mb-6 h-16">
            <?php if ($is_sa_portal): ?>
                <img src="<?= $top_logo ?>" alt="TSCO Group" class="h-16 object-contain">
            <?php else: ?>
                <img src="<?= $top_logo ?>" alt="Restaurant Logo" class="h-16 object-contain rounded">
            <?php endif; ?>
        </div>
        
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
            <?= $is_sa_portal ? 'QrServe Admin' : sanitize($company['name']) ?>
        </h2>
        <p class="text-center text-gray-500 mb-8 text-sm"><?= $is_sa_portal ? 'System Hub' : 'Staff & Management Portal' ?></p>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center">
                <?php 
                    if($_GET['error'] == 'invalid_credentials') echo 'Invalid email or password.';
                    if($_GET['error'] == 'wrong_company') echo 'This account does not belong to this restaurant.';
                ?>
            </div>
        <?php endif; ?>

        <form action="login_process.php" method="POST">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Email Address</label>
                <input type="email" name="email" required class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-semibold mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 rounded border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded transition duration-200">Sign In</button>
        </form>
    </div>

    <!-- Footer -->
    <div class="text-center max-w-md">
        <?php if (!$is_sa_portal): ?>
            <p class="text-xs text-gray-500 mb-2 leading-5">
                Powered by <b>QrServe</b>. Design and developed by <b>Technology Solutions Company</b><br>
                email: support@tscogroup.com | Technical team: +968 91914282 +968 72289890
            </p>
        <?php endif; ?>
        <img src="<?= TSCO_LOGO_DARK ?>" alt="TSCO" class="h-5 mx-auto opacity-50">
    </div>

</body>
</html>
