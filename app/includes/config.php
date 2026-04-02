<?php
if (basename($_SERVER['PHP_SELF']) == 'config.php') { exit('Direct access not permitted'); }
if (session_status() === PHP_SESSION_NONE) { session_start(); }

 $host = 'localhost'; $dbname = 'qrserve_db'; $user = 'qrserve_user'; $pass = 'QrServe_App_User_2024!';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) { die("QrServe DB Error: " . $e->getMessage()); }

// MAGIC: Nginx passes the Company ID via server block. If empty, it's the Super Admin Hub.
 $global_company_id = isset($_SERVER['APP_COMPANY_ID']) ? (int)$_SERVER['APP_COMPANY_ID'] : null;
?>
