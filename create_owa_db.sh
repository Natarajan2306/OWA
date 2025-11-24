#!/bin/bash

# Script to create OWA database and user
# Run this with: bash create_owa_db.sh

echo "Creating OWA database and user..."
echo ""

# Database configuration
DB_NAME="owa_analytics"
DB_USER="owa_user"
DB_PASSWORD="owa_password_123"

# Create database and user using sudo mysql
sudo mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SELECT 'Database and user created successfully!' AS Status;
SHOW DATABASES LIKE '$DB_NAME';
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database setup complete!"
    echo ""
    echo "Use these credentials in the OWA installation form:"
    echo "  Database Host: localhost"
    echo "  Database Port: 3306"
    echo "  Database Name: $DB_NAME"
    echo "  Database User: $DB_USER"
    echo "  Database Password: $DB_PASSWORD"
else
    echo ""
    echo "❌ Error setting up database. Please check the error messages above."
    exit 1
fi


