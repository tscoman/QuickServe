-- ============================================================
-- QrServe Company Database Schema
-- Version: 5.0.0
-- Architecture: Schema-Per-Tenant
-- Each company gets identical structure
-- ============================================================

-- Enable WAL mode for better concurrency
PRAGMA journal_mode = WAL;

-- Foreign keys enabled
PRAGMA foreign_keys = ON;

-- ============================================================
-- TABLE: companies
-- Stores restaurant/company information
-- ============================================================
CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    slug TEXT UNIQUE NOT NULL,
    phone TEXT DEFAULT '',
    logo_url TEXT DEFAULT '',
    street_address TEXT DEFAULT '',
    website_url TEXT DEFAULT '',
    instagram_url TEXT DEFAULT '',
    whatsapp_number TEXT DEFAULT '',
    vat_number TEXT DEFAULT '',
    cr_number TEXT DEFAULT '',
    custom_domain TEXT DEFAULT NULL,
    port_number INTEGER DEFAULT NULL,
    theme ENUM('midnight', 'garden', 'classic', 'rustic') DEFAULT 'midnight',
    menu_header_text TEXT DEFAULT '',
    menu_footer_text DEFAULT '',
    menu_logo_size VARCHAR(20) DEFAULT 'h-16',
    receipt_logo_position ENUM('top', 'center', 'none') DEFAULT 'top',
    receipt_show_vat BOOLEAN DEFAULT 1,
    receipt_show_cr BOOLEAN DEFAULT 1,
    receipt_show_tax_breakdown BOOLEAN DEFAULT 1,
    default_language_id INTEGER DEFAULT 1,
    currency_code VARCHAR(10) DEFAULT 'OMR',
    currency_symbol VARCHAR(10) DEFAULT 'ر.ع.',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_companies_slug ON companies(slug);
CREATE INDEX IF NOT EXISTS idx_companies_port ON companies(port_number);

-- ============================================================
-- TABLE: users
-- All user accounts (SA, Admin, Staff, Customers)
-- ============================================================
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL DEFAULT 0,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role ENUM('super_admin', 'company_admin', 'staff') NOT NULL,
    phone TEXT DEFAULT '',
    reset_token TEXT DEFAULT NULL,
    reset_expires INTEGER DEFAULT NULL,
    is_active BOOLEAN DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    login_count INTEGER DEFAULT 0,
    created_at DATETIME DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_company_role ON users(company_id, role);

