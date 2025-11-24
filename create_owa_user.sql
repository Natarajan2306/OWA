-- Create a database for Open Web Analytics
CREATE DATABASE IF NOT EXISTS owa_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create a user for OWA (replace 'your_password_here' with a secure password)
CREATE USER IF NOT EXISTS 'owa_user'@'localhost' IDENTIFIED BY 'owa_password_123';

-- Grant all privileges on the owa_analytics database to the owa_user
GRANT ALL PRIVILEGES ON owa_analytics.* TO 'owa_user'@'localhost';

-- Apply the changes
FLUSH PRIVILEGES;

-- Show the created database
SHOW DATABASES LIKE 'owa_analytics';

