#!/bin/bash
# Deployment initialization script for Open Web Analytics
# This script ensures the config file exists and sets proper permissions

set -e

CONFIG_FILE="/var/www/html/owa-config.php"
CONFIG_DIST="/var/www/html/owa-config-dist.php"

# If config file doesn't exist, create it from dist template
if [ ! -f "$CONFIG_FILE" ]; then
    echo "Config file not found. Creating from template..."
    if [ -f "$CONFIG_DIST" ]; then
        cp "$CONFIG_DIST" "$CONFIG_FILE"
        echo "Config file created from template at $CONFIG_FILE"
        
        # Verify the file was created
        if [ -f "$CONFIG_FILE" ]; then
            echo "Config file verified: exists and readable"
            ls -la "$CONFIG_FILE"
        else
            echo "ERROR: Config file was not created successfully!"
            exit 1
        fi
    else
        echo "Warning: Config template not found at $CONFIG_DIST"
        echo "Listing files in /var/www/html:"
        ls -la /var/www/html/ | grep -E "config|owa" || true
    fi
fi

# Set proper permissions
if [ -f "$CONFIG_FILE" ]; then
    chmod 644 "$CONFIG_FILE"
    chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true
    echo "Config file permissions set."
    echo "Final config file status:"
    ls -la "$CONFIG_FILE"
else
    echo "WARNING: Config file still does not exist after creation attempt!"
fi

# Ensure owa-data directory exists and is writable
if [ ! -d "/var/www/html/owa-data" ]; then
    mkdir -p /var/www/html/owa-data
fi

# Ensure log directories exist
mkdir -p /var/www/html/owa-data/logs 2>/dev/null || true
mkdir -p /var/www/html/owa-data/caches 2>/dev/null || true
mkdir -p /var/log/apache2 2>/dev/null || true

chmod -R 775 /var/www/html/owa-data 2>/dev/null || true
chown -R www-data:www-data /var/www/html/owa-data 2>/dev/null || true
chmod 777 /var/log/apache2 2>/dev/null || true

echo "Deployment initialization complete."

