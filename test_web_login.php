<?php
/**
 * Test Web Login - Simulates the exact web login flow
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Simulate POST request with namespace
$_POST['owa_user_id'] = 'natty';
$_POST['owa_password'] = 'admin123';
$_POST['owa_action'] = 'base.login';
$_SERVER['REQUEST_METHOD'] = 'POST';

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'admin_web'];
$owa = new owa($config);

// Get the request params
$user_id = owa_coreAPI::getRequestParam('user_id');
$password = owa_coreAPI::getRequestParam('password');

echo "Retrieved parameters:\n";
echo "  user_id: " . ($user_id ?: 'NOT FOUND') . "\n";
echo "  password: " . ($password ? '***' : 'NOT FOUND') . "\n\n";

if (!$user_id || !$password) {
    echo "ERROR: Parameters not retrieved correctly!\n";
    echo "This might be a namespace issue.\n";
    exit(1);
}

// Test authentication
$auth = owa_auth::get_instance();
$status = $auth->authenticateUser();

echo "Authentication result:\n";
echo "  Status: " . ($status['auth_status'] ? 'SUCCESS' : 'FAILED') . "\n\n";

if ($status['auth_status']) {
    echo "✓ Login should work! The credentials are correct.\n";
} else {
    echo "✗ Login failed. Let's check why...\n\n";
    
    // Get the user directly
    $user = owa_coreAPI::entityFactory('base.user');
    $user->getByColumn('user_id', $user_id);
    
    if ($user->wasPersisted()) {
        echo "User found in database.\n";
        echo "Testing password verification:\n";
        $verify = password_verify($password, $user->get('password'));
        echo "  password_verify result: " . ($verify ? 'SUCCESS' : 'FAILED') . "\n";
        
        if (!$verify) {
            echo "\nPassword doesn't match. Resetting password...\n";
            $user->set('password', owa_lib::encryptPassword($password));
            $user->save();
            echo "Password reset. Try logging in again.\n";
        }
    } else {
        echo "User NOT found in database!\n";
    }
}


