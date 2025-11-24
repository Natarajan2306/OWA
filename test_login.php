<?php
/**
 * Test Login Script
 * 
 * This script tests if a username/password combination works.
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 3) {
    echo "Usage: php test_login.php <user_id> <password>\n";
    echo "Example: php test_login.php natty admin123\n";
    exit(1);
}

$user_id = $argv[1];
$password = $argv[2];

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

// Get the user
$user = owa_coreAPI::entityFactory('base.user');
$user->getByColumn('user_id', $user_id);

if (!$user->wasPersisted()) {
    echo "Error: User '$user_id' not found in the database.\n";
    exit(1);
}

echo "User found: " . $user->get('user_id') . "\n";
echo "Email: " . $user->get('email_address') . "\n";
echo "Password hash: " . substr($user->get('password'), 0, 30) . "...\n\n";

// Test password verification
$stored_hash = $user->get('password');
$verify_result = password_verify($password, $stored_hash);

echo "Testing password verification:\n";
echo "  Input password: $password\n";
echo "  Stored hash: " . substr($stored_hash, 0, 30) . "...\n";
echo "  Verification result: " . ($verify_result ? "SUCCESS" : "FAILED") . "\n\n";

if ($verify_result) {
    echo "✓ Password is correct! You should be able to log in.\n";
} else {
    echo "✗ Password verification failed. The password does not match.\n";
    echo "\nLet's reset the password again...\n";
    
    // Reset the password
    $user->set('password', owa_lib::encryptPassword($password));
    $status = $user->save();
    
    if ($status) {
        echo "Password has been reset. Try logging in again.\n";
    } else {
        echo "Failed to reset password.\n";
    }
}


