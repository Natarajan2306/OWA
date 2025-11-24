<?php
// Minimal install.php to test if basic PHP is working
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

echo "PHP is working!<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";
echo "Current directory: " . __DIR__ . "<br>";

// Check if owa_env.php exists
if (file_exists(__DIR__ . '/owa_env.php')) {
    echo "owa_env.php exists<br>";
    try {
        require_once(__DIR__ . '/owa_env.php');
        echo "owa_env.php loaded successfully<br>";
        echo "OWA_DIR: " . (defined('OWA_DIR') ? OWA_DIR : 'NOT DEFINED') . "<br>";
    } catch (Exception $e) {
        echo "Error loading owa_env.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "owa_env.php NOT FOUND<br>";
}

// Check if owa.php exists
if (file_exists(__DIR__ . '/owa.php')) {
    echo "owa.php exists<br>";
    try {
        require_once(__DIR__ . '/owa.php');
        echo "owa.php loaded successfully<br>";
    } catch (Exception $e) {
        echo "Error loading owa.php: " . $e->getMessage() . "<br>";
    }
} else {
    echo "owa.php NOT FOUND<br>";
}

// Try to create OWA instance
if (class_exists('owa')) {
    echo "Class 'owa' exists<br>";
    try {
        define('OWA_CACHE_OBJECTS', false);
        define('OWA_INSTALLING', true);
        $config = ['instance_role' => 'installer'];
        $owa = new owa($config);
        echo "OWA instance created successfully<br>";
    } catch (Exception $e) {
        echo "Error creating OWA instance: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    } catch (Error $e) {
        echo "Fatal error creating OWA instance: " . $e->getMessage() . "<br>";
        echo "Stack trace: " . $e->getTraceAsString() . "<br>";
    }
} else {
    echo "Class 'owa' does NOT exist<br>";
}
?>

