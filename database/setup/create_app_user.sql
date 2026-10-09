-- Run once as a database administrator (replace the password first):
--   mysql -u root -p < database/setup/create_app_user.sql
-- Then put the same user/password in app/Config/config.local.php.
CREATE USER IF NOT EXISTS 'pharmaflow_app'@'localhost' IDENTIFIED BY 'CHANGE_ME_BEFORE_RUNNING';
GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, ALTER, INDEX ON medical_management.* TO 'pharmaflow_app'@'localhost';
-- No DROP/GRANT/FILE: restore and first-time migrations need an admin account.
FLUSH PRIVILEGES;
