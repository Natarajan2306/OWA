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

// Suppress warnings during installation when config file doesn't exist yet
// Use output buffering to catch any warnings that might be output
ob_start();
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

include_once('owa_env.php');
require_once(OWA_BASE_DIR.'/owa.php');

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

$owa = new owa( $config );

$owa = new owa( $config );

// If installation is complete, redirect to index.php
if ($owa->isOwaInstalled()) {
    $public_url = owa_coreAPI::getSetting('base', 'public_url');
    // Only redirect if not doing installation actions
    $do = owa_coreAPI::getRequestParam('do');
    if (empty($do) || (strpos($do, 'install') === false && strpos($do, 'login') === false && strpos($do, 'passwordReset') === false)) {
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

?>