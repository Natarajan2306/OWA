#!/bin/bash
set -e

# Run deployment initialization
/usr/local/bin/deploy-init.sh

# Start Apache
exec apache2-foreground

