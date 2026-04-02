<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('super_admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']); $slug = sanitize(strtolower(str_replace(' ', '-', $_POST['slug'])));
    $phone = sanitize($_POST['phone']); $street_address = sanitize($_POST['street_address']);
    $vat_number = sanitize($_POST['vat_number']); $cr_number = sanitize($_POST['cr_number']);
    $website_url = sanitize($_POST['website_url']); $instagram_url = sanitize($_POST['instagram_url']);
    $whatsapp_number = sanitize($_POST['whatsapp_number']); $theme = sanitize($_POST['theme']);
    $admin_name = sanitize($_POST['admin_name']); $admin_email = sanitize($_POST['admin_email']);
    $admin_password = $_POST['admin_password']; $mobile_numbers = $_POST['mobile_numbers'] ?? [];
    
    $custom_domain = !empty($_POST['custom_domain']) ? sanitize(strtolower(trim($_POST['custom_domain']))) : null;

    $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = ?"); $stmt->execute([$slug]); if ($stmt->fetch()) { redirect('dashboard.php?error=slug_exists'); }
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?"); $stmt->execute([$admin_email]); if ($stmt->fetch()) { redirect('dashboard.php?error=email_exists'); }

    try {
        $pdo->beginTransaction();
        $sql = "INSERT INTO companies (name, slug, phone, street_address, vat_number, cr_number, website_url, instagram_url, whatsapp_number, theme, custom_domain) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $slug, $phone, $street_address, $vat_number, $cr_number, $website_url, $instagram_url, $whatsapp_number, $theme, $custom_domain]);
        $company_id = $pdo->lastInsertId();

        $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (company_id, name, email, password, role) VALUES (?, ?, ?, ?, 'company_admin')");
        $stmt->execute([$company_id, $admin_name, $admin_email, $hashed_password]);

        if (!empty($mobile_numbers)) {
            $stmtMob = $pdo->prepare("INSERT INTO mobile_payment_numbers (company_id, phone_number) VALUES (?, ?)");
            foreach ($mobile_numbers as $num) { $num = sanitize(trim($num)); if (!empty($num)) $stmtMob->execute([$company_id, $num]); }
        }
        $pdo->commit();

        // Determine routing: Domain uses port 80/443, IP uses custom ports
        $server_name = $custom_domain ? $custom_domain : "_";
        $listen_port = $custom_domain ? "80" : "8081"; // Fallback logic below if IP

        if (!$custom_domain) {
            $max_port_stmt = $pdo->query("SELECT MAX(port_number) AS max_port FROM companies WHERE port_number IS NOT NULL");
            $max_port = $max_port_stmt->fetch()['max_port'];
            $listen_port = ($max_port !== null) ? $max_port + 1 : 8081;
            $pdo->prepare("UPDATE companies SET port_number = ? WHERE id = ?")->execute([$listen_port, $company_id]);
            shell_exec("sudo ufw allow " . $listen_port . "/tcp");
        }

        // Build robust Nginx Conf
        $nginx_conf = "
server {
    listen {$listen_port};
    server_name {$server_name};
    root /opt/QrServe/app/public;
    index index.php index.html;
    
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    location ~ \.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param APP_COMPANY_ID {$company_id};
    }
}
";
        file_put_contents("/etc/nginx/sites-available/restaurant_{$slug}.conf", $nginx_conf);
        symlink("/etc/nginx/sites-available/restaurant_{$slug}.conf", "/etc/nginx/sites-enabled/restaurant_{$slug}.conf");
        shell_exec("sudo systemctl reload nginx.service");

        redirect('dashboard.php?success=1');

    } catch (Exception $e) { $pdo->rollBack(); redirect('dashboard.php?error=db_error'); }
} else { redirect('dashboard.php'); }
?>
