<?php
/**
 * Update OWA Config File Script
 * 
 * This script updates the owa-config.php file with environment variables.
 * Usage: php update_config.php
 */

$config_file = '/var/www/html/owa-config.php';
$config_dist = '/var/www/html/owa-config-dist.php';

// Read environment variables
$db_type = getenv('OWA_DB_TYPE') ?: 'mysql';
$db_host = getenv('OWA_DB_HOST') ?: '';
$db_name = getenv('OWA_DB_NAME') ?: '';
$db_user = getenv('OWA_DB_USER') ?: '';
$db_password = getenv('OWA_DB_PASSWORD') ?: '';
$db_port = getenv('OWA_DB_PORT') ?: '3306';
$public_url = getenv('OWA_PUBLIC_URL') ?: '';

// If config file doesn't exist, create it from template
if (!file_exists($config_file) && file_exists($config_dist)) {
    if (!copy($config_dist, $config_file)) {
        echo "Error: Could not create config file from template.\n";
        exit(1);
    }
    echo "Config file created from template.\n";
}

if (!file_exists($config_file)) {
    echo "Error: Config file does not exist and template not found.\n";
    exit(1);
}

// Read the config file
$config_content = file_get_contents($config_file);
if ($config_content === false) {
    echo "Error: Could not read config file.\n";
    exit(1);
}

// Update database configuration
if ($db_type) {
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_TYPE['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_TYPE', '$db_type');",
        $config_content
    );
}

if ($db_host) {
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_HOST['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_HOST', '$db_host');",
        $config_content
    );
}

if ($db_name) {
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_NAME['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_NAME', '$db_name');",
        $config_content
    );
}

if ($db_user) {
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_USER['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_USER', '$db_user');",
        $config_content
    );
}

if ($db_password !== '') {
    // Escape single quotes in password
    $escaped_password = str_replace("'", "\\'", $db_password);
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_PASSWORD['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_PASSWORD', '$escaped_password');",
        $config_content
    );
}

if ($db_port) {
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_DB_PORT['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_DB_PORT', '$db_port');",
        $config_content
    );
}

// Update public URL
if ($public_url !== '') {
    $escaped_url = str_replace("'", "\\'", $public_url);
    $config_content = preg_replace(
        "/define\s*\(\s*['\"]OWA_PUBLIC_URL['\"],\s*['\"][^'\"]*['\"]\s*\);/",
        "define('OWA_PUBLIC_URL', '$escaped_url');",
        $config_content
    );
}

// Write the updated config file
if (file_put_contents($config_file, $config_content) === false) {
    echo "Error: Could not write config file.\n";
    exit(1);
}

// Set proper permissions
chmod($config_file, 0644);
if (function_exists('posix_getuid') && posix_getuid() === 0) {
    // If running as root, change ownership
    chown($config_file, 'www-data');
    $group = posix_getgrnam('www-data');
    if ($group) {
        chgrp($config_file, 'www-data');
    }
}

echo "Config file updated successfully.\n";
echo "Database Type: $db_type\n";
echo "Database Host: $db_host\n";
echo "Database Name: $db_name\n";
echo "Database User: $db_user\n";
echo "Database Port: $db_port\n";
if ($public_url) {
    echo "Public URL: $public_url\n";
}

