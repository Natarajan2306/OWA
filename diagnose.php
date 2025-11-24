<?php
// Diagnostic page to check what's working
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>OWA Diagnostic</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 1000px; margin: 20px auto; padding: 20px; }
        .success { background: #dfd; border: 1px solid #0f0; padding: 10px; margin: 10px 0; }
        .error { background: #fdd; border: 1px solid #f00; padding: 10px; margin: 10px 0; }
        .info { background: #ddf; border: 1px solid #00f; padding: 10px; margin: 10px 0; }
        pre { background: #f5f5f5; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Open Web Analytics Diagnostic</h1>
    
    <h2>PHP Information</h2>
    <div class="info">
        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
        <p><strong>Server API:</strong> <?php echo php_sapi_name(); ?></p>
        <p><strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Not set'; ?></p>
        <p><strong>Script Path:</strong> <?php echo __FILE__; ?></p>
    </div>
    
    <h2>File Checks</h2>
    <?php
    $files_to_check = [
        'owa_env.php',
        'owa.php',
        'owa-config.php',
        'owa-config-dist.php',
        'modules/base/classes/settings.php',
        'owa-data',
    ];
    
    foreach ($files_to_check as $file) {
        $path = __DIR__ . '/' . $file;
        if (file_exists($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $readable = is_readable($path) ? 'Yes' : 'No';
            $writable = is_writable($path) ? 'Yes' : 'No';
            echo "<div class='success'>";
            echo "<strong>$file</strong> - EXISTS<br>";
            echo "Permissions: $perms | Readable: $readable | Writable: $writable";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>$file</strong> - NOT FOUND";
            echo "</div>";
        }
    }
    ?>
    
    <h2>Directory Checks</h2>
    <?php
    $dirs_to_check = [
        'owa-data',
        'owa-data/caches',
        'owa-data/logs',
        'modules',
        'modules/base',
    ];
    
    foreach ($dirs_to_check as $dir) {
        $path = __DIR__ . '/' . $dir;
        if (is_dir($path)) {
            $perms = substr(sprintf('%o', fileperms($path)), -4);
            $readable = is_readable($path) ? 'Yes' : 'No';
            $writable = is_writable($path) ? 'Yes' : 'No';
            echo "<div class='success'>";
            echo "<strong>$dir</strong> - EXISTS<br>";
            echo "Permissions: $perms | Readable: $readable | Writable: $writable";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<strong>$dir</strong> - NOT FOUND";
            echo "</div>";
        }
    }
    ?>
    
    <h2>OWA Environment Test</h2>
    <?php
    try {
        if (file_exists(__DIR__ . '/owa_env.php')) {
            require_once(__DIR__ . '/owa_env.php');
            echo "<div class='success'>";
            echo "<strong>owa_env.php loaded successfully</strong><br>";
            echo "OWA_DIR: " . (defined('OWA_DIR') ? OWA_DIR : 'NOT DEFINED') . "<br>";
            echo "OWA_PATH: " . (defined('OWA_PATH') ? OWA_PATH : 'NOT DEFINED') . "<br>";
            echo "</div>";
        } else {
            echo "<div class='error'>owa_env.php not found</div>";
        }
    } catch (Throwable $e) {
        echo "<div class='error'>";
        echo "<strong>Error loading owa_env.php:</strong><br>";
        echo htmlspecialchars($e->getMessage()) . "<br>";
        echo "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine();
        echo "</div>";
    }
    ?>
    
    <h2>OWA Core Test</h2>
    <?php
    try {
        if (file_exists(__DIR__ . '/owa.php')) {
            require_once(__DIR__ . '/owa.php');
            echo "<div class='success'>";
            echo "<strong>owa.php loaded successfully</strong><br>";
            echo "Class 'owa' exists: " . (class_exists('owa') ? 'Yes' : 'No');
            echo "</div>";
        } else {
            echo "<div class='error'>owa.php not found</div>";
        }
    } catch (Throwable $e) {
        echo "<div class='error'>";
        echo "<strong>Error loading owa.php:</strong><br>";
        echo htmlspecialchars($e->getMessage()) . "<br>";
        echo "File: " . htmlspecialchars($e->getFile()) . ":" . $e->getLine();
        echo "</div>";
    }
    ?>
    
    <h2>Error Log Location</h2>
    <div class="info">
        <p><strong>PHP Error Log:</strong> <?php echo ini_get('error_log') ?: 'Not set'; ?></p>
        <p><strong>Display Errors:</strong> <?php echo ini_get('display_errors') ? 'On' : 'Off'; ?></p>
        <p><strong>Error Reporting:</strong> <?php echo error_reporting(); ?></p>
    </div>
</body>
</html>

