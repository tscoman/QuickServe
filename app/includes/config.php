<?php
// Prevent direct access to includes
if (basename($_SERVER['PHP_SELF']) == 'config.php') {
    exit('Direct access not permitted');
}

session_start();

 $host = 'localhost';
 $dbname = 'qrserve_db';
 $user = 'qrserve_user';
 $pass = 'QrServe_App_User_2024!';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    // Set PDO error mode to exception for secure error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Fetch associative arrays by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("QrServe Database Connection Failed: " . $e->getMessage());
}
?>
