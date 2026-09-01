SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

CREATE TABLE IF NOT EXISTS order_predefined (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  label_text VARCHAR(200) NOT NULL,
  insert_text VARCHAR(200) NOT NULL,
  row_group TINYINT UNSIGNED NOT NULL DEFAULT 1,
  sort_order INT UNSIGNED NOT NULL DEFAULT 0,
  is_active TINYINT UNSIGNED NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_order_predefined_active (is_active, row_group, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE order_predefined
  MODIFY label_text VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY insert_text VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

INSERT INTO order_predefined (label_text, insert_text, row_group, sort_order)
SELECT * FROM (
  SELECT 'گیرا چورس' AS label_text, 'گیرا چورس -' AS insert_text, 1 AS row_group, 10 AS sort_order UNION ALL
  SELECT 'گیرا گول', 'گیرا گول -', 1, 20 UNION ALL
  SELECT 'کالر کف', 'کالر کف -', 1, 30 UNION ALL
  SELECT 'کف', 'کف -', 1, 40 UNION ALL
  SELECT 'ہاف بین گول', 'ہاف بین گول -', 1, 50 UNION ALL
  SELECT 'ہاف بین چورس', 'ہاف بین چورس -', 1, 60 UNION ALL
  SELECT 'بین پٹی ایک اینچ', 'بین پٹی ایک اینچ -', 1, 70 UNION ALL
  SELECT 'چاک پٹی پر کاج بٹن', 'چاک پٹی پر کاج بٹن -', 1, 80 UNION ALL
  SELECT 'بین پٹی سوا', 'بین پٹی سوا -', 1, 90 UNION ALL
  SELECT 'باذٰٰو گول', 'باذٰٰو گول -', 1, 100 UNION ALL
  SELECT 'اوف وائٹ', 'اوف وائٹ -', 2, 10 UNION ALL
  SELECT 'بلیک', 'بلیک -', 2, 20 UNION ALL
  SELECT 'گرے', 'گرے -', 2, 30 UNION ALL
  SELECT 'گرین', 'گرین -', 2, 40 UNION ALL
  SELECT 'سفید', 'سفید -', 2, 50 UNION ALL
  SELECT 'ڈزائن والا', 'ڈزائن والا -', 2, 60 UNION ALL
  SELECT 'زنک', 'زنک -', 2, 70 UNION ALL
  SELECT 'براون', ' براون -', 2, 80 UNION ALL
  SELECT 'میرون', ' میرون -', 2, 90 UNION ALL
  SELECT 'نیوی', 'نیوی  -', 2, 100 UNION ALL
  SELECT 'لائٹ گرے', 'لائٹ گرے -', 2, 110
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM order_predefined LIMIT 1);
