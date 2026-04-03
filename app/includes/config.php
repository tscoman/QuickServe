<?php
// QrServe Core Configuration
$filename = basename($_SERVER["PHP_SELF"] ?? "");
if ($filename == "config.php") { exit("Direct access not permitted"); }

// Load .env file
$env_file = __DIR__ . "/../../.env";
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
} else {
    // Fallback to direct configuration if .env doesn't exist
    $env = [
        'DB_HOST' => 'localhost',
        'DB_NAME' => 'qrserve_db',
        'DB_USER' => 'qrserve_user',
        'DB_PASS' => 'QrServe_App_User_2024!'
    ];
}

// Get portal type from Nginx
$portal_type = $_SERVER["APP_COMPANY_ID"] ?? null;
$is_sa_portal = empty($portal_type);

// Session isolation per portal
if ($is_sa_portal) {
    session_name("QRSERVE_SA");
} else {
    session_name("QRSERVE_C" . $portal_type);
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set("session.use_strict_mode", 1);
    session_set_cookie_params([
        "lifetime" => 0,
        "path" => "/",
        "domain" => "",
        "secure" => false,
        "httponly" => true,
        "samesite" => "Lax"
    ]);
    session_start();
}

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . $env['DB_HOST'] . ";dbname=" . $env['DB_NAME'] . ";charset=utf8mb4",
        $env['DB_USER'],
        $env['DB_PASS'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("QrServe Database Error: " . $e->getMessage());
}

$global_company_id = $portal_type ? (int)$portal_type : null;
?>
