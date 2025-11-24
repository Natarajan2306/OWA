#!/bin/bash
# Quick Database Setup for Open Web Analytics
# Run: bash QUICK_SETUP.sh

echo "Setting up OWA database..."
sudo mysql -u root <<'MYSQL_SCRIPT'
CREATE DATABASE IF NOT EXISTS owa_analytics CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'owa_user'@'localhost' IDENTIFIED BY 'owa_password_123';
GRANT ALL PRIVILEGES ON owa_analytics.* TO 'owa_user'@'localhost';
FLUSH PRIVILEGES;
SELECT '✅ Database setup complete!' AS Status;
MYSQL_SCRIPT

echo ""
echo "✅ Done! Use these credentials in OWA installation:"
echo "   Host: localhost | Port: 3306 | Database: owa_analytics"
echo "   User: owa_user | Password: owa_password_123"


