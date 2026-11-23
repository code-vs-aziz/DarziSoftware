-- Order snapshot: freeze naap / customer / measurement at booking time.
-- Run once on the database. Safe to re-run (checks via information_schema).

-- order_mst header snapshot
SET @db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_mst' AND COLUMN_NAME = 'snap_man_napno') = 0,
  'ALTER TABLE order_mst ADD COLUMN snap_man_napno VARCHAR(64) NULL DEFAULT NULL AFTER naap_no',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_mst' AND COLUMN_NAME = 'snap_customer_name') = 0,
  'ALTER TABLE order_mst ADD COLUMN snap_customer_name VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER snap_man_napno',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_mst' AND COLUMN_NAME = 'snap_customer_code') = 0,
  'ALTER TABLE order_mst ADD COLUMN snap_customer_code INT NULL DEFAULT NULL AFTER snap_customer_name',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- order_dtl line snapshot (suit label at booking)
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'order_dtl' AND COLUMN_NAME = 'snap_suit_name') = 0,
  'ALTER TABLE order_dtl ADD COLUMN snap_suit_name VARCHAR(128) NULL DEFAULT NULL AFTER SNO',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Full measurement body snapshot per order (mirrors measurement table fields used on print).
-- TEXT columns avoid InnoDB row-size limit (#1118) with many measurement fields.
CREATE TABLE IF NOT EXISTS order_measurement_snap (
  order_trnno INT NOT NULL,
  measurement_code INT NULL DEFAULT NULL,
  snap_at DATETIME NOT NULL,
  customer_code INT NULL DEFAULT NULL,
  KL TEXT NULL,
  FPL TEXT NULL,
  BL TEXT NULL,
  Teera TEXT NULL,
  BP TEXT NULL,
  CL TEXT NULL,
  Chaati TEXT NULL,
  Gala TEXT NULL,
  Poncha_Choraic TEXT NULL,
  Poncha_Chorai TEXT NULL,
  Shalwar_Lenght TEXT NULL,
  Asan TEXT NULL,
  Side_Pocket_Size TEXT NULL,
  SC TEXT NULL,
  BG TEXT NULL,
  Lambai TEXT NULL,
  Poncha TEXT NULL,
  shalwar_Asan TEXT NULL,
  Peeir TEXT NULL,
  PLambai TEXT NULL,
  PPoncha TEXT NULL,
  PAsan TEXT NULL,
  PPeeir TEXT NULL,
  PentLambai TEXT NULL,
  pentWaiste TEXT NULL,
  HIP TEXT NULL,
  PentPoncha TEXT NULL,
  PentThai TEXT NULL,
  SLambai TEXT NULL,
  SBazoo TEXT NULL,
  STeera TEXT NULL,
  SGeera TEXT NULL,
  geera TEXT NULL,
  SChaati TEXT NULL,
  SKamar TEXT NULL,
  SGaala TEXT NULL,
  CoatLambai TEXT NULL,
  CoatBazoo TEXT NULL,
  CoatTeera TEXT NULL,
  CoatChaati TEXT NULL,
  CoatHIP TEXT NULL,
  CoatKamar TEXT NULL,
  crossback TEXT NULL,
  wastLambai TEXT NULL,
  wastTeera TEXT NULL,
  wastChaati TEXT NULL,
  wastHIP TEXT NULL,
  wastKamar TEXT NULL,
  remarks TEXT NULL,
  pait TEXT NULL,
  WASIT TEXT NULL,
  CKC TEXT NULL,
  FRONT_Pocket_Size TEXT NULL,
  CAKLAMBAI TEXT NULL,
  KAAFCURAI TEXT NULL,
  MODA_CURAI TEXT NULL,
  CoatGaala TEXT NULL,
  wastGaala TEXT NULL,
  PRIMARY KEY (order_trnno),
  KEY idx_oms_measurement_code (measurement_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;
