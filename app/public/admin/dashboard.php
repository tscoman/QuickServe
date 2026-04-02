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

 $time_filter = $_GET['time'] ?? 'last_2_hours';
 $minutes = match($time_filter) {
    'last_1_hour' => 60, 'last_2_hours' => 120, 'last_6_hours' => 360,
    'today' => (date('H') * 60), 'yesterday' => 1440, default => 120
};
 $start_time = date('Y-m-d H:i:s', strtotime("-{$minutes} minutes"));

 $stmt = $pdo->prepare("SELECT COALESCE(SUM(total_amount), 0) as total_sales, COUNT(id) as total_orders, COALESCE(SUM(CASE WHEN payment_method IN ('cash', 'offline_card', 'mobile_upload') THEN 1 ELSE 0 END), 0) as offline_orders FROM orders WHERE company_id = ? AND status IN ('paid', 'preparing', 'completed') AND created_at >= ?");
 $stmt->execute([$company_id, $start_time]);
 $sales_data = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?= sanitize($company['name']) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>@keyframes pulse-glow { 0% { box-shadow: 0 0 5px rgba(250, 204, 21, 0.2); } 50% { box-shadow: 0 0 20px rgba(250, 204, 21, 0.8); } 100% { box-shadow: 0 0 5px rgba(250, 204, 21, 0.2); } } .alert-glow { animation: pulse-glow 2s infinite; }</style>
<style>

/* Notification Star */

.notif-star { position: fixed; top: 20px; right: 20px; z-index: 9999; cursor: pointer; }

