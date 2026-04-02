<?php
/**
 * QrServe Enterprise Restore Center
 * 
 * One-click restore for Super Admin
 * Restores company database from any backup point
 * 
 * @version 5.0.0
 */

require_once __DIR__ . "/../../includes/config.php";
require_once __DIR__ . "/../../includes/auth.php";
require_once __DIR__ . "/../../includes/functions.php";
require_once __DIR__ . "/../../includes/core/DbManager.php";

requireRole("super_admin");

 $company_id = $_GET["id"] ?? null;

if (!$company_id) {
    redirect("dashboard.php?error=no_company_id");
    exit;
}

// Get company info
 $stmt = QrServeDbManager::getSystem()->prepare("SELECT * FROM companies WHERE id = ?");
 $stmt->execute([$company_id]);
 $company = $stmt->fetch();

if (!$company_id) {
    redirect("dashboard.php?error=company_not_found");
    exit;
}

// Handle restore request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $backup_file = $_POST["backup_file"] ?? "";
    
    if (!empty($backup_file) && file_exists($backup_file)) {
        
        // Extract company ID from filename
        preg_match('/company_(\d+)/backup_(\d{14})/', basename($backup_file), $matches);
        $backup_company_id = $matches[1];
        
        if ($backup_company_id == $company_id) {
            
            // Perform restore
            $db_path = QrServeDbManager::getDbPath($company_id);
            $backup_dir = dirname($backup_file);
            
            // Validate backup file
            $result = shell_exec("gunzip -l '$backup_file' 2>&1");
            
            if ($result["returncode"] == 0) {
                
                // Extract SQL file
                $sql_file = str_replace('.gz', '', $backup_file);
                $sql_file = $backup_dir . "/" . basename($sql_file);
                
                // Remove existing database
                unlink($db_path);
                
                // Import backup
                exec("sqlite3 $db_path < $sql_file");
                
                // Log this restore
                $log_entry = "[" . date("Y-m-d H:i:s") . "] RESTORED company {$company_id} from backup";
                file_put_contents("/opt/QrServe/logs/restore_history.log", $log_entry . "\n", FILE_APPEND);
                
                header("Location: dashboard.php?restored=" . $company_id . "&success=true");
                exit;
                
            } else {
                $error = "Failed to decompress backup file";
            }
        } else {
                $error = "Invalid backup file or wrong company ID";
            }
        } else {
            $error = "Backup file not found";
        }
        
        if (isset($error)) {
            // Log failed attempt
            $log_entry = "[" . date("Y-m-d H:i:s") . "] FAILED RESTORE attempt for company {$company_id}: $error";
            file_put_contents("/opt/QrServe/logs/restore_attempts.log", $log_entry . "\n", FILE_APPEND);
            
            header("Location: restore_company.php?id=" . $company_id . "&error=" . urlencode($error));
            exit;
        }
    }
</div>
</div>
</div>

// Get available backups
 $backup_dir = "/opt/QrServe/backups/company_{$company_id}";
 $backups = [];

