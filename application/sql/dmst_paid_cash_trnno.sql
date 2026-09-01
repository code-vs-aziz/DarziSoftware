-- Link dmst pending-payment rows to cash receipt for safe revert.
-- Run once on each environment.

ALTER TABLE dmst
  ADD COLUMN paid_cash_trnno INT NULL DEFAULT NULL AFTER paid_Date;
