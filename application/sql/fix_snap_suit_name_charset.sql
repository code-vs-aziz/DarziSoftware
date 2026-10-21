-- Fix Urdu suit names corrupted as "????" on transfer screens.
-- snap_suit_name was latin1; suit.name is utf8. Prefer catalog + repair snaps.

ALTER TABLE order_dtl
  MODIFY snap_suit_name VARCHAR(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL;

UPDATE order_dtl d
JOIN suit s ON s.SNO = d.SNO
SET d.snap_suit_name = s.name
WHERE d.SNO IS NOT NULL
  AND (
    d.snap_suit_name IS NULL
    OR d.snap_suit_name = ''
    OR d.snap_suit_name REGEXP '^[?[:space:]]+$'
  );
