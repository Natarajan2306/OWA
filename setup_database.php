<?php
/**
 * Database Setup Script for Open Web Analytics
 * This script will create the database and user for OWA
 * Run: php setup_database.php
 */

echo "==========================================\n";
echo "Open Web Analytics Database Setup\n";
echo "==========================================\n\n";

$db_name = 'owa_analytics';
$db_user = 'owa_user';
$db_password = 'owa_password_123';

echo "This script will create:\n";
echo "  Database: $db_name\n";
echo "  User: $db_user\n";
echo "  Password: $db_password\n\n";

// Try to connect as root using different methods
$connection = null;
$methods = [
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'sudo' => false],
    ['host' => 'localhost', 'user' => 'root', 'password' => '', 'sudo' => true],
];

echo "Attempting to connect to MySQL...\n";

// First, try without sudo (suppress errors)
mysqli_report(MYSQLI_REPORT_OFF);
$connection = @mysqli_connect('localhost', 'root', '', '', 3306);

if (!$connection || mysqli_connect_error()) {
    echo "❌ Direct connection failed. Trying with system command...\n";
    
    // Use system command to create database
    $sql = <<<SQL
CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_password';
GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'localhost';
FLUSH PRIVILEGES;
SQL;

    // Save SQL to temp file
    $sql_file = sys_get_temp_dir() . '/owa_setup_' . time() . '.sql';
    file_put_contents($sql_file, $sql);
    
    echo "\nPlease run this command in your terminal:\n";
    echo "sudo mysql -u root < $sql_file\n\n";
    echo "Or run these SQL commands manually:\n";
    echo "----------------------------------------\n";
    echo $sql;
    echo "\n----------------------------------------\n\n";
    
    // Try to execute via shell
    $output = [];
    $return_var = 0;
    exec("sudo mysql -u root 2>&1 < $sql_file", $output, $return_var);
    
    if ($return_var === 0) {
        echo "✅ Database created successfully via sudo!\n";
        unlink($sql_file);
    } else {
        echo "⚠ Could not execute automatically. Please run manually:\n";
        echo "   sudo mysql -u root\n";
        echo "   Then paste the SQL commands above.\n\n";
        echo "SQL file saved to: $sql_file\n";
    }
} else {
    echo "✅ Connected to MySQL!\n";
    
    // Create database
    if (mysqli_query($connection, "CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "✅ Database '$db_name' created!\n";
    } else {
        echo "⚠ Database creation: " . mysqli_error($connection) . "\n";
    }
    
    // Create user
    $create_user_sql = "CREATE USER IF NOT EXISTS '$db_user'@'localhost' IDENTIFIED BY '$db_password'";
    if (mysqli_query($connection, $create_user_sql)) {
        echo "✅ User '$db_user' created!\n";
    } else {
        echo "⚠ User creation: " . mysqli_error($connection) . "\n";
    }
    
    // Grant privileges
    $grant_sql = "GRANT ALL PRIVILEGES ON $db_name.* TO '$db_user'@'localhost'";
    if (mysqli_query($connection, $grant_sql)) {
        echo "✅ Privileges granted!\n";
    } else {
        echo "⚠ Privilege grant: " . mysqli_error($connection) . "\n";
    }
    
    mysqli_query($connection, "FLUSH PRIVILEGES");
    mysqli_close($connection);
}

// Test the new connection
echo "\nTesting new connection...\n";
$test_conn = @mysqli_connect('localhost', $db_user, $db_password, $db_name, 3306);

if ($test_conn) {
    echo "✅ Connection test successful!\n\n";
    echo "==========================================\n";
    echo "✅ Setup Complete!\n";
    echo "==========================================\n\n";
    echo "Use these credentials in the OWA installation form:\n";
    echo "  Database Host: localhost\n";
    echo "  Database Port: 3306\n";
    echo "  Database Name: $db_name\n";
    echo "  Database User: $db_user\n";
    echo "  Database Password: $db_password\n\n";
    mysqli_close($test_conn);
} else {
    echo "❌ Connection test failed: " . mysqli_connect_error() . "\n";
    echo "\nPlease ensure the database and user were created successfully.\n";
    exit(1);
}

