SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

ALTER TABLE order_predefined
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

ALTER TABLE order_predefined
  MODIFY label_text VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  MODIFY insert_text VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;

TRUNCATE TABLE order_predefined;

INSERT INTO order_predefined (label_text, insert_text, row_group, sort_order) VALUES
('گیرا چورس', 'گیرا چورس -', 1, 10),
('گیرا گول', 'گیرا گول -', 1, 20),
('کالر کف', 'کالر کف -', 1, 30),
('کف', 'کف -', 1, 40),
('ہاف بین گول', 'ہاف بین گول -', 1, 50),
('ہاف بین چورس', 'ہاف بین چورس -', 1, 60),
('بین پٹی ایک اینچ', 'بین پٹی ایک اینچ -', 1, 70),
('چاک پٹی پر کاج بٹن', 'چاک پٹی پر کاج بٹن -', 1, 80),
('بین پٹی سوا', 'بین پٹی سوا -', 1, 90),
('باذٰٰو گول', 'باذٰٰو گول -', 1, 100),
('اوف وائٹ', 'اوف وائٹ -', 2, 10),
('بلیک', 'بلیک -', 2, 20),
('گرے', 'گرے -', 2, 30),
('گرین', 'گرین -', 2, 40),
('سفید', 'سفید -', 2, 50),
('ڈزائن والا', 'ڈزائن والا -', 2, 60),
('زنک', 'زنک -', 2, 70),
('براون', ' براون -', 2, 80),
('میرون', ' میرون -', 2, 90),
('نیوی', 'نیوی  -', 2, 100),
('لائٹ گرے', 'لائٹ گرے -', 2, 110);
