<?php
/**
 * Create User Script for OWA
 * 
 * This script allows you to create a new OWA user.
 * Usage: php create_user.php <user_id> <password> <email> [role] [real_name]
 * 
 * Example: php create_user.php admin mypassword123 admin@example.com admin "Admin User"
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 4) {
    echo "Usage: php create_user.php <user_id> <password> <email> [role] [real_name]\n";
    echo "\n";
    echo "Arguments:\n";
    echo "  user_id    - Username for login (required)\n";
    echo "  password   - Password for the user (required)\n";
    echo "  email      - Email address (required)\n";
    echo "  role       - User role (optional, default: 'viewer')\n";
    echo "  real_name  - Full name (optional, default: user_id)\n";
    echo "\n";
    echo "Examples:\n";
    echo "  php create_user.php admin mypassword123 admin@example.com admin \"Admin User\"\n";
    echo "  php create_user.php john password123 john@example.com\n";
    exit(1);
}

$user_id = $argv[1];
$password = $argv[2];
$email = $argv[3];
$role = isset($argv[4]) ? $argv[4] : 'viewer';
$real_name = isset($argv[5]) ? $argv[5] : $user_id;

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
    echo "Database: " . OWA_DB_NAME . "\n";
    echo "Host: " . OWA_DB_HOST . "\n";
    echo "User: " . OWA_DB_USER . "\n";
    exit(1);
}

// Check if user already exists
$user = owa_coreAPI::entityFactory('base.user');
$user->getByColumn('user_id', $user_id);

if ($user->wasPersisted()) {
    echo "Error: User '$user_id' already exists in the database.\n";
    echo "To reset the password, use: php reset_password.php $user_id <new_password>\n";
    exit(1);
}

// Check if email already exists
$user->getByColumn('email_address', $email);
if ($user->wasPersisted()) {
    echo "Error: Email '$email' is already registered to another user.\n";
    exit(1);
}

// Create the new user
try {
    $user = owa_coreAPI::entityFactory('base.user');
    $ret = $user->createNewUser($user_id, $role, $password, $email, $real_name);
    
    if ($ret) {
        echo "Success! User created successfully.\n";
        echo "\n";
        echo "Login credentials:\n";
        echo "  Username: $user_id\n";
        echo "  Password: $password\n";
        echo "  Email: $email\n";
        echo "  Role: $role\n";
        echo "  Real Name: $real_name\n";
        echo "\n";
        echo "You can now log in at: http://localhost:8080/\n";
    } else {
        echo "Error: Failed to create user. Please check the database connection and try again.\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "\n";
    echo "This might mean the database tables don't exist yet.\n";
    echo "Please run the installation first at: http://localhost:8080/install.php\n";
    exit(1);
}

