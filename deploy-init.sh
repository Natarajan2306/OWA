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
        echo "Config file created from template."
    else
        echo "Warning: Config template not found at $CONFIG_DIST"
    fi
fi

# Set proper permissions
if [ -f "$CONFIG_FILE" ]; then
    chmod 644 "$CONFIG_FILE"
    chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true
    echo "Config file permissions set."
fi

# Ensure owa-data directory exists and is writable
if [ ! -d "/var/www/html/owa-data" ]; then
    mkdir -p /var/www/html/owa-data
fi

chmod -R 755 /var/www/html/owa-data 2>/dev/null || true
chown -R www-data:www-data /var/www/html/owa-data 2>/dev/null || true

echo "Deployment initialization complete."

