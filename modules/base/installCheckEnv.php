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

require_once(OWA_BASE_DIR.'/owa_view.php');
require_once(OWA_BASE_CLASS_DIR.'installController.php');

/**
 * Server Environment Check Controller
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

class owa_installCheckEnvController extends owa_installController {

    function action() {

        $errors = array();
        $bad_environment = false;
        $config_file_present = false;

        // check PHP version
        $version = explode( '.', phpversion() );

        if ( $version[0] < 5 && $version[1] < 2 ) {
            $errors['php_version']['name'] = 'PHP Version';
            $errors['php_version']['value'] = phpversion();
            $errors['php_version']['msg'] = $this->getMsgAsString(3301);
            $bad_environment = true;
        }

        // Check permissions on log directory
        if ( ! is_writable( OWA_DATA_DIR . 'logs/' ) ) {

            $errors['owa_logdir_permissions']['name'] = 'Log Directory Permissions';
            $errors['owa_logdir_permissions']['value'] = 'Not writable';
            $errors['owa_logdir_permissions']['msg'] = 'Check filesystem permissions for '. OWA_DATA_DIR . 'logs/ ' . ' to ensure it is writable.';
            $bad_environment = true;
        }

        // Check permissions on caches directory
        if ( ! is_writable( OWA_DATA_DIR . 'caches/' ) ) {

            $errors['owa_caches_permissions']['name'] = 'Caches Directory Permissions';
            $errors['owa_caches_permissions']['value'] = 'Not writable';
            $errors['owa_caches_permissions']['msg'] = 'Check filesystem permissions for '. OWA_DATA_DIR . 'caches/ ' . ' to ensure it is writable.';
            $bad_environment = true;
        }

        // check for magic_quotes
        if ( function_exists( 'get_magic_quotes_gpc' ) ) {

            $magic_quotes = get_magic_quotes_gpc();

            if ( $magic_quotes ) {

                $errors['magic_quotes_gpc']['name'] = 'magic_quotes_gpc';
                $errors['magic_quotes_gpc']['value'] = $magic_quotes;
                $errors['magic_quotes_gpc']['msg'] = "The magic_quotes_gpc PHP INI directive must be set to 'OFF' in order for OWA domstreams to operate correctly.";
                $bad_environment = true;

            }
        }
        
        // check to ensure tha the vendors dir exist
        if (! is_dir( OWA_VENDOR_DIR ) ) {
	        
	        $errors['vendors_dir'] = [
		        
		        'name'	=> 'Vendors Directory',
		        'value'	=> 'missing',
		        'msg'	=> "The vendors directory is missing. Please run 'composer install' from the top level OWA directory."
	        ];
	        
	        $bad_environment = true;
        }
        
        // check to ensure tha the vendors dir exist
        if ( ! is_dir( OWA_BASE_MODULE_DIR .'dist' ) ) {
	        
	        $errors['base_dist_dir'] = [
		        
		        'name'	=> 'dist Directory',
		        'value'	=> 'missing',
		        'msg'	=> "The base module dist directory is missing. Please run 'npm build' from the top level OWA directory."
	        ];
	        
	        $bad_environment = true;
        }


        // Check for config file and then test the db connection
        if ($this->c->isConfigFilePresent()) {
            $config_file_present = true;
            $conn = $this->checkDbConnection();
            if ($conn != true) {
                $errors['db']['name'] = 'Database Connection';
                $errors['db']['value'] = 'Connection failed';
                
                // Get detailed error message
                $db = owa_coreAPI::dbSingleton();
                $error_msg = 'Check the connection settings in your configuration file.';
                if ($db && method_exists($db, 'getLastError')) {
                    $error_detail = $db->getLastError();
                    if ($error_detail) {
                        $error_msg .= '<br><br><strong>Error Details:</strong> ' . htmlspecialchars($error_detail);
                        
                        // Provide helpful suggestions based on the error
                        if (stripos($error_detail, 'Access denied') !== false) {
                            $error_msg .= '<br><br><strong>Possible solutions:</strong><ul>';
                            $error_msg .= '<li>Check that your database username and password are correct</li>';
                            $error_msg .= '<li>Verify the database user has proper permissions</li>';
                            $error_msg .= '</ul>';
                        } elseif (stripos($error_detail, 'Unknown database') !== false) {
                            $error_msg .= '<br><br><strong>Solution:</strong> The database does not exist. Please create it first.';
                        } elseif (stripos($error_detail, 'getaddrinfo') !== false || stripos($error_detail, 'Name or service not known') !== false) {
                            $error_msg .= '<br><br><strong>Solution:</strong> Cannot resolve database hostname. In Coolify, make sure:';
                            $error_msg .= '<ul><li>The database service is linked to your application</li>';
                            $error_msg .= '<li>The OWA_DB_HOST environment variable is set to the correct database service name</li>';
                            $error_msg .= '<li>You can find the database service name in your Coolify database service settings</li></ul>';
                        }
                    }
                } elseif (!$db) {
                    $error_msg .= '<br><br><strong>Error:</strong> Database connection object could not be created.';
                    $error_msg .= '<br>This usually means database connection parameters are missing or invalid in the config file.';
                    $error_msg .= '<br>Please check that OWA_DB_HOST, OWA_DB_NAME, OWA_DB_USER, and OWA_DB_PASSWORD are set correctly.';
                }
                
                $errors['db']['msg'] = $error_msg;
                $bad_environment = true;
            }
        }

        // if the environment is good
        if ($bad_environment != true) {
            // and the config file is present
            if ($config_file_present === true) {
                //skip to defaults entry step
                $this->setRedirectAction('base.installDefaultsEntry');
                return;
            } else {
                // otherwise show config file entry form
                $this->setView('base.install');
                // Todo: prepopulate public URL.
                //$config = array('public_url', $url);
                //$this->set('config', $config);
                $this->setSubview('base.installConfigEntry');
                return;
            }
        // if the environment is bad, then show environment error details.
        } else {
            $this->set('errors', $errors);
            $this->setView('base.install');
            $this->setSubview('base.installCheckEnv');
        }
    }
}

/**
 * Installer Server Environment Setup Check View
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

class owa_installCheckEnvView extends owa_view {

    function render($data) {

        //page title
        $this->t->set('page_title', 'Server Environment Check');
        $this->body->set('errors', $this->get('errors'));
        // load body template
        $this->body->set_template('install_check_env.tpl');
        $this->setJs("owa", "base/dist/owa.reporting-combined-min.js");
    }
}

?>