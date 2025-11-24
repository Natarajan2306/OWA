<?php
/**
 * Install OWA and Create User Script
 * 
 * This script installs the OWA database schema and creates an admin user.
 * Usage: php install_and_create_user.php <user_id> <password> <email> [domain]
 * 
 * Example: php install_and_create_user.php admin mypassword123 admin@example.com localhost
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 4) {
    echo "Usage: php install_and_create_user.php <user_id> <password> <email> [domain]\n";
    echo "\n";
    echo "Arguments:\n";
    echo "  user_id  - Username for login (required)\n";
    echo "  password - Password for the user (required)\n";
    echo "  email    - Email address (required)\n";
    echo "  domain   - Domain name for default site (optional, default: localhost)\n";
    echo "\n";
    echo "Example:\n";
    echo "  php install_and_create_user.php admin mypassword123 admin@example.com localhost\n";
    exit(1);
}

$user_id = $argv[1];
$password = $argv[2];
$email = $argv[3];
$domain = isset($argv[4]) ? $argv[4] : 'localhost';

// Initialize OWA
define('OWA_INSTALLING', true);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

echo "Starting OWA installation...\n\n";

// Get install manager
$im = owa_coreAPI::supportClassFactory('base', 'installManager');

// Check if already installed
if ($im->isInstallComplete()) {
    echo "OWA is already installed.\n";
    echo "To create a new user, use: php create_user.php <user_id> <password> <email>\n";
    exit(0);
}

// Check database connection
$db = owa_coreAPI::dbSingleton();
if (!$db) {
    echo "Error: Could not get database instance.\n";
    exit(1);
}

$db->connect();
if (!$db->connection_status) {
    echo "Error: Could not connect to database.\n";
    echo "Please check your database configuration in owa-config.php\n";
    exit(1);
}

echo "Database connection: OK\n";

// Install schema
echo "Installing database schema...\n";
$status = $im->installSchema();

if ($status !== true) {
    echo "Error: Failed to install database schema.\n";
    exit(1);
}

echo "Database schema installed: OK\n";

// Create admin user
echo "Creating admin user...\n";
$created_password = $im->createAdminUser($user_id, $email, $password);

if ($created_password === false) {
    echo "Warning: Admin user may already exist or creation failed.\n";
    echo "Attempting to create user directly...\n";
    
    // Try to create user directly
    $u = owa_coreAPI::entityFactory('base.user');
    $u->getByColumn('user_id', $user_id);
    
    if ($u->wasPersisted()) {
        echo "User '$user_id' already exists. Updating password...\n";
        $u->set('password', owa_lib::encryptPassword($password));
        $u->save();
        echo "Password updated successfully.\n";
    } else {
        $ret = $u->createNewUser($user_id, 'admin', $password, $email, 'default admin');
        if ($ret) {
            echo "User created successfully.\n";
        } else {
            echo "Error: Failed to create user.\n";
            exit(1);
        }
    }
} else {
    echo "Admin user created: OK\n";
}

// Create default site
echo "Creating default site...\n";
$im->createDefaultSite($domain, $domain, 'Default Site', '');

// Mark installation as complete
$c = owa_coreAPI::configSingleton();
$c->persistSetting('base', 'install_complete', true);
$c->save();

echo "\n";
echo "========================================\n";
echo "Installation Complete!\n";
echo "========================================\n";
echo "\n";
echo "Login credentials:\n";
echo "  Username: $user_id\n";
echo "  Password: $password\n";
echo "  Email: $email\n";
echo "\n";
echo "You can now log in at: http://localhost:8080/\n";
echo "\n";

