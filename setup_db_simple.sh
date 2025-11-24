#!/bin/bash

# Simple database setup script
# Run: bash setup_db_simple.sh

DB_NAME="owa_analytics"
DB_USER="owa_user"
DB_PASSWORD="owa_password_123"

echo "Creating OWA database and user..."
echo ""

# Create SQL file
SQL_FILE="/tmp/owa_setup_$$.sql"
cat > "$SQL_FILE" <<EOF
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
SELECT 'Database and user created successfully!' AS Status;
EOF

echo "Running SQL commands with sudo..."
sudo mysql -u root < "$SQL_FILE"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Database setup complete!"
    echo ""
    echo "Use these credentials in OWA:"
    echo "  Host: localhost"
    echo "  Port: 3306"
    echo "  Database: $DB_NAME"
    echo "  User: $DB_USER"
    echo "  Password: $DB_PASSWORD"
    rm -f "$SQL_FILE"
else
    echo ""
    echo "❌ Setup failed. Please run manually:"
    echo "   sudo mysql -u root"
    echo "   Then paste the SQL from: $SQL_FILE"
    exit 1
fi


