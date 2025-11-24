<?php
// Minimal check script to verify PHP is working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>PHP Check</title>
</head>
<body>
    <h1>PHP is Working!</h1>
    <p>PHP Version: <?php echo PHP_VERSION; ?></p>
    <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></p>
    <p>Document Root: <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></p>
    <p>Current File: <?php echo __FILE__; ?></p>
    
    <h2>File Checks</h2>
    <ul>
        <li>owa_env.php: <?php echo file_exists(__DIR__ . '/owa_env.php') ? 'EXISTS' : 'NOT FOUND'; ?></li>
        <li>owa.php: <?php echo file_exists(__DIR__ . '/owa.php') ? 'EXISTS' : 'NOT FOUND'; ?></li>
        <li>owa-config.php: <?php echo file_exists(__DIR__ . '/owa-config.php') ? 'EXISTS' : 'NOT FOUND'; ?></li>
        <li>owa-config-dist.php: <?php echo file_exists(__DIR__ . '/owa-config-dist.php') ? 'EXISTS' : 'NOT FOUND'; ?></li>
    </ul>
    
    <h2>Directory Checks</h2>
    <ul>
        <li>owa-data: <?php echo is_dir(__DIR__ . '/owa-data') ? 'EXISTS' : 'NOT FOUND'; ?></li>
        <li>owa-data writable: <?php echo is_writable(__DIR__ . '/owa-data') ? 'YES' : 'NO'; ?></li>
    </ul>
    
    <h2>Try Loading OWA</h2>
    <?php
    try {
        if (file_exists(__DIR__ . '/owa_env.php')) {
            require_once(__DIR__ . '/owa_env.php');
            echo '<p style="color: green;">✓ owa_env.php loaded</p>';
            echo '<p>OWA_DIR: ' . (defined('OWA_DIR') ? OWA_DIR : 'NOT DEFINED') . '</p>';
        } else {
            echo '<p style="color: red;">✗ owa_env.php NOT FOUND</p>';
        }
    } catch (Exception $e) {
        echo '<p style="color: red;">Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
    }
    ?>
</body>
</html>

