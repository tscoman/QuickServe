<?php
/**
 * QrServe Unified Login Processor
 * Handles authentication for Super Admin AND Restaurant portals
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

 $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
 $password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    redirect('index.php?error=invalid_credentials');
}

try {
    // Find user by email
    $stmt = $pdo->prepare("SELECT u.*, c.name as company_name, c.logo_url 
                           FROM users u 
                           LEFT JOIN companies c ON u.company_id = c.id 
                           WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Log failed attempt
        error_log("QrServe: Failed login attempt for $email from {$_SERVER['REMOTE_ADDR']}");
        redirect('index.php?error=invalid_credentials');
    }

    // Check if user account is active (add is_active column later if needed)
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['company_id'] = $user['company_id'];
    
    // Regenerate session ID to prevent fixation
    session_regenerate_id(true);

    // Redirect based on role
    switch ($user['role']) {
        case 'super_admin':
            // SA should ONLY log in on main portal (port 8080)
            if (!empty($global_company_id)) {
                // Trying to log into restaurant portal as SA - redirect to SA portal
                redirect('index.php?error=unauthorized_role');
            }
            redirect('super-admin/dashboard.php');
            break;
            
        case 'company_admin':
            // Verify they belong to this restaurant (if on restaurant portal)
            if (!empty($global_company_id) && $user['company_id'] != $global_company_id) {
                // Wrong restaurant!
                redirect('index.php?error=unauthorized_role');
            }
            redirect('admin/dashboard.php');
            break;
            
        case 'staff':
            if (!empty($global_company_id) && $user['company_id'] != $global_company_id) {
                redirect('index.php?error=unauthorized_role');
            }
            redirect('staff/orders.php');
            break;
            
        default:
            redirect('index.php?error=unauthorized_role');
    }

} catch (Exception $e) {
    error_log("QrServe Login Error: " . $e->getMessage());
    redirect('index.php?error=login_required');
}
?>
