-- Create mineadmin user if it doesn't exist and grant privileges
CREATE USER IF NOT EXISTS 'mineadmin'@'%' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON mineadmin.* TO 'mineadmin'@'%';

-- Also create root user for any host and grant privileges
CREATE USER IF NOT EXISTS 'root'@'%' IDENTIFIED BY '123456';
GRANT ALL PRIVILEGES ON mineadmin.* TO 'root'@'%';

-- Flush privileges to apply changes
FLUSH PRIVILEGES;