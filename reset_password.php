<?php
/**
 * Password Reset Script for OWA
 * 
 * This script allows you to reset the password for an OWA user.
 * Usage: php reset_password.php <user_id> <new_password>
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Get command line arguments
if ($argc < 3) {
    echo "Usage: php reset_password.php <user_id> <new_password>\n";
    echo "Example: php reset_password.php natty mynewpassword123\n";
    exit(1);
}

$user_id = $argv[1];
$new_password = $argv[2];

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

// Set the new password using the set method
$user->set('password', owa_lib::encryptPassword($new_password));
$status = $user->save();

if ($status) {
    echo "Success! Password for user '$user_id' has been reset.\n";
    echo "You can now log in with:\n";
    echo "  Username: $user_id\n";
    echo "  Password: $new_password\n";
} else {
    echo "Error: Failed to update password. Please check the database connection.\n";
    exit(1);
}

