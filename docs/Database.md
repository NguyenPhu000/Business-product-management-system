-- =======================================================
-- Database: business_product_management_system
-- Compatible with XAMPP / MariaDB
-- =======================================================

CREATE DATABASE IF NOT EXISTS `business_product_management_system`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `business_product_management_system`;

-- ==============================
-- A. Quản trị
-- ==============================

CREATE TABLE roles (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(50) NOT NULL UNIQUE,
description VARCHAR(255)
) ENGINE=InnoDB;

CREATE TABLE users (
id INT AUTO_INCREMENT PRIMARY KEY,
username VARCHAR(100) NOT NULL UNIQUE,
email VARCHAR(150) NOT NULL UNIQUE,
password_hash VARCHAR(255) NOT NULL,
role_id INT DEFAULT NULL,
full_name VARCHAR(150),
phone VARCHAR(50),
status TINYINT(1) DEFAULT 1,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE user_logs (
id BIGINT AUTO_INCREMENT PRIMARY KEY,
user_id INT NULL,
action VARCHAR(100) NOT NULL,
object_type VARCHAR(100),
object_id INT,
meta JSON,
ip VARCHAR(45),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
INDEX(user_id),
FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE system_config (
`key` VARCHAR(100) PRIMARY KEY,
`value` TEXT,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

--
-- Cấu trúc bảng cho bảng `password_reset_requests`
--

CREATE TABLE `password_reset_requests` (
`id` int(11) NOT NULL,
`user_id` int(11) NOT NULL,
`email` varchar(255) NOT NULL,
`status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
`requested_at` datetime NOT NULL,
`approved_by` int(11) DEFAULT NULL,
`approved_at` datetime DEFAULT NULL,
`new_password` varchar(255) DEFAULT NULL COMMENT 'Mật khẩu mới sau khi được phê duyệt'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
-- ==============================
-- B. Category - Brand - Supplier
-- ==============================

CREATE TABLE categories (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) NOT NULL,
slug VARCHAR(200) NOT NULL,
parent_id INT DEFAULT NULL,
is_active TINYINT(1) DEFAULT 1,
sort_order INT DEFAULT 0,
INDEX(parent_id)
) ENGINE=InnoDB;

CREATE TABLE brands (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(150) NOT NULL,
description TEXT,
logo_url VARCHAR(255),
is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE suppliers (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(200) NOT NULL,
contact VARCHAR(200),
phone VARCHAR(50),
email VARCHAR(150),
address TEXT,
is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- ==============================
-- C. Products & Variants
-- ==============================

CREATE TABLE products (
id INT AUTO_INCREMENT PRIMARY KEY,
sku VARCHAR(100) NOT NULL UNIQUE,
name VARCHAR(255) NOT NULL,
short_desc VARCHAR(512),
long_desc TEXT,
brand_id INT DEFAULT NULL,
default_tax_id INT DEFAULT NULL,
status TINYINT(1) DEFAULT 1,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (brand_id) REFERENCES brands(id)
) ENGINE=InnoDB;

CREATE TABLE product_images (
id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
variant_id INT DEFAULT NULL,
url VARCHAR(255),
is_primary TINYINT(1) DEFAULT 0,
sort_order INT DEFAULT 0,
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_variants (
id INT AUTO_INCREMENT PRIMARY KEY,
product_id INT NOT NULL,
sku VARCHAR(120) NOT NULL,
attributes JSON DEFAULT NULL,
price DECIMAL(15,2) DEFAULT 0,
unit_cost DECIMAL(15,2) DEFAULT 0,
barcode VARCHAR(100),
is_active TINYINT(1) DEFAULT 1,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
INDEX(product_id),
UNIQUE(product_id, sku),
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_categories (
product_id INT NOT NULL,
category_id INT NOT NULL,
PRIMARY KEY(product_id, category_id),
FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE product_combos (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(255) NOT NULL,
sku VARCHAR(120) UNIQUE,
price DECIMAL(15,2),
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE product_combo_items (
id INT AUTO_INCREMENT PRIMARY KEY,
combo_id INT NOT NULL,
product_variant_id INT NOT NULL,
qty INT NOT NULL DEFAULT 1,
FOREIGN KEY (combo_id) REFERENCES product_combos(id) ON DELETE CASCADE,
FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB;

-- ==============================
-- D. Inventory & Transactions
-- ==============================

CREATE TABLE inventory (
id INT AUTO_INCREMENT PRIMARY KEY,
product_variant_id INT NOT NULL,
warehouse VARCHAR(150) DEFAULT 'default',
quantity INT DEFAULT 0,
min_threshold INT DEFAULT 0,
last_updated DATETIME DEFAULT CURRENT_TIMESTAMP,
UNIQUE(product_variant_id, warehouse),
FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE inventory_transactions (
id BIGINT AUTO_INCREMENT PRIMARY KEY,
product_variant_id INT NOT NULL,
warehouse VARCHAR(150) DEFAULT 'default',
type ENUM('import','export','adjust') NOT NULL,
quantity_change INT NOT NULL,
reference_type VARCHAR(50),
reference_id INT,
note TEXT,
created_by INT,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (product_variant_id) REFERENCES product_variants(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ==============================
-- E. Purchase (Import)
-- ==============================

CREATE TABLE purchase_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
po_number VARCHAR(120) UNIQUE,
supplier_id INT,
total_amount DECIMAL(15,2) DEFAULT 0,
status ENUM('draft','completed','cancelled') DEFAULT 'draft',
created_by INT,
purchase_date DATE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
) ENGINE=InnoDB;

CREATE TABLE purchase_details (
id INT AUTO_INCREMENT PRIMARY KEY,
purchase_order_id INT NOT NULL,
product_variant_id INT NOT NULL,
quantity INT NOT NULL,
import_price DECIMAL(15,2) NOT NULL,
subtotal DECIMAL(15,2) AS (quantity \* import_price) STORED,
FOREIGN KEY (purchase_order_id) REFERENCES purchase_orders(id) ON DELETE CASCADE,
FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB;

-- ==============================
-- F. Sales (Export)
-- ==============================

CREATE TABLE sales_orders (
id INT AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(120) UNIQUE,
customer_name VARCHAR(255),
total_excl_tax DECIMAL(15,2) DEFAULT 0,
total_tax DECIMAL(15,2) DEFAULT 0,
total_incl_tax DECIMAL(15,2) AS (total_excl_tax + total_tax) STORED,
status ENUM('draft','completed','cancelled') DEFAULT 'draft',
created_by INT,
sale_date DATE,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE sales_details (
id INT AUTO_INCREMENT PRIMARY KEY,
sales_order_id INT NOT NULL,
product_variant_id INT NOT NULL,
quantity INT NOT NULL,
sale_price DECIMAL(15,2) NOT NULL,
unit_cost DECIMAL(15,2) DEFAULT 0,
discount DECIMAL(12,2) DEFAULT 0,
line_excl_tax DECIMAL(15,2) AS ((quantity \* sale_price) - discount) STORED,
line_tax DECIMAL(15,2) DEFAULT 0,
FOREIGN KEY (sales_order_id) REFERENCES sales_orders(id) ON DELETE CASCADE,
FOREIGN KEY (product_variant_id) REFERENCES product_variants(id)
) ENGINE=InnoDB;

-- ==============================
-- G. Tax & Report
-- ==============================

CREATE TABLE tax (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100),
rate DECIMAL(5,2) NOT NULL,
type ENUM('product','system') DEFAULT 'product',
is_active TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE report_summary (
id INT AUTO_INCREMENT PRIMARY KEY,
period_date DATE NOT NULL,
period_type ENUM('daily','monthly') DEFAULT 'daily',
total_revenue DECIMAL(18,2) DEFAULT 0,
total_cost DECIMAL(18,2) DEFAULT 0,
total_tax DECIMAL(18,2) DEFAULT 0,
total_profit DECIMAL(18,2) DEFAULT 0,
created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
UNIQUE(period_date, period_type)
) ENGINE=InnoDB;
