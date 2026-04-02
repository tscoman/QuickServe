<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/functions.php';
requireRole('company_admin');

 $company_id = getCompanyId();
 $stmt = $pdo->prepare("SELECT * FROM companies WHERE id = ?"); $stmt->execute([$company_id]); $company = $stmt->fetch();
 $stmt = $pdo->prepare("SELECT phone_number FROM mobile_payment_numbers WHERE company_id = ?"); $stmt->execute([$company_id]); $mobile_numbers = $stmt->fetchAll();
 $stmt = $pdo->prepare("SELECT id, name, email FROM users WHERE company_id = ? AND role = 'staff'"); $stmt->execute([$company_id]); $staff_list = $stmt->fetchAll();

 $access_url = $company['custom_domain'] ? "https://".$company['custom_domain'] : "http://prmx.omanwebhosting.com:".$company['port_number'];
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= sanitize($company['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse-glow {
            0% { box-shadow: 0 0 5px rgba(250, 204, 21, 0.2); }
            50% { box-shadow: 0 0 20px rgba(250, 204, 21, 0.8); }
            100% { box-shadow: 0 0 5px rgba(250, 204, 21, 0.2); }
        }
        .alert-glow { animation: pulse-glow 2s infinite; }
    </style>
</head>
<body class="h-full overflow-hidden flex flex-col text-gray-100">

    <header class="bg-gray-800 shadow-lg px-6 py-3 flex justify-between items-center z-10">
        <div class="flex items-center space-x-4">
            <?php if ($company['logo_url']): ?>
                <img src="<?= sanitize($company['logo_url']) ?>" class="h-10 rounded bg-white p-1">
            <?php else: ?>
                <div class="h-10 w-10 bg-blue-600 rounded flex items-center justify-center font-bold text-xl"><?= strtoupper(substr($company['name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div>
                <h1 class="text-xl font-bold tracking-wide"><?= sanitize($company['name']) ?></h1>
                <a href="<?= $access_url ?>" target="_blank" class="text-xs text-blue-400 hover:underline font-mono"><?= $access_url ?></a>
            </div>
        </div>
        <div class="flex items-center space-x-4">
            <a href="menu.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold text-sm tracking-wide">🍔 MENU</a>
            <a href="../../index.php?logout=1" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-bold text-sm tracking-wide">LOGOUT</a>
        </div>
    </header>

    <main class="flex-1 flex overflow-hidden">
        <aside class="w-80 bg-gray-800 border-r border-gray-700 overflow-y-auto p-6 space-y-8 hidden lg:block">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Business Details</h3>
                <div class="space-y-2 text-sm border-l-4 border-blue-500 pl-3">
                    <p><?= sanitize($company['street_address'] ?? 'Not set') ?></p>
                    <p class="text-gray-400">VAT: <?= sanitize($company['vat_number'] ?? 'N/A') ?> | CR: <?= sanitize($company['cr_number'] ?? 'N/A') ?></p>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Bank Accounts</h3>
                <?php foreach ($mobile_numbers as $mn): ?>
                    <div class="bg-gray-900 p-3 rounded-lg mb-2 font-mono text-sm flex justify-between items-center">
                        <span><?= sanitize($mn['phone_number']) ?></span>
                        <span class="text-green-400 text-xs">● Active</span>
                    </div>
                <?php endforeach; ?>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Add Staff</h3>
                <form action="add_staff.php" method="POST" class="space-y-2">
                    <input type="text" name="name" required placeholder="Name" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm">
                    <input type="email" name="email" required placeholder="Email" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm">
                    <input type="password" name="password" required placeholder="Password" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white p-2 rounded font-bold text-sm">ADD USER</button>
                </form>
                <div class="mt-4 space-y-2">
                    <?php foreach ($staff_list as $s): ?>
                        <div class="flex justify-between text-sm bg-gray-900 p-2 rounded border-l-4 border-gray-600">
                            <span><?= sanitize($s['name']) ?></span>
                            <a href="delete_staff.php?id=<?= $s['id'] ?>" class="text-red-500 hover:text-red-400 text-xs">X</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-auto pt-8 border-t border-gray-700 text-center opacity-50">
                <img src="<?= TSCO_LOGO_LIGHT ?>" class="h-6 mx-auto mb-1">
                <p class="text-xs text-gray-500">Powered by QrServe</p>
            </div>
        </aside>

        <section class="flex-1 bg-gray-950 p-8 overflow-y-auto">
            <div class="bg-yellow-500 text-black p-6 rounded-2xl mb-8 flex justify-between items-center alert-glow border-4 border-yellow-300">
                <div>
                    <h2 class="text-3xl font-black tracking-wider">⚠️ KITCHEN ALERTS</h2>
                    <p class="text-lg font-bold opacity-80 mt-1">Waiting for new orders...</p>
                </div>
                <div class="text-6xl font-black">0</div>
            </div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <div>
                    <h3 class="text-2xl font-bold mb-4 text-red-400 tracking-wide flex justify-between"><span>💳 PENDING PAYMENTS</span><span class="bg-red-600 text-white text-xl font-black px-4 py-1 rounded-full">0</span></h3>
                    <div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center"><p class="text-5xl mb-4">🧾</p><p class="text-xl font-bold text-gray-600">NO PENDING PAYMENTS</p></div>
                </div>
                <div>
                    <h3 class="text-2xl font-bold mb-4 text-green-400 tracking-wide flex justify-between"><span>👨‍🍳 ACTIVE ORDERS</span><span class="bg-green-600 text-white text-xl font-black px-4 py-1 rounded-full">0</span></h3>
                    <div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center"><p class="text-5xl mb-4">🍽️</p><p class="text-xl font-bold text-gray-600">KITCHEN IS READY</p></div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
