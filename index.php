<?php

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

// Set up error handling FIRST, before anything else
error_reporting(E_ALL);

// Try to enable error display (may be overridden by php.ini in production)
@ini_set('display_errors', 1);
@ini_set('display_startup_errors', 1);
@ini_set('log_errors', 1);

// Try multiple error log locations
$error_logs = [
    '/var/log/apache2/php_errors.log',
    '/var/log/php_errors.log',
    __DIR__ . '/owa-data/logs/php_errors.log',
    sys_get_temp_dir() . '/php_errors.log'
];

foreach ($error_logs as $log_path) {
    $log_dir = dirname($log_path);
    if (is_dir($log_dir) && is_writable($log_dir)) {
        @ini_set('error_log', $log_path);
        break;
    }
}

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

// Prevent redirect loops - reject URLs that are too long or contain nested loginForm redirects
// This must run BEFORE any other code to catch the issue early
if (isset($_SERVER['REQUEST_URI'])) {
    $request_uri = $_SERVER['REQUEST_URI'];
    // Check for nested loginForm redirects in owa_go parameter (check first as it's more specific)
    if (preg_match('/owa_go=.*base\.loginForm.*owa_go=.*base\.loginForm/i', $request_uri) ||
        preg_match('/go=.*base\.loginForm.*go=.*base\.loginForm/i', $request_uri)) {
        // Redirect to clean loginForm URL
        header('Location: /index.php?owa_do=base.loginForm', true, 302);
        exit;
    }
    // Reject URLs longer than 2000 characters (Apache default is 8190, but we want to catch loops early)
    if (strlen($request_uri) > 2000) {
        // Redirect to clean loginForm URL
        header('Location: /index.php?owa_do=base.loginForm', true, 302);
        exit;
    }
    // Also check query string for nested redirects
    if (isset($_SERVER['QUERY_STRING'])) {
        $query_string = $_SERVER['QUERY_STRING'];
        if (preg_match('/owa_go=.*base\.loginForm.*owa_go=.*base\.loginForm/i', $query_string) ||
            preg_match('/go=.*base\.loginForm.*go=.*base\.loginForm/i', $query_string)) {
            header('Location: /index.php?owa_do=base.loginForm', true, 302);
            exit;
        }
    }
}

try {
    require_once('owa_env.php');
} catch (Throwable $e) {
    if (ob_get_level()) {
        ob_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><title>Error Loading OWA Environment</title></head><body>';
    echo '<h1>Error Loading OWA Environment</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}

// Check if config file exists before loading OWA
if (!file_exists(OWA_DIR.'owa-config.php')) {
    // Config file doesn't exist, redirect to install
    if (ob_get_level()) {
        ob_end_clean();
    }
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $public_url = $protocol . $host . '/';
    header('Location: ' . $public_url . 'install.php', true, 302);
    exit;
}

try {
    require_once(OWA_DIR.'owa.php');
} catch (Throwable $e) {
    if (ob_get_level()) {
        ob_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><title>Error Loading OWA</title></head><body>';
    echo '<h1>Error Loading OWA</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}

/**
 * Main Admin Page Wrapper Script
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

// Initialize owa admin

$config = [

    'instance_role' => 'admin_web'
];

try {
    $owa = new owa( $config );
} catch (Throwable $e) {
    if (ob_get_level()) {
        ob_clean();
    }
    if (!headers_sent()) {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
    }
    echo '<!DOCTYPE html><html><head><title>Error Initializing OWA</title></head><body>';
    echo '<h1>Error Initializing OWA</h1>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>File:</strong> ' . htmlspecialchars($e->getFile()) . ':' . $e->getLine() . '</p>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
    echo '</body></html>';
    exit;
}

if (!$owa->isOwaInstalled()) {
    // Clear any output buffer before redirect
    if (ob_get_level()) {
        ob_end_clean();
    }
    // redirect to install
    try {
        $public_url = owa_coreAPI::getSetting('base','public_url');
    } catch (Throwable $e) {
        $public_url = null;
    }
    if (!$public_url) {
        // Fallback if public_url is not set
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $public_url = $protocol . $host . '/';
    }
    owa_lib::redirectBrowser($public_url.'install.php');
    exit;
}

// Clear output buffer and continue
if (ob_get_level()) {
    ob_end_clean();
}

if ( $owa->isEndpointEnabled( basename( __FILE__ ) ) ) {
    
    $params = [];
    
    $do = owa_coreAPI::getRequestParam('do');
    
    if ( ! $do ) {
    
        $params['do'] = $owa->getSetting('base', 'start_page');
    }
    // run controller or view and echo page content
    echo $owa->handleRequest( $params );
    
} else {

    // unload owa
    $owa->restInPeace();
}

?>