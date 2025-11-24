#!/bin/bash

# Open Web Analytics Database Setup Script
# This script helps you set up the database for OWA

echo "=========================================="
echo "Open Web Analytics Database Setup"
echo "=========================================="
echo ""

# Database configuration
DB_NAME="owa_analytics"
DB_USER="owa_user"
DB_PASSWORD="owa_secure_password_$(date +%s | tail -c 6)"

echo "This script will:"
echo "1. Create a database named: $DB_NAME"
echo "2. Create a MySQL user named: $DB_USER"
echo "3. Grant privileges to the user"
echo ""
echo "Generated password: $DB_PASSWORD"
echo ""
read -p "Do you want to continue? (y/n) " -n 1 -r
echo ""

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Setup cancelled."
    exit 1
fi

# Try to connect to MySQL
echo ""
echo "Attempting to connect to MySQL..."

# Try without password first (for Homebrew MariaDB)
if mysql -u root -e "SELECT 1;" > /dev/null 2>&1; then
    echo "✓ Connected to MySQL without password"
    MYSQL_CMD="mysql -u root"
elif sudo mysql -u root -e "SELECT 1;" > /dev/null 2>&1; then
    echo "✓ Connected to MySQL with sudo"
    MYSQL_CMD="sudo mysql -u root"
else
    echo "✗ Could not connect to MySQL automatically"
    echo ""
    echo "Please run the following SQL commands manually:"
    echo "----------------------------------------"
    echo "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo "CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';"
    echo "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';"
    echo "FLUSH PRIVILEGES;"
    echo "----------------------------------------"
    echo ""
    echo "Or run: mysql -u root -p"
    echo "Then paste the SQL commands above."
    exit 1
fi

# Create database and user
echo "Creating database and user..."
$MYSQL_CMD <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SELECT 'Database and user created successfully!' AS Status;
EOF

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "✓ Database setup complete!"
    echo "=========================================="
    echo ""
    echo "Use these credentials in the OWA installation form:"
    echo "  Database Host: localhost"
    echo "  Database Port: 3306"
    echo "  Database Name: $DB_NAME"
    echo "  Database User: $DB_USER"
    echo "  Database Password: $DB_PASSWORD"
    echo ""
    echo "Save this password - you'll need it for the installation!"
else
    echo ""
    echo "✗ Error setting up database. Please check the error messages above."
    exit 1
fi

