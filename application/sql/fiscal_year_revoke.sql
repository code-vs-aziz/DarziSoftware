-- Optional: remove fiscal year tables from live DB after revoking FY feature in PHP.
-- Does NOT drop archive schemas (e.g. alibaba_fy2025) — drop those manually if created:
--   DROP DATABASE IF EXISTS alibaba_fy2025;

DROP TABLE IF EXISTS fy_close_job;
DROP TABLE IF EXISTS fy_master_snapshot;
DROP TABLE IF EXISTS fiscal_year;

DELETE FROM rbac_role_permissions WHERE permission_key IN ('configuration.close_fiscal_year', 'fiscal_year.switch');
DELETE FROM rbac_permissions WHERE permission_key IN ('configuration.close_fiscal_year', 'fiscal_year.switch');
