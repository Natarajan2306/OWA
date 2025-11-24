<?php
/**
 * Simple database connection test script
 * Run this to test your MySQL connection before using it in OWA
 */

echo "Testing MySQL Connection...\n\n";

// Configuration - UPDATE THESE VALUES
$host = 'localhost';
$port = 3306;
$dbname = 'owa_analytics';
$username = 'owa_user';
$password = 'owa_password_123'; // Change this to match the password you set

echo "Host: $host\n";
echo "Port: $port\n";
echo "Database: $dbname\n";
echo "Username: $username\n";
echo "Password: " . (empty($password) ? '(empty)' : '***') . "\n\n";

// Test connection
mysqli_report(MYSQLI_REPORT_OFF);
$connection = @mysqli_connect($host, $username, $password, '', $port);

if (!$connection) {
    $error = mysqli_connect_error();
    echo "❌ Connection FAILED!\n";
    echo "Error: $error\n\n";
    
    if (stripos($error, 'Access denied') !== false) {
        echo "SOLUTION: The password is incorrect or the user doesn't have permission.\n";
        echo "\nTry one of these:\n";
        echo "1. Use sudo: sudo mysql -u root\n";
        echo "2. Create a new user (run this in MySQL):\n";
        echo "   CREATE DATABASE IF NOT EXISTS $dbname;\n";
        echo "   CREATE USER 'owa_user'@'localhost' IDENTIFIED BY 'your_password';\n";
        echo "   GRANT ALL PRIVILEGES ON $dbname.* TO 'owa_user'@'localhost';\n";
        echo "   FLUSH PRIVILEGES;\n";
    } elseif (stripos($error, 'Unknown database') !== false) {
        echo "SOLUTION: The database doesn't exist. Create it first:\n";
        echo "   CREATE DATABASE $dbname;\n";
    }
    exit(1);
}

echo "✓ Connection successful!\n\n";

// Test database selection
if (!mysqli_select_db($connection, $dbname)) {
    echo "⚠ Database '$dbname' does not exist.\n";
    echo "Creating database...\n";
    
    if (mysqli_query($connection, "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")) {
        echo "✓ Database created successfully!\n";
        mysqli_select_db($connection, $dbname);
    } else {
        echo "❌ Failed to create database: " . mysqli_error($connection) . "\n";
        mysqli_close($connection);
        exit(1);
    }
} else {
    echo "✓ Database '$dbname' exists and is accessible.\n";
}

mysqli_close($connection);

echo "\n✅ All tests passed! You can use these credentials in OWA:\n";
echo "   Host: $host\n";
echo "   Port: $port\n";
echo "   Database: $dbname\n";
echo "   Username: $username\n";
echo "   Password: " . (empty($password) ? '(empty)' : '***') . "\n";

