<?php
/**
 * QrServe Multi-Tenant Database Manager
 * 
 * Enterprise-grade connection pooling and isolation
 * Each company gets its own database file
 * 
 * @version 5.0.0
 * @author TSCO Group AI Core
 * @license Proprietary
 */

class QrServeDbManager {
    
    /**
     * Connection pool cache
     * Stores active PDO connections per company
     */
    private static $connections = [];
    
    /**
     * Get database connection for specific company
     * 
     * @param int $company_id The company ID (0 = Super Admin/System)
     * @return PDO Database connection object
     */
    public static function get($company_id = 0) {
        
        // Normalize ID
        $company_id = (int)$company_id;
        
        // Return cached connection if exists
        if (isset(self::$connections[$company_id])) {
            return self::$connections[$company_id];
        }
        
        // Determine database path based on company
        if ($company_id === 0) {
            // Super Admin / System database
            $db_path = '/opt/QrServe/databases/system.db';
        } else {
            // Company-specific database
            $db_path = '/opt/QrServe/databases/company_' . $company_id . '.db';
            
            // Check if database file exists
            if (!file_exists($db_path)) {
                throw new Exception("Database not found for company ID: $company_id");
            }
        }
        
        // Create PDO connection
        try {
            $pdo = new PDO('sqlite:' . $db_path);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA journal_mode=WAL'); // Enable WAL for better performance
            $pdo->exec('PRAGMA foreign_keys=ON');
            
            // Cache the connection
            self::$connections[$company_id] = $pdo;
            
            return $pdo;
            
        } catch (PDOException $e) {
            error_log("QrServe DB Error [Company $company_id]: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    /**
     * Close specific company connection
     * 
     * @param int $company_id Company ID to close
     */
    public static function close($company_id) {
        if (isset(self::$connections[$company_id])) {
            self::$connections[$company_id] = null;
            return true;
        }
        return false;
    }
    
    /**
     * Close ALL connections
     */
    public static function closeAll() {
        foreach (self::$connections as $id => $conn) {
        $conn = null; // Close connection
        }
        self::$connections = [];
    }
    
    /**
     * Get raw database path for company
     * 
     * @param int $company_id
     * @return string Path to database file
     */
    public static function getDbPath($company_id) {
        if ($company_id === 0) {
            return '/opt/QrServe/databases/system.db';
        } else {
            return '/opt/QrServe/databases/company_' . $company_id . '.db';
        }
    }
    
    /**
     * Check if company database exists
     * 
     * @param int $company_id
     * @return bool
     */
    public static function dbExists($company_id) {
        $path = self::getDbPath($company_id);
        return file_exists($path);
    }
    
    /**
     * Get database size in human-readable format
     * 
     * @param int $company_id
     * @return string Formatted size
     */
    public static function getDbSize($company_id) {
        $path = self::getDbPath($company_id);
        
        if (!file_exists($path)) {
            return 'Not found';
        }
        
        $size = filesize($path);
        
        if ($size < 1024) {
            return round($size, 2) . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } elseif ($size < 1073741824) {
            return round($size / 1048576, 2) . ' MB';
        } else {
            return round($size / 1073741824, 2) . ' GB';
        }
    }
}
