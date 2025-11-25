#!/bin/bash
# Helper script to install OWA and create admin user via Docker
# Usage: ./install-owa.sh [user_id] [password] [email] [domain]
# Example: ./install-owa.sh admin mypassword123 admin@example.com analytics.pdevsecops.com
# If no arguments provided, uses environment variables from docker-compose.yml

CONTAINER_NAME="owa-web"

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Error: Container '${CONTAINER_NAME}' is not running."
    echo "Please start the container first with: docker-compose up -d"
    exit 1
fi

# Get arguments or use defaults
if [ $# -ge 3 ]; then
    USER_ID=$1
    PASSWORD=$2
    EMAIL=$3
    DOMAIN=${4:-analytics.pdevsecops.com}
else
    # Try to get from environment or use defaults
    USER_ID=${OWA_ADMIN_USER:-admin}
    PASSWORD=${OWA_ADMIN_PASSWORD:-$(openssl rand -base64 16 | tr -d "=+/" | cut -c1-16)}
    EMAIL=${OWA_ADMIN_EMAIL:-admin@example.com}
    DOMAIN=${OWA_PUBLIC_URL:-analytics.pdevsecops.com}
    
    # Extract domain from URL if it's a full URL
    if [[ $DOMAIN == http* ]]; then
        DOMAIN=$(echo $DOMAIN | sed -e 's|^[^/]*//||' -e 's|/.*$||')
    fi
    
    if [ $# -eq 0 ]; then
        echo "No arguments provided. Using:"
        echo "  Username: $USER_ID"
        echo "  Password: $PASSWORD"
        echo "  Email: $EMAIL"
        echo "  Domain: $DOMAIN"
        echo ""
    fi
fi

echo "Installing OWA and creating admin user..."
echo "  Username: $USER_ID"
echo "  Email: $EMAIL"
echo "  Domain: $DOMAIN"
echo "Running in container: $CONTAINER_NAME"
echo ""

# Execute the install script in the container
docker exec -it $CONTAINER_NAME php /var/www/html/install_and_create_user.php "$USER_ID" "$PASSWORD" "$EMAIL" "$DOMAIN"

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "Installation completed successfully!"
    echo "=========================================="
    echo ""
    echo "Login credentials:"
    echo "  Username: $USER_ID"
    echo "  Password: $PASSWORD"
    echo "  Email: $EMAIL"
    echo ""
    echo "You can now log in at your OWA URL"
else
    echo ""
    echo "Installation failed. Please check the error messages above."
    exit 1
fi

