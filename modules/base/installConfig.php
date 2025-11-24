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

require_once(OWA_BASE_CLASS_DIR.'installController.php');

/**
 * Install Configuration Controller
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

class owa_installConfigController extends owa_installController {

    function __construct($params) {
    
        parent::__construct($params);

        // require nonce
        $this->setNonceRequired();
    }

    public function validate()
    {
        //required params
        $this->addValidation('db_host', $this->getParam('db_host'), 'required', ['errorMsg' => 'Database host is required.']);
        $this->addValidation('db_name', $this->getParam('db_name'), 'required', ['errorMsg' => 'Database name is required.']);
        $this->addValidation('db_user', $this->getParam('db_user'), 'required', ['errorMsg' => 'Database user is required.']);
        $this->addValidation('db_password', $this->getParam('db_password'), 'required', ['errorMsg' => 'Database password is required.']);
        $this->addValidation('db_type', $this->getParam('db_type'), 'required', ['errorMsg' => 'Database type is required.']);

        // Config for the public_url validation
        $publicUrlConf = [
            'substring' => 'http',
            'match'     => '/',
            'length'    => -1,
            'position'  => -1,
            'operator'  => '=',
            'errorMsg'  => 'Your URL of OWA\'s base directory must end with a slash.'
        ];

        $this->addValidation('public_url', $this->getParam('public_url'), 'subStringMatch', $publicUrlConf);

        // Config for the domain validation
        $domainConf = [
            'substring' => 'http',
            'position'  => 0,
            'operator'  => '=',
            'errorMsg'  => 'Please add http:// or https:// to the beginning of your public url.'
        ];

        $this->addValidation('public_url', $this->getParam('public_url'), 'subStringPosition', $domainConf);
    }

    function action() {

        // define db connection constants using values submitted
        if ( ! defined( 'OWA_DB_TYPE' ) ) {
            define( 'OWA_DB_TYPE', $this->getParam( 'db_type' ) );
        }

        if ( ! defined( 'OWA_DB_HOST' ) ) {
            define('OWA_DB_HOST', $this->getParam( 'db_host' ) );
        }

        if ( ! defined( 'OWA_DB_PORT' ) ) {
            define('OWA_DB_PORT', $this->getParam( 'db_port' ) );
        }

        if ( ! defined( 'OWA_DB_NAME' ) ) {
            define('OWA_DB_NAME', $this->getParam( 'db_name' ) );
        }

        if ( ! defined( 'OWA_DB_USER' ) ) {
            define('OWA_DB_USER', $this->getParam( 'db_user' ) );
        }

        if ( ! defined( 'OWA_DB_PASSWORD' ) ) {
            define('OWA_DB_PASSWORD', $this->getParam( 'db_password' ) );
        }

        // Validate database parameters before setting them
        $db_host = trim(OWA_DB_HOST);
        $db_user = trim(OWA_DB_USER);
        $db_name = trim(OWA_DB_NAME);
        $db_port = trim(OWA_DB_PORT);
        
        // Check for invalid parameters (like "Warning" which indicates a PHP warning was captured)
        if (empty($db_host) || stripos($db_host, 'warning') !== false || 
            empty($db_user) || stripos($db_user, 'warning') !== false ||
            empty($db_name) || stripos($db_name, 'warning') !== false) {
            $this->set('error_msg', 'Invalid database connection parameters. Please check that all fields are filled correctly.');
            $this->set('config', $this->params);
            $this->setView('base.install');
            $this->setSubview('base.installConfigEntry');
            return;
        }
        
        owa_coreAPI::setSetting('base', 'db_type', OWA_DB_TYPE);
        owa_coreAPI::setSetting('base', 'db_host', $db_host);
        owa_coreAPI::setSetting('base', 'db_port', $db_port ?: '3306');
        owa_coreAPI::setSetting('base', 'db_name', $db_name);
        owa_coreAPI::setSetting('base', 'db_user', $db_user);
        owa_coreAPI::setSetting('base', 'db_password', OWA_DB_PASSWORD);

        // Check DB connection status
        $db = owa_coreAPI::dbSingleton();
        $db->connect();
        if ($db->connection_status != true) {
            // Get the actual error message from the database connection
            $error_detail = '';
            if (method_exists($db, 'getLastError')) {
                $error_detail = $db->getLastError();
            }
            $error_msg_array = $this->getMsg(3012);
            $error_msg = is_array($error_msg_array) ? $error_msg_array['message'] : (string)$error_msg_array;
            if ($error_detail) {
                $error_msg = $error_msg . '<br><strong>MySQL Error:</strong> ' . htmlspecialchars($error_detail);
                // Provide helpful suggestions based on the error
                if (stripos($error_detail, 'Access denied') !== false) {
                    $error_msg .= '<br><br><strong>Possible solutions:</strong><ul>';
                    $error_msg .= '<li>Check that your database username and password are correct</li>';
                    $error_msg .= '<li>If using root user, make sure you have the correct password</li>';
                    $error_msg .= '<li>Consider creating a dedicated MySQL user for OWA (see instructions below)</li>';
                    $error_msg .= '</ul>';
                } elseif (stripos($error_detail, 'Unknown database') !== false) {
                    $error_msg .= '<br><br><strong>Solution:</strong> The database does not exist. Please create it first using: <code>CREATE DATABASE ' . htmlspecialchars($db_name) . ';</code>';
                }
            }
            $this->set('error_msg', $error_msg);
            $this->set('config', $this->params);
            $this->setView('base.install');
            $this->setSubview('base.installConfigEntry');

        } else {
            //create config file
            $this->c->createConfigFile($this->params);
            $this->setRedirectAction('base.installDefaultsEntry');
        }
    }

    function errorAction() {
        
        $this->set('config', $this->params);
        $this->setView('base.install');
        $this->setSubview('base.installConfigEntry');
    }
}

?>