<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

 $user = getCurrentUser($pdo);
 $error = '';
 $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old_pass = $_POST['old_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    if (password_verify($old_pass, $user['password'])) {
        if ($new_pass === $confirm_pass && strlen($new_pass) >= 8) {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hash, $_SESSION['user_id']]);
            $success = "Password updated successfully.";
        } else {
            $error = "New passwords must match and be at least 8 characters.";
        }
    } else {
        $error = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - QrServe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 h-screen">
    <nav class="bg-white shadow-sm border-b px-6 py-3 flex justify-between items-center">
        <div class="flex items-center space-x-3">
            <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-8">
            <span class="font-bold text-gray-800 text-lg">Profile & Security</span>
        </div>
        <a href="dashboard.php" class="text-sm bg-gray-200 text-gray-800 px-3 py-1 rounded hover:bg-gray-300">Back to Dashboard</a>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-xl">
        <div class="bg-white rounded-lg shadow p-6 border">
            <h2 class="text-2xl font-bold mb-6 text-gray-800">Account Settings</h2>
            
            <?php if($error): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= $error ?></div><?php endif; ?>
            <?php if($success): ?><div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= $success ?></div><?php endif; ?>

            <div class="mb-6 p-4 bg-gray-50 rounded">
                <p class="text-sm text-gray-500">Logged in as</p>
                <p class="text-lg font-semibold"><?= sanitize($user['email']) ?></p>
                <p class="text-sm text-gray-500"><?= sanitize($user['name']) ?></p>
            </div>

            <form action="profile.php" method="POST">
                <h3 class="font-semibold mb-3 text-gray-700">Change Password</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Current Password</label>
                    <input type="password" name="old_password" required class="w-full border p-2 rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-600 mb-1">New Password (Min 8 chars)</label>
                    <input type="password" name="new_password" required class="w-full border p-2 rounded">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-600 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required class="w-full border p-2 rounded">
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-2 rounded hover:bg-blue-700 font-bold">Update Password</button>
            </form>
        </div>
    </div>
</body>
</html>