.notif-star svg { width: 40px; height: 40px; fill: #ffffff; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); transition: transform 0.2s; }

.notif-star:hover svg { transform: scale(1.2); }

.notif-badge { position: absolute; top: -5px; right: -5px; background: #ef4444; color: white; font-size: 12px; font-weight: 900; width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 2px solid #1f2937; box-shadow: 0 0 10px rgba(239, 68, 68, 0.8); }

.notif-dropdown { display: none; position: fixed; top: 70px; right: 20px; background: #1f2937; border: 1px solid #374151; border-radius: 12px; padding: 20px; width: 300px; z-index: 9999; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); }

.notif-dropdown.active { display: block; }

.notif-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid #374151; }

.notif-row:last-child { border-bottom: none; }

.notif-count { font-size: 24px; font-weight: 900; }

</style>

</head>
<body class="h-full overflow-hidden flex flex-col text-gray-100">
    <header class="bg-gray-800 shadow-lg px-6 py-3 flex justify-between items-center z-10">
        <div class="flex items-center space-x-4">
            <?php if ($company['logo_url']): ?><img src="<?= sanitize($company['logo_url']) ?>" class="h-10 rounded bg-white p-1"><?php else: ?><div class="h-10 w-10 bg-blue-600 rounded flex items-center justify-center font-bold text-xl"><?= strtoupper(substr($company['name'], 0, 1)) ?></div><?php endif; ?>
            <div><h1 class="text-xl font-bold tracking-wide"><?= sanitize($company['name']) ?></h1><a href="<?= $access_url ?>" target="_blank" class="text-xs text-blue-400 hover:underline font-mono"><?= $access_url ?></a></div>
        </div>
        <div class="flex items-center space-x-4">
            <a href="menu.php" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded font-bold text-sm tracking-wide">MENU</a>
            <a href="../../index.php?logout=1" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded font-bold text-sm tracking-wide">LOGOUT</a>
        </div>
    </header>
    <main class="flex-1 flex overflow-hidden">
        <aside class="w-80 bg-gray-800 border-r border-gray-700 overflow-y-auto p-6 space-y-8 hidden lg:block">
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Business Details</h3>
                <div class="space-y-2 text-sm border-l-4 border-blue-500 pl-3"><p><?= sanitize($company['street_address'] ?? 'Not set') ?></p><p class="text-gray-400">VAT: <?= sanitize($company['vat_number'] ?? 'N/A') ?> | CR: <?= sanitize($company['cr_number'] ?? 'N/A') ?></p></div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Bank Accounts</h3>
                <?php foreach ($mobile_numbers as $mn): ?><div class="bg-gray-900 p-3 rounded-lg mb-2 font-mono text-sm flex justify-between items-center"><span><?= sanitize($mn['phone_number']) ?></span><span class="text-green-400 text-xs">Active</span></div><?php endforeach; ?>
            </div>
            <div>
                <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">Add Staff</h3>
                <form action="add_staff.php" method="POST" class="space-y-2"><input type="text" name="name" required placeholder="Name" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm"><input type="email" name="email" required placeholder="Email" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm"><input type="password" name="password" required placeholder="Password" class="w-full bg-gray-900 border border-gray-700 p-2 rounded text-sm"><button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white p-2 rounded font-bold text-sm">ADD USER</button></form>
                <div class="mt-4 space-y-2"><?php foreach ($staff_list as $s): ?><div class="flex justify-between text-sm bg-gray-900 p-2 rounded border-l-4 border-gray-600"><span><?= sanitize($s['name']) ?></span><a href="delete_staff.php?id=<?= $s['id'] ?>" class="text-red-500 hover:text-red-400 text-xs">X</a></div><?php endforeach; ?></div>
            </div>
            
<!-- PRINTERS & RECEIPT SETTINGS -->
<div>
    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">🖨️ Printers & Receipt Setup</h3>
    <div class="bg-gray-900 p-3 rounded-lg mb-4">
        <form action="add_printer.php" method="POST" class="space-y-2">
            <input type="text" name="printer_name" placeholder="Printer Name (e.g., Main Grill)" required class="w-full bg-gray-800 border border-gray-600 p-1.5 rounded text-xs">
            <select name="printer_type" class="w-full bg-gray-800 border border-gray-600 p-1.5 rounded text-xs text-gray-400">
                <option value="kitchen">Kitchen (Food Prep)</option>
                <option value="cashier">Cashier (Receipt Printer)</option>
                <option value="bar">Bar (Beverages)</option>
            </select>
            <input type="text" name="identifier" placeholder="IP Address (192.168.1.50) or OS Name" class="w-full bg-gray-800 border border-gray-600 p-1.5 rounded text-xs">
            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white p-1.5 rounded font-bold text-xs">+ Add Printer</button>
        </form>
        <div class="space-y-2 mt-4 max-h-24 overflow-y-auto pr-1">
            <?php 
            $prn_stmt = $pdo->prepare("SELECT * FROM printers WHERE company_id = ? ORDER BY id DESC"); 
            $prn_stmt->execute([$company_id]); 
            $all_printers = $prn_stmt->fetchAll(); 
            foreach ($all_printers as $prn): ?>
                <div class="flex justify-between items-center text-xs bg-gray-800 p-1.5 rounded border-l-2 border-blue-500">
                    <div>
                        <span class="font-bold text-white"><?= sanitize($prn['printer_name']) ?></span>
                        <span class="text-gray-500 block">Type: <?= $prn['printer_type'] ?> | ID: <?= sanitize($prn['identifier']) ?></span>
                    </div>
                    <a href="delete_printer.php?id=<?= $prn['id'] ?>" class="text-red-500 hover:text-red-400 text-xs font-bold">X</a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div>
    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-widest mb-4">🧾 Receipt Customization</h3>
    <form action="save_receipt_settings.php" method="POST" class="bg-gray-900 p-3 rounded-lg space-y-2 text-xs">
        <p class="text-gray-400 mb-2">Adjust what prints on customer receipts.</p>
        <label class="flex items-center gap-2 text-gray-300">
            <input type="hidden" name="save_receipt" value="1">
            <input type="checkbox" name="receipt_show_vat" value="1" <?= $company['receipt_show_vat'] ? 'checked' : '' ?> class="w-4 h-4 rounded">
            <span>Display VAT % on receipt</span>
        </label>
        <label class="flex items-center gap-2 text-gray-300">
            <input type="checkbox" name="receipt_show_cr" value="1" <?= $company['receipt_show_cr'] ? 'checked' : '' ?> class="w-4 h-4 rounded">
            <span>Display CR Number on receipt</span>
        </label>
        <label class="flex items-center gap-2 text-gray-300">
            <input type="checkbox" name="receipt_show_tax_breakdown" value="1" <?= $company['receipt_show_tax_breakdown'] ? 'checked' : '' ?> class="w-4 h-4 rounded">
            <span>Show individual tax breakdown</span>
        </label>
        <select name="receipt_logo_position" class="w-full bg-gray-800 border border-gray-600 p-1.5 rounded text-gray-400">
            <option value="top" <?= $company['receipt_logo_position'] == 'top' ? 'selected' : '' ?>>Logo at Top (Standard)</option>
            <option value="center" <?= $company['receipt_logo_position'] == 'center' ? 'selected' : '' ?>>Logo Centered (Fancy)</option>
            <option value="none" <?= $company['receipt_logo_position'] == 'none' ? 'selected' : '' ?>>No Logo (Text Only)</option>
        </select>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-500 text-white p-1.5 rounded font-bold text-xs">Save Settings</button>
    </form>
</div>
<div class="mt-auto pt-8 border-t border-gray-700 text-center opacity-50"><img src="<?= TSCO_LOGO_LIGHT ?>" class="h-6 mx-auto mb-1"><p class="text-xs text-gray-500">Powered by QrServe</p></div>
        </aside>
        <section class="flex-1 bg-gray-950 p-8 overflow-y-auto">
            <div class="bg-gray-800 rounded-2xl border border-gray-700 p-6 mb-8 shadow-lg">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-black text-white tracking-wide">LIVE SALES TRACKER</h2>
                    <select id="timeFilter" onchange="loadSales()" class="bg-gray-900 border border-gray-600 text-white px-4 py-2 rounded-lg font-bold"><option value="last_1_hour" <?= $time_filter=='last_1_hour'?'selected':''?>>Last 1 Hour</option><option value="last_2_hours" <?= $time_filter=='last_2_hours'?'selected':''?>>Last 2 Hours</option><option value="last_6_hours" <?= $time_filter=='last_6_hours'?'selected':''?>>Last 6 Hours</option><option value="today" <?= $time_filter=='today'?'selected':''?>>Today</option><option value="yesterday" <?= $time_filter=='yesterday'?'selected':''?>>Yesterday</option></select>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-green-900/30 border-2 border-green-500 rounded-xl p-6 text-center"><p class="text-sm font-bold text-green-400 uppercase tracking-widest mb-2">Total Revenue</p><p id="totalSales" class="text-5xl font-black text-green-400"><?= number_format($sales_data['total_sales'], 3) ?></p><p class="text-xs text-green-600 mt-2">OMR</p></div>
                    <div class="bg-blue-900/30 border-2 border-blue-500 rounded-xl p-6 text-center"><p class="text-sm font-bold text-blue-400 uppercase tracking-widest mb-2">Total Orders</p><p id="totalOrders" class="text-5xl font-black text-blue-400"><?= $sales_data['total_orders'] ?></p><p class="text-xs text-blue-600 mt-2">Completed</p></div>
                    <div class="bg-orange-900/30 border-2 border-orange-500 rounded-xl p-6 text-center"><p class="text-sm font-bold text-orange-400 uppercase tracking-widest mb-2">Cash/Card Swipes</p><p id="offlineOrders" class="text-5xl font-black text-orange-400"><?= $sales_data['offline_orders'] ?></p><p class="text-xs text-orange-600 mt-2">Awaiting Proof</p></div>
                </div>
            </div>
            <div class="bg-yellow-500 text-black p-6 rounded-2xl mb-8 flex justify-between items-center alert-glow border-4 border-yellow-300"><div><h2 class="text-3xl font-black tracking-wider">KITCHEN ALERTS</h2><p class="text-lg font-bold opacity-80 mt-1">Waiting for new orders...</p></div><div class="text-6xl font-black">0</div></div>
            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
                <div><h3 class="text-2xl font-bold mb-4 text-red-400 tracking-wide flex justify-between"><span>PENDING PAYMENTS</span><span class="bg-red-600 text-white text-xl font-black px-4 py-1 rounded-full">0</span></h3><div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center"><p class="text-5xl mb-4">NO PENDING PAYMENTS</p></div></div>
                <div><h3 class="text-2xl font-bold mb-4 text-green-400 tracking-wide flex justify-between"><span>ACTIVE ORDERS</span><span class="bg-green-600 text-white text-xl font-black px-4 py-1 rounded-full">0</span></h3><div class="border-4 border-dashed border-gray-800 rounded-2xl p-12 text-center"><p class="text-5xl mb-4">KITCHEN IS READY</p></div></div>
            </div>
        </section>
    </main>
    <script>
    function loadSales() {
        const time = document.getElementById('timeFilter').value;
        fetch(`dashboard.php?time=${time}`).then(r => r.text()).then(html => {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            document.getElementById('totalSales').innerText = doc.getElementById('totalSales').innerText;
            document.getElementById('totalOrders').innerText = doc.getElementById('totalOrders').innerText;
            document.getElementById('offlineOrders').innerText = doc.getElementById('offlineOrders').innerText;
        });
    }
    </script>
<!-- Notification Star -->

<div class="notif-star" onclick="toggleNotifDropdown()">

    <svg viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>

    <div class="notif-badge" id="notifBadge" style="display:none;">0</div>

</div>



<!-- Dropdown Box -->

<div class="notif-dropdown" id="notifDropdown">

    <h3 class="text-white font-bold mb-4 text-lg">Live Order Alerts</h3>

    <div class="notif-row">

        <span class="text-red-400">💳 Pending Payments</span>

        <span class="notif-count text-red-400" id="dropPending">0</span>

    </div>

    <div class="notif-row">

        <span class="text-yellow-400">👨‍🍳 In Kitchen</span>

        <span class="notif-count text-yellow-400" id="dropPreparing">0</span>

    </div>

    <div class="notif-row">

        <span class="text-green-400">✅ Recent Paid</span>

        <span class="notif-count text-green-400" id="dropRecent">0</span>

    </div>

</div>



<script>

let audioCtx;

function playBeep() {

    if(!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();

    let oscar = audioCtx.createOscillator();

    let gain = audioCtx.createGain();

    oscar.connect(gain);

    gain.connect(audioCtx.destination);

    oscar.frequency.value = 800;

    gain.gain.value = 0.3;

    oscar.start();

    setTimeout(() => { oscar.stop(); }, 200);

}



function toggleNotifDropdown() {

    document.getElementById("notifDropdown").classList.toggle("active");

}



let prevAlerts = 0;

function checkCounts() {

    fetch("api/get_counts.php")

        .then(r => r.json())

        .then(data => {

            document.getElementById("notifBadge").innerText = data.total_alerts;

            document.getElementById("dropPending").innerText = data.pending_payments;

            document.getElementById("dropPreparing").innerText = data.preparing;

            document.getElementById("dropRecent").innerText = data.recent_paid;

            

            if (data.total_alerts > 0) {

                document.getElementById("notifBadge").style.display = "flex";

                if (data.total_alerts > prevAlerts) {

                    playBeep(); // Play sound if new orders come in

                }

            } else {

                document.getElementById("notifBadge").style.display = "none";

            }

            prevAlerts = data.total_alerts;

        });

}



// Poll every 10 seconds

setInterval(checkCounts, 10000);

checkCounts();

</script>


</body>
</html>