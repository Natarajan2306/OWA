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
    
    # Update config file with environment variables if they're set
    # This is important for Coolify where database host might be dynamic
    if [ -n "$OWA_DB_HOST" ] || [ -n "$OWA_DB_NAME" ] || [ -n "$OWA_DB_USER" ]; then
        echo "Environment variables detected. Updating config file..."
        
        # Use PHP script for more reliable config file updates
        if [ -f "/var/www/html/update_config.php" ]; then
            cd /var/www/html
            php update_config.php 2>&1
            UPDATE_EXIT=$?
            if [ $UPDATE_EXIT -eq 0 ]; then
                echo "Config file updated successfully using PHP script."
            else
                echo "WARNING: PHP config update failed. Falling back to sed method..."
                # Fallback to sed method
                if [ -n "$OWA_DB_TYPE" ]; then
                    sed -i "s/define('OWA_DB_TYPE',.*);/define('OWA_DB_TYPE', '${OWA_DB_TYPE}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_DB_HOST" ]; then
                    sed -i "s/define('OWA_DB_HOST',.*);/define('OWA_DB_HOST', '${OWA_DB_HOST}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_DB_NAME" ]; then
                    sed -i "s/define('OWA_DB_NAME',.*);/define('OWA_DB_NAME', '${OWA_DB_NAME}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_DB_USER" ]; then
                    sed -i "s/define('OWA_DB_USER',.*);/define('OWA_DB_USER', '${OWA_DB_USER}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_DB_PASSWORD" ]; then
                    ESCAPED_PASSWORD=$(echo "$OWA_DB_PASSWORD" | sed "s/'/\\\\'/g")
                    sed -i "s/define('OWA_DB_PASSWORD',.*);/define('OWA_DB_PASSWORD', '${ESCAPED_PASSWORD}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_DB_PORT" ]; then
                    sed -i "s/define('OWA_DB_PORT',.*);/define('OWA_DB_PORT', '${OWA_DB_PORT}');/" "$CONFIG_FILE" 2>/dev/null || true
                fi
                if [ -n "$OWA_PUBLIC_URL" ]; then
                    sed -i "s|define('OWA_PUBLIC_URL',.*);|define('OWA_PUBLIC_URL', '${OWA_PUBLIC_URL}');|" "$CONFIG_FILE" 2>/dev/null || true
                fi
            fi
        else
            echo "WARNING: update_config.php not found. Using sed method..."
            # Fallback to sed method
            if [ -n "$OWA_DB_TYPE" ]; then
                sed -i "s/define('OWA_DB_TYPE',.*);/define('OWA_DB_TYPE', '${OWA_DB_TYPE}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_DB_HOST" ]; then
                sed -i "s/define('OWA_DB_HOST',.*);/define('OWA_DB_HOST', '${OWA_DB_HOST}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_DB_NAME" ]; then
                sed -i "s/define('OWA_DB_NAME',.*);/define('OWA_DB_NAME', '${OWA_DB_NAME}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_DB_USER" ]; then
                sed -i "s/define('OWA_DB_USER',.*);/define('OWA_DB_USER', '${OWA_DB_USER}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_DB_PASSWORD" ]; then
                ESCAPED_PASSWORD=$(echo "$OWA_DB_PASSWORD" | sed "s/'/\\\\'/g")
                sed -i "s/define('OWA_DB_PASSWORD',.*);/define('OWA_DB_PASSWORD', '${ESCAPED_PASSWORD}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_DB_PORT" ]; then
                sed -i "s/define('OWA_DB_PORT',.*);/define('OWA_DB_PORT', '${OWA_DB_PORT}');/" "$CONFIG_FILE" 2>/dev/null || true
            fi
            if [ -n "$OWA_PUBLIC_URL" ]; then
                sed -i "s|define('OWA_PUBLIC_URL',.*);|define('OWA_PUBLIC_URL', '${OWA_PUBLIC_URL}');|" "$CONFIG_FILE" 2>/dev/null || true
            fi
        fi
    fi
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
            
            # Update config file with environment variables if they're set
            if [ -n "$OWA_DB_HOST" ] || [ -n "$OWA_DB_NAME" ] || [ -n "$OWA_DB_USER" ]; then
                echo "Environment variables detected. Updating config file..."
                
                # Use PHP script for more reliable config file updates
                if [ -f "/var/www/html/update_config.php" ]; then
                    cd /var/www/html
                    php update_config.php 2>&1
                else
                    echo "WARNING: update_config.php not found. Using sed method..."
                    # Fallback to sed method
                    if [ -n "$OWA_DB_TYPE" ]; then
                        sed -i "s/define('OWA_DB_TYPE',.*);/define('OWA_DB_TYPE', '${OWA_DB_TYPE}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_DB_HOST" ]; then
                        sed -i "s/define('OWA_DB_HOST',.*);/define('OWA_DB_HOST', '${OWA_DB_HOST}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_DB_NAME" ]; then
                        sed -i "s/define('OWA_DB_NAME',.*);/define('OWA_DB_NAME', '${OWA_DB_NAME}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_DB_USER" ]; then
                        sed -i "s/define('OWA_DB_USER',.*);/define('OWA_DB_USER', '${OWA_DB_USER}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_DB_PASSWORD" ]; then
                        ESCAPED_PASSWORD=$(echo "$OWA_DB_PASSWORD" | sed "s/'/\\\\'/g")
                        sed -i "s/define('OWA_DB_PASSWORD',.*);/define('OWA_DB_PASSWORD', '${ESCAPED_PASSWORD}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_DB_PORT" ]; then
                        sed -i "s/define('OWA_DB_PORT',.*);/define('OWA_DB_PORT', '${OWA_DB_PORT}');/" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                    if [ -n "$OWA_PUBLIC_URL" ]; then
                        sed -i "s|define('OWA_PUBLIC_URL',.*);|define('OWA_PUBLIC_URL', '${OWA_PUBLIC_URL}');|" "$CONFIG_FILE" 2>/dev/null || true
                    fi
                fi
            fi
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

