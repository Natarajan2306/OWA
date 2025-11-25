<?php
/**
 * Force Install OWA Script
 * 
 * This script forces installation even if install_complete flag is set.
 * Use this when the database tables are missing but the flag is set.
 * Usage: php force_install.php <user_id> <password> <email> [domain] [site_name]
 * 
 * Example: php force_install.php admin mypassword123 admin@example.com analytics.pdevsecops.com "Practical DevSecOps"
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 4) {
    echo "Usage: php force_install.php <user_id> <password> <email> [domain] [site_name]\n";
    echo "\n";
    echo "Arguments:\n";
    echo "  user_id   - Username for login (required)\n";
    echo "  password  - Password for the user (required)\n";
    echo "  email     - Email address (required)\n";
    echo "  domain    - Domain name for default site (optional, default: localhost)\n";
    echo "  site_name - Display name for the site (optional, default: domain)\n";
    echo "\n";
    echo "Example:\n";
    echo "  php force_install.php admin mypassword123 admin@example.com analytics.pdevsecops.com \"Practical DevSecOps\"\n";
    exit(1);
}

$user_id = $argv[1];
$password = $argv[2];
$email = $argv[3];
$domain = isset($argv[4]) ? $argv[4] : 'localhost';
$site_name = isset($argv[5]) ? $argv[5] : $domain;

// Initialize OWA
define('OWA_INSTALLING', true);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

echo "Force installing OWA (bypassing install check)...\n\n";

// Get install manager
$im = owa_coreAPI::supportClassFactory('base', 'installManager');

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

// Clear install_complete flag first
echo "Clearing install_complete flag...\n";
$c = owa_coreAPI::configSingleton();
$c->persistSetting('base', 'install_complete', false);
$c->save();

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
        echo "User '$user_id' already exists. Updating password and role...\n";
        $u->set('password', owa_lib::encryptPassword($password));
        $u->set('role', 'admin');
        $u->set('email_address', $email);
        $u->save();
        echo "User updated successfully.\n";
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
$im->createDefaultSite($domain, $site_name, 'Default Site', '');

// Mark installation as complete
echo "Marking installation as complete...\n";
$c->persistSetting('base', 'install_complete', true);
$save_status = $c->save();

if ($save_status !== true) {
    echo "Warning: Could not persist install_complete flag to database.\n";
} else {
    echo "Installation marked as complete: OK\n";
}

echo "\n";
echo "========================================\n";
echo "Installation Complete!\n";
echo "========================================\n";
echo "\n";
echo "Login credentials:\n";
echo "  Username: $user_id\n";
echo "  Password: $password\n";
echo "  Email: $email\n";
echo "  Domain: $domain\n";
echo "  Site Name: $site_name\n";
echo "\n";
echo "You can now log in at your OWA URL\n";
echo "\n";

