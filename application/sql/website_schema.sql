-- Darzi Baba public website schema (same DB as ERP — no duplicate customer master).
-- Run once on MariaDB/MySQL (e.g. alibaba). Safe to re-run (IF NOT EXISTS / INSERT IGNORE).

-- ---------------------------------------------------------------------------
-- Settings / CMS
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS web_settings (
  id TINYINT UNSIGNED NOT NULL DEFAULT 1,
  site_name VARCHAR(120) NOT NULL DEFAULT 'Darzi Baba',
  tagline_en VARCHAR(255) NOT NULL DEFAULT '',
  tagline_ur VARCHAR(255) NOT NULL DEFAULT '',
  about_en TEXT NULL,
  about_ur TEXT NULL,
  contact_phone VARCHAR(40) NOT NULL DEFAULT '',
  contact_whatsapp VARCHAR(40) NOT NULL DEFAULT '',
  contact_email VARCHAR(120) NOT NULL DEFAULT '',
  contact_address_en TEXT NULL,
  contact_address_ur TEXT NULL,
  seo_title_default VARCHAR(160) NOT NULL DEFAULT 'Darzi Baba',
  seo_description_default VARCHAR(320) NOT NULL DEFAULT '',
  og_image_url VARCHAR(500) NOT NULL DEFAULT '',
  feature_shop TINYINT(1) NOT NULL DEFAULT 1,
  feature_measurements TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO web_settings (id, site_name, tagline_en, tagline_ur, about_en, seo_title_default, seo_description_default)
VALUES (
  1,
  'Darzi Baba',
  'Premium tailoring & measurements, made simple.',
  'پریمیم درزی خدمات اور ناپ — آسان اور معتبر۔',
  'Darzi Baba brings our trusted tailoring workshop online: view your saved measurements, add new ناپ with guided tutorials, and shop selected products.',
  'Darzi Baba — Tailoring, Measurements & Shop',
  'View and submit your tailor measurements securely, watch measurement tutorials, and shop Darzi Baba products.'
);

CREATE TABLE IF NOT EXISTS web_pages (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  slug VARCHAR(120) NOT NULL,
  title_en VARCHAR(200) NOT NULL DEFAULT '',
  title_ur VARCHAR(200) NOT NULL DEFAULT '',
  body_en MEDIUMTEXT NULL,
  body_ur MEDIUMTEXT NULL,
  seo_title VARCHAR(160) NOT NULL DEFAULT '',
  seo_description VARCHAR(320) NOT NULL DEFAULT '',
  is_published TINYINT(1) NOT NULL DEFAULT 0,
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_sections (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  page_key VARCHAR(80) NOT NULL DEFAULT 'home',
  section_key VARCHAR(80) NOT NULL,
  title_en VARCHAR(200) NOT NULL DEFAULT '',
  title_ur VARCHAR(200) NOT NULL DEFAULT '',
  body_en TEXT NULL,
  body_ur TEXT NULL,
  image_url VARCHAR(500) NOT NULL DEFAULT '',
  cta_label_en VARCHAR(80) NOT NULL DEFAULT '',
  cta_label_ur VARCHAR(80) NOT NULL DEFAULT '',
  cta_url VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_sections_page_key (page_key, section_key),
  KEY idx_web_sections_page_active (page_key, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO web_sections (page_key, section_key, title_en, title_ur, body_en, body_ur, cta_label_en, cta_label_ur, cta_url, sort_order, is_active, updated_at)
VALUES
('home', 'hero', 'Darzi Baba', 'درزی بابا', 'Trusted tailoring measurements and selected products — connected to our workshop.', 'معتبر ناپ اور منتخب مصنوعات — ہماری ورکشاپ سے جڑا ہوا۔', 'View my measurements', 'میرا ناپ دیکھیں', '/measurements', 10, 1, NOW()),
('home', 'services', 'Our services', 'ہماری خدمات', 'Measurement lookup, guided remote ناپ submission, tutorials, and shop.', 'ناپ تلاش، رہنمائی کے ساتھ ناپ جمع کرانا، ٹیوٹوریلز، اور شاپ۔', '', '', '', 20, 1, NOW()),
('home', 'howto', 'How it works', 'کیسے کام کرتا ہے', 'Enter your customer number, verify by WhatsApp OTP, then view or submit measurements.', 'اپنا کسٹمر نمبر درج کریں، واٹس ایپ OTP سے تصدیق کریں، پھر ناپ دیکھیں یا جمع کروائیں۔', 'Start now', 'اب شروع کریں', '/measurements', 30, 1, NOW());

CREATE TABLE IF NOT EXISTS web_faqs (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  question_en VARCHAR(400) NOT NULL DEFAULT '',
  question_ur VARCHAR(400) NOT NULL DEFAULT '',
  answer_en TEXT NULL,
  answer_ur TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  KEY idx_web_faqs_active (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_media (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  file_path VARCHAR(500) NOT NULL,
  file_url VARCHAR(500) NOT NULL DEFAULT '',
  mime_type VARCHAR(100) NOT NULL DEFAULT '',
  alt_en VARCHAR(255) NOT NULL DEFAULT '',
  alt_ur VARCHAR(255) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Measurement tutorials (keys match ERP measurement columns)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS web_measurement_guides (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  field_key VARCHAR(64) NOT NULL,
  garment VARCHAR(40) NOT NULL DEFAULT 'kurta',
  label_en VARCHAR(120) NOT NULL DEFAULT '',
  label_ur VARCHAR(120) NOT NULL DEFAULT '',
  instruction_en TEXT NULL,
  instruction_ur TEXT NULL,
  example_value VARCHAR(40) NOT NULL DEFAULT '',
  unit VARCHAR(16) NOT NULL DEFAULT 'inch',
  min_value DECIMAL(8,2) NULL DEFAULT NULL,
  max_value DECIMAL(8,2) NULL DEFAULT NULL,
  video_url VARCHAR(500) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_measurement_guides_field (field_key),
  KEY idx_web_measurement_guides_garment (garment, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Customer portal auth (links to existing customer.code — no duplicate customers)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS web_otp_challenges (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_code INT NOT NULL,
  man_napno VARCHAR(64) NOT NULL DEFAULT '',
  phone_masked VARCHAR(32) NOT NULL DEFAULT '',
  phone_normalized VARCHAR(24) NOT NULL DEFAULT '',
  otp_hash VARCHAR(255) NOT NULL,
  attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
  max_attempts TINYINT UNSIGNED NOT NULL DEFAULT 5,
  expires_at DATETIME NOT NULL,
  consumed_at DATETIME NULL DEFAULT NULL,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  PRIMARY KEY (id),
  KEY idx_web_otp_customer (customer_code, created_at),
  KEY idx_web_otp_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_customer_sessions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_code INT NOT NULL,
  token_hash CHAR(64) NOT NULL,
  expires_at DATETIME NOT NULL,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  user_agent VARCHAR(255) NOT NULL DEFAULT '',
  revoked_at DATETIME NULL DEFAULT NULL,
  created_at DATETIME NOT NULL,
  last_seen_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_customer_sessions_token (token_hash),
  KEY idx_web_customer_sessions_customer (customer_code),
  KEY idx_web_customer_sessions_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_api_rate_limits (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  bucket_key VARCHAR(120) NOT NULL,
  hit_count INT UNSIGNED NOT NULL DEFAULT 1,
  window_start DATETIME NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_api_rate_limits_bucket (bucket_key),
  KEY idx_web_api_rate_limits_window (window_start)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Website measurement submissions (approval before writing live measurement)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS web_measurement_submissions (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  customer_code INT NOT NULL,
  measurement_code INT NULL DEFAULT NULL,
  submission_type ENUM('create','update') NOT NULL DEFAULT 'create',
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  payload_json MEDIUMTEXT NOT NULL,
  remarks TEXT NULL,
  review_note TEXT NULL,
  reviewed_by VARCHAR(100) NULL DEFAULT NULL,
  reviewed_at DATETIME NULL DEFAULT NULL,
  source VARCHAR(40) NOT NULL DEFAULT 'website',
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_web_meas_sub_customer (customer_code, created_at),
  KEY idx_web_meas_sub_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- E-commerce (separate from tailoring order_mst)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS web_categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  parent_id INT UNSIGNED NULL DEFAULT NULL,
  slug VARCHAR(120) NOT NULL,
  name_en VARCHAR(160) NOT NULL DEFAULT '',
  name_ur VARCHAR(160) NOT NULL DEFAULT '',
  description_en TEXT NULL,
  description_ur TEXT NULL,
  image_url VARCHAR(500) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_categories_slug (slug),
  KEY idx_web_categories_parent (parent_id, is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NULL DEFAULT NULL,
  slug VARCHAR(160) NOT NULL,
  sku VARCHAR(80) NOT NULL DEFAULT '',
  name_en VARCHAR(200) NOT NULL DEFAULT '',
  name_ur VARCHAR(200) NOT NULL DEFAULT '',
  short_desc_en VARCHAR(400) NOT NULL DEFAULT '',
  short_desc_ur VARCHAR(400) NOT NULL DEFAULT '',
  description_en MEDIUMTEXT NULL,
  description_ur MEDIUMTEXT NULL,
  price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  compare_at_price DECIMAL(12,2) NULL DEFAULT NULL,
  stock_qty INT NULL DEFAULT NULL,
  track_inventory TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  seo_title VARCHAR(160) NOT NULL DEFAULT '',
  seo_description VARCHAR(320) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_products_slug (slug),
  KEY idx_web_products_category (category_id, is_active),
  KEY idx_web_products_featured (is_featured, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_product_images (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  product_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(500) NOT NULL,
  alt_en VARCHAR(255) NOT NULL DEFAULT '',
  alt_ur VARCHAR(255) NOT NULL DEFAULT '',
  sort_order INT NOT NULL DEFAULT 0,
  is_primary TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_web_product_images_product (product_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_orders (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_number VARCHAR(32) NOT NULL,
  customer_code INT NULL DEFAULT NULL,
  guest_name VARCHAR(160) NOT NULL DEFAULT '',
  guest_phone VARCHAR(40) NOT NULL DEFAULT '',
  guest_email VARCHAR(120) NOT NULL DEFAULT '',
  shipping_address TEXT NULL,
  status ENUM('pending','confirmed','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  payment_method ENUM('cod','pay_in_shop','other') NOT NULL DEFAULT 'cod',
  payment_status ENUM('unpaid','paid','refunded','partial') NOT NULL DEFAULT 'unpaid',
  subtotal DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  discount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  currency CHAR(3) NOT NULL DEFAULT 'PKR',
  customer_note TEXT NULL,
  admin_note TEXT NULL,
  ip_address VARCHAR(45) NOT NULL DEFAULT '',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_web_orders_number (order_number),
  KEY idx_web_orders_customer (customer_code, created_at),
  KEY idx_web_orders_status (status, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS web_order_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  product_name VARCHAR(200) NOT NULL DEFAULT '',
  sku VARCHAR(80) NOT NULL DEFAULT '',
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY idx_web_order_items_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- RBAC permissions for Manage Website
-- ---------------------------------------------------------------------------
INSERT IGNORE INTO rbac_permissions (permission_key, permission_label, legacy_page, category) VALUES
('website.manage', 'Manage Website', NULL, 'Website'),
('website.content', 'Website Content', NULL, 'Website'),
('website.products', 'Website Products', NULL, 'Website'),
('website.orders', 'Website Orders', NULL, 'Website'),
('website.measurements', 'Website Measurement Submissions', NULL, 'Website');

INSERT IGNORE INTO rbac_role_permissions (role_id, permission_key)
SELECT 1, permission_key FROM rbac_permissions
WHERE permission_key LIKE 'website.%';

INSERT IGNORE INTO rbac_role_permissions (role_id, permission_key)
SELECT 2, permission_key FROM rbac_permissions
WHERE permission_key IN ('website.manage', 'website.content', 'website.products', 'website.orders', 'website.measurements');

-- Default shop categories (ERP garment list, without pajama)
INSERT INTO web_categories (id, slug, name_en, name_ur, image_url, sort_order, is_active, created_at)
VALUES
  (1, 'kameez-shalwar', 'Kameez Shalwar', 'قمیض شلوار', '', 10, 1, NOW()),
  (2, 'pent', 'Pent', 'پینٹ', '', 20, 1, NOW()),
  (3, 'shirt', 'Shirt', 'شرٹ', '', 30, 1, NOW()),
  (4, 'coat-sherwani', 'Coat + Sherwani', 'کوٹ + شیروانی', '', 40, 1, NOW()),
  (5, 'waistcoat', 'Waistcoat', 'ویسٹ کوٹ', '', 50, 1, NOW())
ON DUPLICATE KEY UPDATE
  slug = VALUES(slug),
  name_en = VALUES(name_en),
  name_ur = VALUES(name_ur),
  sort_order = VALUES(sort_order),
  is_active = 1,
  updated_at = NOW();
