<?php
// Set up error handling FIRST, before anything else
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/apache2/php_errors.log');

// Register error handler to catch fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Only show error page if headers haven't been sent
        if (!headers_sent()) {
            while (ob_get_level()) {
                ob_end_clean();
            }
            http_response_code(500);
            header('Content-Type: text/html; charset=utf-8');
            echo '<!DOCTYPE html><html><head><title>Fatal Error</title></head><body>';
            echo '<h1>Fatal Error</h1>';
            echo '<p><strong>Message:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . ':' . $error['line'] . '</p>';
            echo '<p><strong>Type:</strong> ' . $error['type'] . '</p>';
            echo '</body></html>';
        }
    }
});

// Use output buffering to catch any warnings that might be output
ob_start();

//
// Open Web Analytics - An Open Source Web Analytics Framework
//
// Copyright 2006 Peter Adams. All rights reserved.
//
// Licensed under GPL v2.0 http://www.gnu.org/copyleft/gpl.html
//
// Unless required by applicable law or agreed to in writing, software
// distributed under the License is distributed on an "AS IS" BASIS,
// WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
// See the License for the specific language governing permissions and
// limitations under the License.
//
// $Id$
//

try {
    include_once('owa_env.php');
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error Loading OWA Environment</title></head><body>';
    echo '<h1>Error Loading OWA Environment</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '</body></html>';
    exit;
}

try {
    require_once(OWA_BASE_DIR.'/owa.php');
} catch (Throwable $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><title>Error Loading OWA</title></head><body>';
    echo '<h1>Error Loading OWA</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '</body></html>';
    exit;
}

/**
 * Install Page Wrapper Script
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

// Initialize owa
//define('OWA_ERROR_HANDLER', 'development');
define('OWA_CACHE_OBJECTS', false);
define('OWA_INSTALLING', true);

$config = [
    'instance_role' => 'installer'
];

try {
    $owa = new owa( $config );
    
    // If installation is complete, redirect to index.php
    if ($owa->isOwaInstalled()) {
        $public_url = owa_coreAPI::getSetting('base', 'public_url');
        // Only redirect if not doing installation actions
        $do = owa_coreAPI::getRequestParam('do');
        if (empty($do) || (strpos($do, 'install') === false && strpos($do, 'login') === false && strpos($do, 'passwordReset') === false)) {
            ob_end_clean();
            owa_lib::redirectBrowser($public_url . 'index.php');
            exit;
        }
    }

    if ( $owa->isEndpointEnabled( basename( __FILE__ ) ) ) {
        // Clear output buffer before outputting content
        ob_end_clean();

        // need third param here so that seting is not persisted.
        $owa->setSetting('base','main_url', 'install.php');
        // run controller, echo page content
        $do = owa_coreAPI::getRequestParam('do');
        $params = array();
        if (empty($do)) {
            $params['do'] = 'base.installStart';
        }

        // run controller or view and echo page content
        echo $owa->handleRequest($params);

    } else {
        // Clear output buffer
        ob_end_clean();
        // unload owa
        $owa->restInPeace();
    }
} catch (Throwable $e) {
    // Clear output buffer
    $output = ob_get_clean();
    
    // Log the error
    error_log(sprintf('OWA Installation Error: %s in %s:%d', $e->getMessage(), $e->getFile(), $e->getLine()));
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    // Display a friendly error page
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Installation Error - Open Web Analytics</title>
        <style>
            body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
            .error { background: #fee; border: 1px solid #fcc; padding: 20px; border-radius: 5px; }
            h1 { color: #c00; }
            pre { background: #f5f5f5; padding: 10px; overflow-x: auto; font-size: 12px; }
            .info { background: #eef; border: 1px solid #ccf; padding: 15px; margin-top: 20px; border-radius: 5px; }
        </style>
    </head>
    <body>
        <div class="error">
            <h1>Installation Error</h1>
            <p>An error occurred while initializing Open Web Analytics:</p>
            <pre><?php echo htmlspecialchars($e->getMessage()); ?></pre>
            <p><strong>File:</strong> <?php echo htmlspecialchars($e->getFile()); ?>:<?php echo $e->getLine(); ?></p>
            <?php if (!empty($output)): ?>
            <div class="info">
                <strong>Output before error:</strong>
                <pre><?php echo htmlspecialchars($output); ?></pre>
            </div>
            <?php endif; ?>
            <div class="info">
                <p><strong>Stack Trace:</strong></p>
                <pre><?php echo htmlspecialchars($e->getTraceAsString()); ?></pre>
            </div>
            <p>Please check your server logs for more details.</p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

?>