#!/bin/bash
# Helper script to reset OWA user password via Docker
# Usage: ./reset-password.sh <user_id> <new_password>
# Example: ./reset-password.sh admin mynewpassword123

if [ $# -lt 2 ]; then
    echo "Usage: $0 <user_id> <new_password>"
    echo ""
    echo "Example:"
    echo "  $0 admin mynewpassword123"
    echo ""
    echo "This script will reset the password for the specified user in the OWA Docker container."
    exit 1
fi

USER_ID=$1
NEW_PASSWORD=$2
CONTAINER_NAME="owa-web"

# Check if container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${CONTAINER_NAME}$"; then
    echo "Error: Container '${CONTAINER_NAME}' is not running."
    echo "Please start the container first with: docker-compose up -d"
    exit 1
fi

echo "Resetting password for user: $USER_ID"
echo "Running in container: $CONTAINER_NAME"
echo ""

# Execute the reset password script in the container
docker exec -it $CONTAINER_NAME php /var/www/html/reset_password.php "$USER_ID" "$NEW_PASSWORD"

if [ $? -eq 0 ]; then
    echo ""
    echo "Password reset completed successfully!"
else
    echo ""
    echo "Password reset failed. Please check the error messages above."
    exit 1
fi