if (is_dir($backup_dir)) {
    $files = glob("${backup_dir}/backup_*.sql.gz");
    
    rsort($files); // Newest first
    
    foreach ($files as $file) {
        preg_match('/backup_(\d{14})/', basename($file), $match);
        
        $backups[] = [
            "filename" => basename($file),
            "display_date" => date("Y-m-d H:i:s", filemtime($file)),
            "size" => filesize($file),
            "path" => $file
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restore <?= htmlspecialchars($company['name']) ?> - QrServe</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
.gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.card-shadow { box-shadow: 0 10px 40px rgba(0,0,0,0.12); }
</style>
</head>
<body class="bg-gray-100 min-h-screen">

<nav class="bg-white shadow-md px-6 py-4 sticky top-0 z-50">
<div class="max-w-7xl mx-auto flex items-center justify-between">
    <div class="flex items-center space-x-4">
        <a href="dashboard.php" class="text-blue-600 font-bold hover:text-blue-800 transition">
            <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
        </a>
        <img src="https://tscocdn.sirv.com/TSCO-LOGO-EN-DARK.png" alt="TSCO" class="h-9 mr-3">
        <span class="text-xl font-bold text-gray-800">RESTORE CENTER</span>
    </div>
    <div class="text-sm text-gray-500">
        <i class="fas fa-clock mr-1"></i> <?= date("F j, Y") ?>
    </div>
</nav>

<main class="max-w-6xl mx-auto px-6 py-8 mt-16">

<!-- Header Section -->
<div class="gradient-bg rounded-2xl p-8 mb-8 text-white shadow-lg">
    <div class="flex items-center space-x-4">
        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-inner">
            <i class="fas fa-database text-4xl text-indigo-600"></i>
        </div>
        <div>
            <h1 class="text-3xl font-black">Enterprise Data Recovery</h1>
            <p class="text-indigo-200 font-semibold mt-1">Advanced restoration center</p>
        </div>
    </div>
</div>

<!-- Warning Box -->
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-lg mb-8">
    <div class="flex items-start">
        <i class="fas fa-exclamation-triangle text-yellow-600 mr-3 text-xl"></i>
        <div>
            <h3 class="font-bold text-yellow-800">Important Notice</h3>
            <ul class="list-disc list-inside ml-4 mt-2 text-sm text-yellow-700">
                <li><strong>This will REPLACE all current data</strong> with the selected backup</li>
                <li>All changes since backup will be permanently lost</li>
                <li>Other restaurants will NOT be affected (data isolation)</li>
                <li>Consider notifying restaurant owner before restoring</li>
            </ul>
        </div>
    </div>
</div>

<!-- Company Info Card -->
<div class="bg-white rounded-xl shadow-md p-6 mb-8 border border-gray-200">
    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
        <i class="fas fa-building mr-2 text-blue-600"></i>
        Target Restaurant Information
    </h3>
    <div class="grid grid-cols-2 gap-4 text-sm">
        <div>
            <p class="text-gray-600">Company ID:</p>
            <p class="font-mono font-bold text-gray-900"><?= $company_id ?></p>
        </div>
        <div>
            <p class="text-gray-600">Restaurant Name:</p>
            <p class="font-bold text-gray-900"><?= htmlspecialchars($company['name']) ?></p>
        </div>
        <div>
            <p class="text-gray-600">Slug:</p>
            <p class="font-mono text-gray-900"><?= htmlspecialchars($company['slug']) ?></p>
        </div>
        <div>
            <p class="text-gray-600">Status:</p>
            <span class="px-2 py-1 rounded-full bg-green-100 text-green-800 text-xs font-bold">ACTIVE</span>
        </div>
    </div>
</div>

<?php if (empty($backups)): ?>
    <!-- No Backups Available -->
    <div class="bg-gray-50 rounded-xl p-12 text-center">
        <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
        <h3 class="text-xl font-bold text-gray-600 mb-2">No Backups Found</h3>
        <p class="text-gray-500 mb-6">This restaurant has never been backed up.</p>
        <a href="tools/backup_company.php?id=<?= $company_id ?>" 
           class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-semibold transition">
            <i class="fas fa-download mr-2"></i>Create First Backup Now
        </a>
    </div>
<?php else: ?>

    <div class="mb-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-history text-green-600 mr-2"></i>
            Available Restorations (<?= count($backups) ?> found)
        </h3>
        
        <div class="space-y-3 max-h-96 overflow-y-auto border rounded-lg">
            <?php foreach ($backups as $backup): ?>
            <form method="post" action="" class="border rounded-lg p-4 hover:bg-gray-50 transition group">
                <input type="hidden" name="backup_file" value="<?= htmlspecialchars($backup['path']) ?>">
                
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center space-x-4 flex-grow">
                        <div class="w-12 h-12 bg-gradient-to-br from-green-100 to-emerald-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-file-archive text-green-600 text-lg"></i>
                        </div>
                        
                        <div class="flex-grow">
                            <p class="font-semibold text-sm text-gray-900"><?= $backup['filename'] ?></p>
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar-alt mr-1"></i><?= $backup['display_date'] ?>
                                <span class="ml-2">|</span>
                                <span class="ml-2">$(numfmt --to=iec-i --suffix=B $backup['size'])</span>
                            </div>
                        </div>
                        
                        <button type="submit" 
                                onclick="return confirm('Are you sure?\\n\\nThis will replace ALL current data with backup data!')"
                                class="px-6 py-2 bg-red-600 text-white rounded-lg font-bold hover:bg-red-700 transition opacity-75 group-hover:opacity-100">
                            <i class="fas fa-undo mr-1"></i>RESTORE THIS VERSION
                        </button>
                    </div>
                </form>
            <?php endforeach; ?>
        </div>
        
        <div class="mt-4 pt-4 border-t">
            <a href="tools/backup_company.php?id=<?= $company_id ?>" 
               class="text-blue-600 hover:text-blue-800 text-sm font-semibold inline-flex items-center">
                <i class="fas fa-plus-circle mr-1"></i>Create New Backup
            </a>
        </div>
    </div>
<?php } ?>

</main>

<footer class="text-center py-6 text-gray-400 text-sm mt-12 border-t">
    <p>&copy; 2026 Technology Solutions Company (TSCO Group)</p>
    <p>QrServe Enterprise System v5.0</p>
</footer>

</body>
</html>
