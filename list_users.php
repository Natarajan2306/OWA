<?php
/**
 * List Users Script for OWA
 * 
 * This script lists all users in the OWA system.
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

// Get all users
$db = owa_coreAPI::dbSingleton();
$db->selectFrom('owa_user');
$db->selectColumn("*");
$users = $db->getAllRows();

if (empty($users)) {
    echo "No users found in the database.\n";
    exit(0);
}

echo "Users in OWA:\n";
echo str_repeat("=", 80) . "\n";
printf("%-20s %-30s %-15s %-30s\n", "User ID", "Real Name", "Role", "Email Address");
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    printf("%-20s %-30s %-15s %-30s\n", 
        $user['user_id'] ?? 'N/A',
        $user['real_name'] ?? 'N/A',
        $user['role'] ?? 'N/A',
        $user['email_address'] ?? 'N/A'
    );
}

echo str_repeat("=", 80) . "\n";
echo "\nTo reset a password, run:\n";
echo "  php reset_password.php <user_id> <new_password>\n";
echo "\nExample:\n";
if (!empty($users[0]['user_id'])) {
    echo "  php reset_password.php {$users[0]['user_id']} mynewpassword123\n";
}


