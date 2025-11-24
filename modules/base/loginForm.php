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
require_once(OWA_BASE_DIR.'/owa_controller.php');

/**
 * Login Form Controller
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */
class owa_loginFormController extends owa_controller {

    function __construct($params) {

        return parent::__construct($params);
    }

    function action() {

        $cu = owa_coreAPI::getCurrentUser();

        // Sanitize go parameter - remove it if it points to loginForm to prevent redirect loops
        $go = $this->getParam('go');
        if ($go) {
            $decoded_go = urldecode($go);
            // Remove go parameter if it contains loginForm to prevent loops
            if (strpos($decoded_go, 'base.loginForm') !== false || strpos($decoded_go, 'loginForm') !== false) {
                $go = null;
            }
        }
        
        // If we're on install.php, redirect to index.php for login
        $current_script = basename($_SERVER['PHP_SELF']);
        if ($current_script === 'install.php' && owa_coreAPI::getSetting('base', 'install_complete')) {
            // Installation is complete, redirect to index.php
            $public_url = owa_coreAPI::getSetting('base', 'public_url');
            owa_lib::redirectBrowser($public_url . 'index.php?owa_do=base.loginForm');
            exit;
        }
        
        $this->set('go', $go);
        $this->set('user_id', $cu->getUserData('user_id'));
        $this->setView('base.loginForm');
    }
}

/**
 * Login Form View
 * 
 * @author      Peter Adams <peter@openwebanalytics.com>
 * @copyright   Copyright &copy; 2006 Peter Adams <peter@openwebanalytics.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GPL v2.0
 * @category    owa
 * @package     owa
 * @version        $Revision$
 * @since        owa 1.0.0
 */

class owa_loginFormView extends owa_view {

    function __construct() {

        return parent::__construct();
    }

    function construct($data) {

        $this->setTitle("Login");
        $this->t->set_template('wrapper_public.tpl');
        $this->body->set_template('login_form.tpl');
        $this->body->set('headline', 'Please login using the from below');
        $this->body->set('user_id', $this->get('user_id'));
        
        // Ensure error_code is converted to error_msg if present
        // assembleView() should handle this, but we ensure it here as well
        if (array_key_exists('error_code', $this->data) && !array_key_exists('error_msg', $this->data)) {
            $error_msg = $this->getMsg($this->data['error_code']);
            if ($error_msg) {
                $this->t->set('error_msg', $error_msg);
                $this->data['error_msg'] = $error_msg; // Also set in data for consistency
            }
        }
        
        // Sanitize go parameter - ensure it doesn't point to loginForm
        $go = $this->get('go');
        if ($go) {
            $decoded_go = urldecode($go);
            // Remove go parameter if it contains loginForm to prevent loops
            if (strpos($decoded_go, 'base.loginForm') !== false || strpos($decoded_go, 'loginForm') !== false) {
                $go = '';
            } else {
                $go = owa_sanitize::cleanUrl($go);
            }
        }
        $this->body->set('go', $go);
        $this->setJs("owa", "base/dist/owa.reporting-combined-min.js");
    }
}

?>