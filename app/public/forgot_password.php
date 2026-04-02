<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

 $company = null;
if ($global_company_id) {
    $stmt = $pdo->prepare("SELECT name, logo_url FROM companies WHERE id = ?");
    $stmt->execute([$global_company_id]);
    $company = $stmt->fetch();
}
 $top_logo = $company ? ($company['logo_url'] ?? TSCO_LOGO_DARK) : TSCO_LOGO_DARK;

if (isset($_GET['token'])) {
    $stmt = $pdo->prepare("SELECT id, email FROM users WHERE reset_token = ? AND reset_expires > ?");
    $stmt->execute([$_GET['token'], time()]);
    $user = $stmt->fetch();
    if (!$user) { die("<div style='text-align:center;margin-top:100px;font-family:sans-serif'><h2 style='color:red'>Invalid or Expired Link</h2></div>"); }
    
    $success = null; $error = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $new_pass = $_POST['password'];
        if (strlen($new_pass) < 8) { $error = "Password must be at least 8 characters."; } 
        else {
            $hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?")->execute([$hash, $user['id']]);
            $success = "Password updated! <a href='index.php'>Login here</a>.";
        }
    }
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Reset Password</title><script src="https://cdn.tailwindcss.com"></script></head>
    <body class="bg-gray-100 h-screen flex flex-col justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-blue-600">
            <div class="flex justify-center mb-6"><img src="<?= $top_logo ?>" class="h-16 object-contain rounded"></div>
            <h2 class="text-xl font-bold text-center mb-6">Set New Password for <?= sanitize($user['email']) ?></h2>
            <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center'>$error</div>"; ?>
            <?php if($success) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4 text-sm text-center'>$success</div>"; ?>
            <form action="forgot_password.php?token=<?= $_GET['token'] ?>" method="POST">
                <input type="password" name="password" placeholder="New Password (Min 8 chars)" required minlength="8" class="w-full px-4 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 mb-4">
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Reset Password</button>
            </form>
        </div>
    </body></html>
    <?php
} else {
    $error = null; $success = null;
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = sanitize(trim($_POST['email']));
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?"); $stmt->execute([$email]); $user = $stmt->fetch();
        if ($user) {
            $token = bin2hex(random_bytes(32)); $expires = time() + 3600;
            $pdo->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?")->execute([$token, $expires, $user['id']]);
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
            $reset_link = "{$protocol}://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}?token={$token}";
            $success = "If an account exists, a reset link has been generated. (Link: $reset_link)";
        } else { $error = "No account found with that email."; }
    }
    ?>
    <!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Forgot Password</title><script src="https://cdn.tailwindcss.com"></script></head>
    <body class="bg-gray-100 h-screen flex flex-col justify-center items-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md border-t-4 border-blue-600">
            <div class="flex justify-center mb-6"><img src="<?= $top_logo ?>" class="h-16 object-contain rounded"></div>
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">Reset Password</h2>
            <p class="text-center text-gray-500 mb-8 text-sm">Enter your admin email address</p>
            <?php if($error) echo "<div class='bg-red-100 text-red-700 p-3 rounded mb-4 text-sm text-center'>$error</div>"; ?>
            <?php if($success) echo "<div class='bg-green-100 text-green-700 p-3 rounded mb-4 text-sm text-center'>$success</div>"; ?>
            <form action="forgot_password.php" method="POST">
                <input type="email" name="email" placeholder="Email Address" required class="w-full px-4 py-2 rounded border border-gray-300 focus:ring-2 focus:ring-blue-500 mb-6">
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded hover:bg-blue-700">Send Reset Link</button>
            </form>
            <p class="text-center mt-6"><a href="index.php" class="text-sm text-blue-600 hover:underline">Back to Login</a></p>
        </div>
    </body></html>
    <?php
}
?>
