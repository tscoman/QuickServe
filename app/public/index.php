<?php
require_once __DIR__ . "/../includes/config.php";

// Get company from port via APP_COMPANY_ID
$company_id = $_SERVER["APP_COMPANY_ID"] ?? null;
$company = null;
$is_sa = false;

if (!$company_id) {
    // Super Admin portal (8080)
    $is_sa = true;
    $company_name = "QrServe Super Admin";
    $company_logo = null;
    $company_theme = "midnight";
} else {
    // Company portal - fetch branding from database
    $stmt = $pdo->prepare("SELECT id, name, logo_url, theme FROM companies WHERE id = ? AND status = 'active'");
    $stmt->execute([$company_id]);
    $company = $stmt->fetch();
    
    if (!$company) {
        die("Restaurant not found or inactive");
    }
    
    $company_name = $company['name'];
    $company_logo = $company['logo_url'];
    $company_theme = $company['theme'] ?? 'midnight';
}

// Theme colors
$colors = [
    'midnight' => ['bg' => '#1a1a2e', 'text' => '#fff', 'primary' => '#0f3460', 'accent' => '#16c784'],
    'garden' => ['bg' => '#f0f7f0', 'text' => '#2d5016', 'primary' => '#52b788', 'accent' => '#74c69d'],
    'classic' => ['bg' => '#f5f5f5', 'text' => '#333', 'primary' => '#1f4788', 'accent' => '#4a90e2'],
    'rustic' => ['bg' => '#8b7355', 'text' => '#f5f5dc', 'primary' => '#d2691e', 'accent' => '#ff9800']
];
$c = $colors[$company_theme] ?? $colors['midnight'];

$error = $_GET['error'] ?? null;
$success = $_GET['success'] ?? null;
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($company_name); ?> - Login</title>
<script src="https://cdn.tailwindcss.com"></script>
<style>
:root { --bg: <?php echo $c['bg']; ?>; --text: <?php echo $c['text']; ?>; --primary: <?php echo $c['primary']; ?>; }
body { background: var(--bg); color: var(--text); }
.btn-primary { background: var(--primary); color: white; }
</style>
</head>
<body class="min-h-screen flex items-center justify-center">
<div class="max-w-md w-full bg-white/10 backdrop-blur-sm rounded-lg shadow-2xl p-8">
<?php if ($company_logo): ?>
<div class="text-center mb-6">
<img src="<?php echo htmlspecialchars($company_logo); ?>" alt="Logo" class="h-20 mx-auto rounded-lg">
</div>
<?php endif; ?>
<h1 class="text-3xl font-bold text-center mb-2"><?php echo htmlspecialchars($company_name); ?></h1>
<p class="text-center text-sm opacity-75 mb-6"><?php echo $is_sa ? 'Administration Portal' : 'Restaurant Login'; ?></p>

<?php if ($error): ?>
<div class="bg-red-500/20 text-red-200 p-3 rounded mb-4 text-sm">
<?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<form method="POST" action="login_process.php" class="space-y-4">
<div>
<label class="block text-sm font-bold mb-2">Email</label>
<input type="email" name="email" required class="w-full bg-white/10 border border-white/30 rounded px-4 py-2 text-white placeholder-white/50" placeholder="your@email.com">
</div>
<div>
<label class="block text-sm font-bold mb-2">Password</label>
<input type="password" name="password" required class="w-full bg-white/10 border border-white/30 rounded px-4 py-2 text-white placeholder-white/50" placeholder="••••••••">
</div>
<button type="submit" class="w-full btn-primary py-2 rounded font-bold">Login</button>
</form>

<div class="mt-4 text-center text-sm">
<a href="forgot_password.php" class="opacity-75 hover:opacity-100">Forgot password?</a>
</div>
</div>
</body>
</html>
