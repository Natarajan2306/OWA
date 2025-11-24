<?php
/**
 * Test Authentication Script
 * 
 * This script tests if authentication is working properly.
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

// Test authentication
$auth = owa_auth::get_instance();
$cu = owa_coreAPI::getCurrentUser();

echo "Testing Authentication...\n\n";

// Check if user is authenticated
echo "Is Authenticated: " . ($cu->isAuthenticated() ? "YES" : "NO") . "\n";

// Check user data
if ($cu->isAuthenticated()) {
    echo "User ID: " . $cu->getUserData('user_id') . "\n";
    echo "Role: " . $cu->getRole() . "\n";
    echo "Email: " . $cu->getUserData('email_address') . "\n";
    
    // Check capabilities
    $caps = $cu->getCapabilities($cu->getRole());
    echo "Capabilities: " . implode(', ', $caps) . "\n";
    
    // Check specific capability
    echo "Has 'view_site_list' capability: " . ($cu->isCapable('view_site_list') ? "YES" : "NO") . "\n";
} else {
    echo "User is not authenticated.\n";
    echo "This is expected in CLI mode - authentication requires cookies/sessions.\n";
}

echo "\n";
echo "To test web authentication, try logging in at: http://localhost:8080/\n";

