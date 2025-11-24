#!/bin/bash
# Deployment initialization script for Open Web Analytics
# This script ensures the config file exists and sets proper permissions
# Note: In production with volume mounts, the file may need to be created outside the mount

# Don't exit on error - we want to continue even if file creation fails
set +e

CONFIG_FILE="/var/www/html/owa-config.php"
CONFIG_DIST="/var/www/html/owa-config-dist.php"

echo "=== Deployment Initialization Script ==="
echo "Checking for config file: $CONFIG_FILE"

# Check if config file exists
if [ -f "$CONFIG_FILE" ]; then
    echo "Config file already exists."
    ls -la "$CONFIG_FILE"
else
    echo "Config file not found. Attempting to create from template..."
    
    # Check if template exists
    if [ ! -f "$CONFIG_DIST" ]; then
        echo "WARNING: Config template not found at $CONFIG_DIST"
        echo "This is OK - the file may be created during Docker build or by the installation wizard."
        echo "Listing files in /var/www/html:"
        ls -la /var/www/html/ | head -20
    else
        echo "Template found. Attempting to copy..."
        echo "Template file info:"
        ls -la "$CONFIG_DIST"
        
        # Try to copy the file
        cp "$CONFIG_DIST" "$CONFIG_FILE" 2>&1
        COPY_EXIT=$?
        
        if [ $COPY_EXIT -eq 0 ]; then
            echo "Copy command succeeded (exit code: $COPY_EXIT)."
        else
            echo "WARNING: Copy command failed (exit code: $COPY_EXIT)."
            echo "This may be because /var/www/html is a volume mount."
        fi
        
        # Wait a moment and verify
        sleep 0.2
        
        # Verify the file was created
        if [ -f "$CONFIG_FILE" ]; then
            echo "Config file verified: exists"
            ls -la "$CONFIG_FILE"
        else
            echo "WARNING: Config file was not created or was removed."
            echo "This is expected if /var/www/html is a volume mount."
            echo "The config file should be created during Docker build or by the installation wizard."
        fi
    fi
fi

# Set proper permissions if file exists
if [ -f "$CONFIG_FILE" ]; then
    chmod 644 "$CONFIG_FILE" 2>/dev/null || true
    chown www-data:www-data "$CONFIG_FILE" 2>/dev/null || true
    echo "Config file permissions set."
    echo "Final config file status:"
    ls -la "$CONFIG_FILE"
else
    echo "NOTE: Config file does not exist. This is OK - it will be created by the installation wizard."
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