# Set proper permissions - ensure www-data can write to logs
chown -R www-data:www-data /var/www/html/owa-data 2>/dev/null || true
chmod -R 777 /var/www/html/owa-data 2>/dev/null || true
chmod 777 /var/log/apache2 2>/dev/null || true

# Ensure logs directory is specifically writable
chmod 777 /var/www/html/owa-data/logs 2>/dev/null || true
chown www-data:www-data /var/www/html/owa-data/logs 2>/dev/null || true

# Reset admin user if environment variables are set
if [ -n "$OWA_ADMIN_USER" ] && [ -n "$OWA_ADMIN_PASSWORD" ] && [ -n "$OWA_ADMIN_EMAIL" ]; then
    echo ""
    echo "=== Admin User Reset ==="
    echo "Admin user environment variables detected."
    echo "Resetting admin user..."
    
    # Wait longer for database to be ready (especially important in Docker)
    echo "Waiting for database to be ready..."
    sleep 5
    
    # Check if config file exists and database is configured
    if [ -f "$CONFIG_FILE" ]; then
        # Try to create admin user with retries
        MAX_RETRIES=3
        RETRY_COUNT=0
        RESET_EXIT=1
        
        while [ $RETRY_COUNT -lt $MAX_RETRIES ] && [ $RESET_EXIT -ne 0 ]; do
            if [ $RETRY_COUNT -gt 0 ]; then
                echo "Retry attempt $RETRY_COUNT of $MAX_RETRIES..."
                sleep 3
            fi
            
            # Run the reset admin user script
            cd /var/www/html
            php reset_admin_user.php "$OWA_ADMIN_USER" "$OWA_ADMIN_PASSWORD" "$OWA_ADMIN_EMAIL" 2>&1
            RESET_EXIT=$?
            
            RETRY_COUNT=$((RETRY_COUNT + 1))
        done
        
        if [ $RESET_EXIT -eq 0 ]; then
            echo "Admin user reset completed successfully."
        else
            echo "WARNING: Admin user reset failed after $MAX_RETRIES attempts (exit code: $RESET_EXIT)."
            echo "This might be because:"
            echo "  1. The database is not ready yet"
            echo "  2. The OWA installation has not been completed (database tables don't exist)"
            echo "  3. There's a database connection issue"
            echo ""
            echo "You can manually reset the admin user later using:"
            echo "  php reset_admin_user.php $OWA_ADMIN_USER $OWA_ADMIN_PASSWORD $OWA_ADMIN_EMAIL"
            echo ""
            echo "Or complete the installation via the web interface first."
        fi
    else
        echo "WARNING: Config file not found. Skipping admin user reset."
        echo "Admin user will be created during installation."
    fi
else
    echo ""
    echo "NOTE: Admin user environment variables not set."
    echo "To automatically create/reset admin user on deployment, set:"
    echo "  OWA_ADMIN_USER - Admin username"
    echo "  OWA_ADMIN_PASSWORD - Admin password"
    echo "  OWA_ADMIN_EMAIL - Admin email"
fi

echo "Deployment initialization complete."

