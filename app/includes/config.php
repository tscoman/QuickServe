<?php
/**
 * QrServe Core Configuration
 * Multi-Tenant SaaS Architecture
 */

// Prevent direct access
if (basename($_SERVER['PHP_SELF'] ?? '')) == 'config.php') {
    exit('Direct access not permitted');
}

// Determine portal type from Nginx variable
 $portal_type = $_SERVER['APP_COMPANY_ID'] ?? null;
 $is_sa_portal = empty($portal_type);

// Session configuration with isolation per portal
if ($is_sa_portal) {
    session_name('QRSERVE_SA');
} else {
    session_name('QRSERVE_C' . $portal_type);
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.use_strict_mode', 1);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Database connection
 $host = 'localhost';
 $dbname = 'qrserve_db';
 $user = 'qrserve_user';
 $pass = 'QrServe_App_User_2024!';

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
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
