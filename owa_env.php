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

// Set up error handler VERY EARLY to suppress config file warnings
// This must happen before any classes are loaded
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    // Suppress warnings about missing config file (normal during installation)
    if (($errno === E_WARNING || $errno === E_NOTICE) && 
        (strpos($errstr, 'owa-config.php') !== false || 
         strpos($errstr, 'Failed to open stream') !== false ||
         strpos($errstr, 'Failed opening') !== false)) {
        return true; // Suppress this error completely
    }
    return false; // Let other errors through
}, E_WARNING | E_NOTICE);

/**
 * Environment Configuration
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */
  
if (!defined('OWA_PATH')) {
    define('OWA_PATH', dirname(__FILE__));
}
define('OWA_DIR', OWA_PATH . '/');
define('OWA_DATA_DIR', OWA_DIR . 'owa-data/');
define('OWA_MODULES_DIR', OWA_DIR.'modules/');
define('OWA_BASE_DIR', OWA_PATH); // depricated
define('OWA_BASE_CLASSES_DIR', OWA_DIR); //depricated
define('OWA_BASE_MODULE_DIR', OWA_DIR.'modules/base/');
define('OWA_BASE_CLASS_DIR', OWA_BASE_MODULE_DIR.'classes/');
define('OWA_INCLUDE_DIR', OWA_DIR.'includes/');
define('OWA_PLUGIN_DIR', OWA_DIR.'plugins/');
define('OWA_CONF_DIR', OWA_DIR.'conf/');
define('OWA_THEMES_DIR', OWA_DIR.'themes/');
define('OWA_VERSION', 'master');
define('OWA_VENDOR_DIR', OWA_DIR.'vendor/');

if ( file_exists( OWA_VENDOR_DIR . 'autoload.php' ) ) {
	
	require_once ( OWA_VENDOR_DIR . 'autoload.php' );
}

// Suppress deprecation warnings from vendor libraries (PHP 8.1+ compatibility)
// These are in third-party code and will be fixed in future library updates
if (PHP_VERSION_ID >= 80100) {
    // Temporarily suppress E_DEPRECATED to avoid warnings from vendor code
    // OWA's error handler will be set up later and will handle other errors
    $current_error_reporting = error_reporting();
    error_reporting($current_error_reporting & ~E_DEPRECATED);
}

?>
