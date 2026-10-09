-- Run once as a database administrator (replace the password first):
--   mysql -u root -p < database/setup/create_app_user.sql
-- Then put the same user/password in app/Config/config.local.php.
CREATE USER IF NOT EXISTS 'pharmaflow_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_BEFORE_RUNNING';
GRANT SELECT, INSERT, UPDATE, DELETE ON medical_management.* TO 'pharmaflow_app'@'localhost';
-- Migrations, restore and the permissions page create tables, so they need more.
-- Use a separate admin account for those, or temporarily:
-- GRANT CREATE, ALTER, INDEX, DROP, REFERENCES ON medical_management.* TO 'pharmaflow_app'@'localhost';
FLUSH PRIVILEGES;
