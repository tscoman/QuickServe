<?php
require_once 'config.php';

// Safe redirect
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Sanitize output (Prevents XSS)
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Format currency
function formatMoney($amount) {
    return '$' . number_format((float)$amount, 2);
}

// Generate simple random string (for QR tokens, etc.)
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// TSCO Branding Constants
define('TSCO_LOGO_DARK', 'https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png');
define('TSCO_LOGO_LIGHT', 'https://tscocdn.sirv.com/TSCO-LOGO-EN-LIGHT.png');
define('TSCO_SUPPORT_EMAIL', 'support@tscogroup.com');
define('TSCO_SUPPORT_WHATSAPP', '+968 91914282');

// Pre-load Theme CSS classes based on DB selection
function getThemeClasses($themeName) {
    switch ($themeName) {
        case 'midnight':
            return ['bg' => 'bg-gray-900', 'text' => 'text-white', 'btn' => 'bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold', 'card' => 'bg-gray-800 border-gray-700'];
        case 'garden':
            return ['bg' => 'bg-gray-50', 'text' => 'text-gray-800', 'btn' => 'bg-emerald-600 hover:bg-emerald-500 text-white font-bold', 'card' => 'bg-white border-emerald-100'];
        case 'classic':
            return ['bg' => 'bg-white', 'text' => 'text-gray-900', 'btn' => 'bg-red-600 hover:bg-red-500 text-white font-bold', 'card' => 'bg-gray-50 border-red-100'];
        case 'rustic':
            return ['bg' => 'bg-amber-50', 'text' => 'text-amber-900', 'btn' => 'bg-amber-800 hover:bg-amber-700 text-white font-bold', 'card' => 'bg-white border-amber-200'];
        default:
            return ['bg' => 'bg-gray-900', 'text' => 'text-white', 'btn' => 'bg-yellow-500 hover:bg-yellow-400 text-gray-900 font-bold', 'card' => 'bg-gray-800 border-gray-700'];
    }
}
?>
