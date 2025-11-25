<?php
/**
 * Reset Admin User Script for OWA
 * 
 * This script deletes existing admin users and creates a new one.
 * Used during deployment to ensure fresh admin credentials.
 * 
 * Usage: php reset_admin_user.php <user_id> <password> <email>
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 4) {
    echo "Usage: php reset_admin_user.php <user_id> <password> <email>\n";
    echo "\n";
    echo "Arguments:\n";
    echo "  user_id  - Username for login (required)\n";
    echo "  password - Password for the user (required)\n";
    echo "  email    - Email address (required)\n";
    echo "\n";
    echo "Example:\n";
    echo "  php reset_admin_user.php admin mypassword123 admin@example.com\n";
    exit(1);
}

$user_id = $argv[1];
$password = $argv[2];
$email = $argv[3];

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

// Check if database is connected
$db = owa_coreAPI::dbSingleton();
if (!$db) {
    echo "Error: Could not get database instance.\n";
    echo "Please make sure the database is configured in owa-config.php\n";
    exit(1);
}

$db->connect();
if (!$db->connection_status) {
    echo "Error: Could not connect to database.\n";
    echo "Please check your database configuration in owa-config.php\n";
    exit(1);
}

echo "Resetting admin user...\n";

// Get all admin users
$db->selectFrom('owa_user');
$db->selectColumn("*");
$db->where('role', 'admin');
$admin_users = $db->getAllRows();

if (!empty($admin_users)) {
    echo "Found " . count($admin_users) . " existing admin user(s). Deleting...\n";
    
    // Delete all existing admin users
    foreach ($admin_users as $admin_user) {
        $existing_user_id = $admin_user['user_id'] ?? 'unknown';
        echo "  Deleting admin user: $existing_user_id\n";
        
        $u = owa_coreAPI::entityFactory('base.user');
        $u->getByColumn('user_id', $existing_user_id);
        if ($u->wasPersisted()) {
            $u->delete();
        }
    }
    echo "Existing admin users deleted.\n";
} else {
    echo "No existing admin users found.\n";
}

// Check if the new user_id already exists (non-admin)
$u = owa_coreAPI::entityFactory('base.user');
$u->getByColumn('user_id', $user_id);

if ($u->wasPersisted()) {
    echo "Warning: User '$user_id' already exists (non-admin). Deleting...\n";
    $u->delete();
}

// Create the new admin user
try {
    $u = owa_coreAPI::entityFactory('base.user');
    $ret = $u->createNewUser($user_id, 'admin', $password, $email, 'default admin');
    
    if ($ret) {
        echo "Success! New admin user created successfully.\n";
        echo "\n";
        echo "Login credentials:\n";
        echo "  Username: $user_id\n";
        echo "  Password: $password\n";
        echo "  Email: $email\n";
        echo "  Role: admin\n";
        echo "\n";
    } else {
        echo "Error: Failed to create admin user. Please check the database connection and try again.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "This might mean the database tables don't exist yet.\n";
    echo "Please run the installation first.\n";
    exit(1);
}

