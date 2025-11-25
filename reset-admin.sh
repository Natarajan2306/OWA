#!/bin/bash
# Helper script to reset OWA admin user via Docker
# Usage: ./reset-admin.sh [user_id] [password] [email]
# Example: ./reset-admin.sh admin mynewpassword123 admin@example.com
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
else
    # Try to get from environment or use defaults
    USER_ID=${OWA_ADMIN_USER:-admin}
    PASSWORD=${OWA_ADMIN_PASSWORD:-$(openssl rand -base64 16 | tr -d "=+/" | cut -c1-16)}
    EMAIL=${OWA_ADMIN_EMAIL:-admin@example.com}
    
    if [ $# -eq 0 ]; then
        echo "No arguments provided. Using:"
        echo "  Username: $USER_ID"
        echo "  Password: $PASSWORD"
        echo "  Email: $EMAIL"
        echo ""
    fi
fi

echo "Resetting admin user..."
echo "  Username: $USER_ID"
echo "  Email: $EMAIL"
echo "Running in container: $CONTAINER_NAME"
echo ""

# Execute the reset admin user script in the container
docker exec -it $CONTAINER_NAME php /var/www/html/reset_admin_user.php "$USER_ID" "$PASSWORD" "$EMAIL"

if [ $? -eq 0 ]; then
    echo ""
    echo "Admin user reset completed successfully!"
    echo ""
    echo "Login credentials:"
    echo "  Username: $USER_ID"
    echo "  Password: $PASSWORD"
    echo "  Email: $EMAIL"
else
    echo ""
    echo "Admin user reset failed. Please check the error messages above."
    exit 1
fi

