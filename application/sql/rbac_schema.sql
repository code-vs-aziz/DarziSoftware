CREATE TABLE IF NOT EXISTS rbac_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(100) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS rbac_permissions (
  permission_key VARCHAR(120) NOT NULL PRIMARY KEY,
  permission_label VARCHAR(150) NOT NULL,
  legacy_page VARCHAR(150) NULL,
  category VARCHAR(80) NOT NULL DEFAULT 'General',
  is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE IF NOT EXISTS rbac_role_permissions (
  role_id INT NOT NULL,
  permission_key VARCHAR(120) NOT NULL,
  PRIMARY KEY (role_id, permission_key),
  INDEX idx_rbac_role_permissions_permission (permission_key)
);

CREATE TABLE IF NOT EXISTS rbac_user_roles (
  username VARCHAR(100) NOT NULL,
  role_id INT NOT NULL,
  PRIMARY KEY (username, role_id),
  INDEX idx_rbac_user_roles_role (role_id)
);

INSERT IGNORE INTO rbac_roles (id, role_name, description, is_active) VALUES
(1, 'Super Admin', 'Full access to all modules and RBAC administration', 1),
(2, 'Manager', 'Operational manager role', 1),
(3, 'Staff', 'Limited staff role', 1);

INSERT IGNORE INTO rbac_permissions (permission_key, permission_label, legacy_page, category) VALUES
('dashboard.view', 'Dashboard', NULL, 'Dashboard'),
('rbac.admin', 'RBAC Administration', NULL, 'Administration'),
('configuration.manage', 'Business Configuration', NULL, 'Administration'),
('customer.manage', 'Customer', 'customer', 'Master Data'),
('measurement.manage', 'Measurement', NULL, 'Master Data'),
('order.create', 'Order Create', NULL, 'Orders'),
('order.status', 'Order Status', 'Order Status', 'Orders'),
('order.detail', 'Order Detail', 'Order Detail', 'Orders'),
('order.ready_delivered', 'Ready for Delivered', 'Ready for Delivered', 'Orders'),
('order.blank_print', 'Blank Print', 'Blank Print', 'Orders'),
('transfer.booking', 'Pending Booking', 'Pending Booking', 'Transfers'),
('transfer.cutting', 'Pending Cutting', 'Pending Cutting', 'Transfers'),
('transfer.ho2', 'Pending HO2', 'Pending HO2', 'Transfers'),
('transfer.stitching', 'Pending Stitching', 'Pending Stitching', 'Transfers'),
('transfer.manage', 'Transfer Orders', NULL, 'Transfers'),
('cash.receipt', 'Cash Payment/Receipt', 'Cash Payment/Receipt', 'Cash'),
('reports.menu', 'Reports', 'Reports', 'Reports'),
('report.employee_ledger', 'Employee Ledger', 'Employee Ledger', 'Reports'),
('report.customer_ledger', 'Customer Ledger', 'Customer Ledger', 'Reports'),
('report.cash_ledger', 'Cash Ledger', 'Cash Ledger', 'Reports'),
('report.expenses', 'Expenses Sheet', 'Expenses Sheet', 'Reports'),
('report.pending_payment', 'Pending Payment Sheet', 'Pending Payment Sheet', 'Reports'),
('report.all_sale', 'All Sale Report', 'All Sale Report', 'Reports'),
('report.delivery_order', 'Delivery Order Sheet', 'Backup', 'Reports'),
('report.customer_total_sale', 'Customer Total Sale', 'Backup', 'Reports'),
('report.employee_report', 'Employee Report', 'Backup', 'Reports'),
('report.booking', 'Booking', 'Backup', 'Reports'),
('report.financial_dashboard', 'Financial Dashboard', NULL, 'Reports'),
('report.sales_expense', 'Sales Vs Expense', NULL, 'Reports'),
('report.cash_flow', 'Cash Flow Report', NULL, 'Reports'),
('employee.manage', 'Employee', 'Employee', 'Master Data'),
('suit.manage', 'Suit Type', 'Suit Type', 'Master Data'),
('style.manage', 'Style', 'Style', 'Master Data'),
('embroidery.manage', 'Embroidery', 'Embroidery', 'Master Data'),
('button.manage', 'Button', NULL, 'Master Data');

INSERT IGNORE INTO rbac_role_permissions (role_id, permission_key)
SELECT 1, permission_key FROM rbac_permissions;

INSERT IGNORE INTO rbac_user_roles (username, role_id)
SELECT UNAME, 1 FROM users WHERE UNAME = 'alibaba';