-- ============================================================
-- TABLE: categories
-- Menu categories (Burgers, Pizzas, Drinks, etc.)
-- ============================================================
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    printer_id INTEGER DEFAULT NULL,
    image_url TEXT DEFAULT '',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (printer_id) REFERENCES printers(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_categories_company ON categories(company_id);
CREATE INDEX IF NOT EXISTS idx_categories_active ON categories(company_id, is_active);

-- ============================================================
-- TABLE: printers
-- Printer configurations for each company
-- ============================================================
CREATE TABLE IF NOT EXISTS printers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    printer_name TEXT NOT NULL,
    printer_type ENUM('kitchen', 'cashier', 'bar') DEFAULT 'kitchen',
    identifier TEXT DEFAULT '', -- Can be IP address or OS printer name
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_printers_company ON printers(company_id);

-- ============================================================
-- TABLE: menu_items
-- Individual menu items/products
-- ============================================================
CREATE TABLE IF NOT EXISTS menu_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT DEFAULT '',
    price DECIMAL(10,2) NOT NULL,
    image_url TEXT DEFAULT '',
    is_available BOOLEAN DEFAULT 1,
    is_special BOOLEAN DEFAULT 0,
    preparation_time INTEGER DEFAULT 0,
    instruction_notes TEXT DEFAULT '',
    sort_order INTEGER DEFAULT 0,
    calories INTEGER DEFAULT 0,
    allergens TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_menu_items_category ON menu_items(category_id);
CREATE INDEX IF NOT EXISTS idx_menu_items_company ON menu_items(company_id);
CREATE INDEX IF NOT EXISTS idx_menu_items_available ON menu_items(company_id, is_available);

-- ============================================================
-- TABLE: tables (Dine-in tables)
-- QR codes for each table
-- ============================================================
CREATE TABLE IF NOT EXISTS tables (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    table_number TEXT NOT NULL,
    table_name TEXT DEFAULT '',
    qr_code_url TEXT DEFAULT '',
    seats_capacity INTEGER DEFAULT 4,
    is_waiter_mode BOOLEAN DEFAULT 0,
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_tables_company ON tables(company_id);
CREATE INDEX IF NOT EXISTS idx_tables_table_num ON tables(company_id, table_number);

-- ============================================================
-- TABLE: orders
-- Customer orders
-- ============================================================
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    order_number TEXT NOT NULL,
    table_id INTEGER DEFAULT NULL,
    customer_name TEXT DEFAULT '',
    customer_phone TEXT DEFAULT '',
    customer_vehicle_info TEXT DEFAULT '',
    session_token TEXT DEFAULT '',
    order_type ENUM('dine_in', 'takeout', 'delivery', 'waiter_assisted') DEFAULT 'dine_in',
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'served', 'completed', 'cancelled', 'refunded') DEFAULT 'pending',
    subtotal DECIMAL(10,2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    total DECIMAL(10, 2) NOT NULL,
    payment_method ENUM('cash', 'stripe', 'paypal', 'mobile_upload', 'offline_card', 'custom_api') DEFAULT 'cash',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_proof_url TEXT DEFAULT '',
    proof_status ENUM('none', 'awaiting_proof', 'approved', 'rejected') DEFAULT 'none',
    assigned_waiter_id INTEGER DEFAULT NULL,
    original_order_id INTEGER DEFAULT NULL,
    refund_required BOOLEAN DEFAULT 0,
    notes TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    completed_at DATETIME DEFAULT NULL,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    FOREIGN KEY (table_id) REFERENCES tables(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_waiter_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE INDEX IF NOT EXISTS idx_orders_company ON orders(company_id);
CREATE INDEX IF NOT EXISTS idx_orders_status ON orders(company_id, status);
CREATE INDEX IF NOT EXISTS idx_orders_order_num ON orders(company_id, order_number);
CREATE INDEX IF NOT EXISTS idx_orders_session ON orders(session_token);
CREATE INDEX IF NOT EXISTS idx_orders_created ON orders(created_at);

-- ============================================================
-- TABLE: order_items
-- Line items within an order
-- ============================================================
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    company_id INTEGER NOT NULL,
    menu_item_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    special_instructions TEXT DEFAULT '',
    preparation_notes TEXT DEFAULT '',
    item_status ENUM('ordered', 'preparing', 'ready', 'served', 'cancelled') DEFAULT 'ordered',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_order_items_order ON order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_item ON order_items(menu_item_id);

-- ============================================================
-- TABLE: taxes
-- Tax configuration per company
-- ============================================================
CREATE TABLE IF NOT EXISTS taxes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    tax_name TEXT NOT NULL,
    tax_rate DECIMAL(5, 2) NOT NULL,
    tax_type ENUM('vat', 'service_charge', 'tourism', 'delivery', 'other') DEFAULT 'vat',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_taxes_company ON taxes(company_id);

-- ============================================================
-- TABLE: mobile_payment_numbers
-- Multiple bank account numbers for mobile payments
-- ============================================================
CREATE TABLE IF NOT EXISTS mobile_payment_numbers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    phone_number TEXT NOT NULL,
    bank_name TEXT DEFAULT '',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS mobile_phones_company ON mobile_payment_numbers(company_id);

-- ============================================================
-- TABLE: languages
-- Supported languages for UI translation
-- ============================================================
CREATE TABLE IF NOT EXISTS languages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(5) UNIQUE NOT NULL,
    name VARCHAR(50) NOT NULL,
    direction ENUM('ltr', 'rtl') DEFAULT 'ltr',
    is_active BOOLEAN DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Seed initial languages
INSERT OR IGNORE INTO languages VALUES 
    (1, 'English', 'ltr', 1),
    (2, 'العربية', 'rtl', 1);

-- ============================================================
-- TABLE: settings
-- Company-level key-value store
-- ============================================================
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    setting_key TEXT NOT NULL,
    setting_value TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE(company_id, setting_key),
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

-- ============================================================
-- Table: audit_log
-- Tracks important actions for security/compliance
-- ============================================================
CREATE TABLE IF NOT EXISTS audit_log (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER DEFAULT 0,
    user_id INTEGER DEFAULT NULL,
    action TEXT NOT NULL,
    details TEXT DEFAULT '',
    ip_address TEXT DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS audit_log_company ON audit_log(company_id);
CREATE INDEX IF NOT EXISTS audit_log_time ON audit_log(created_at);

-- ============================================================
-- VIEWS (for easier querying)
-- ============================================================

-- View: Active companies with stats
CREATE VIEW IF NOT EXISTS view_active_companies AS
SELECT 
    c.*,
    COUNT(DISTINCT o.id) as total_orders,
    (SELECT COUNT(*) FROM users u WHERE u.company_id = c.id AND u.role != 'super_admin') as staff_count,
    (SELECT COUNT(*) FROM tables t WHERE t.company_id = c.id AND t.is_active = 1) as active_tables
FROM companies c
LEFT JOIN orders o ON o.company_id = c.id
WHERE c.is_active = 1
GROUP BY c.id
ORDER BY c.created_at DESC;

-- View: Recent orders across all companies
CREATE VIEW IF NOT EXISTS view_recent_orders AS
SELECT 
    o.id,
    c.name as company_name,
    o.order_number,
    o.customer_name,
    o.total,
    o.status,
    o.payment_method,
    o.created_at
FROM orders o
JOIN companies c ON o.company_id = c.id
ORDER BY o.created_at DESC
LIMIT 50;

-- View: Companies needing backup
CREATE VIEW IF NOT EXISTS view_backup_status AS
SELECT 
    c.id,
    c.name,
    (SELECT COUNT(*) FROM backups b WHERE b.company_id = c.id) as backup_count,
    (SELECT MAX(b.created_at) FROM backups b WHERE b.company_id = c.id) as last_backup,
    CASE 
        WHEN (SELECT COUNT(*) FROM backups b WHERE b.company_id = c.id) > 0 THEN 'OK'
        WHEN c.is_active = 1 THEN 'NEEDS BACKUP'
        ELSE 'INACTIVE'
    END as backup_status
FROM companies c
ORDER BY c.name;

