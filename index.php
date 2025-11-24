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

// Suppress warnings when config file doesn't exist (will redirect to install)
// Use output buffering to catch any warnings that might be output
ob_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

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

require_once('owa_env.php');

// Check if config file exists before loading OWA
if (!file_exists(OWA_DIR.'owa-config.php')) {
    // Config file doesn't exist, redirect to install
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $public_url = $protocol . $host . '/';
    header('Location: ' . $public_url . 'install.php', true, 302);
    exit;
}

require_once(OWA_DIR.'owa.php');

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

$owa = new owa( $config );

if (!$owa->isOwaInstalled()) {
    // Clear any output buffer before redirect
    ob_end_clean();
    // redirect to install
    $public_url = owa_coreAPI::getSetting('base','public_url');
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
ob_end_clean();

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