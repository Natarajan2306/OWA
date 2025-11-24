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

require_once(OWA_BASE_DIR.'/owa_controller.php');
require_once(OWA_BASE_DIR.'/owa_auth.php');

class owa_loginController extends owa_controller {

    public function validate()
    {
        $this->addValidation('user_id', $this->getParam('user_id'), 'userName', ['stopOnError' => true]);
    }

    function action() {

        $auth = owa_auth::get_instance();
        $status = $auth->authenticateUser();
        $go = owa_sanitize::cleanUrl( $this->getParam('go') );
        // if authentication is successfull
        if ($status['auth_status'] == true) {

            // Force redirect to index.php (not install.php) after successful login
            $public_url = owa_coreAPI::getSetting('base', 'public_url');
            $start_page = $this->config['start_page'];
            
            if (!empty($go)) {
                // redirect to url if present, but ensure it's index.php
                $url = urldecode(htmlspecialchars_decode( $go ) );
                // If URL contains install.php, replace with index.php
                if (strpos($url, 'install.php') !== false) {
                    $url = str_replace('install.php', 'index.php', $url);
                }
                // If URL doesn't start with http, prepend public_url
                if (!preg_match('/^https?:\/\//', $url)) {
                    if (strpos($url, '/') !== 0) {
                        $url = $public_url . $url;
                    } else {
                        $url = $public_url . ltrim($url, '/');
                    }
                }
                $this->e->debug("redirecting browser to...:". $url);
                owa_lib::redirectBrowser($url);

            } else {
                //else redirect to home page - always use index.php
                $redirect_url = $public_url . 'index.php?owa_do=' . $start_page;
                $this->e->debug("redirecting browser to start page: ". $redirect_url);
                owa_lib::redirectBrowser($redirect_url);
                exit; // Ensure we exit after redirect
            }

        } else {
            // return login form with error msg
            $this->setView('base.loginForm');
            $this->set('go', $go);
            $this->set('error_code', 2002);
            $this->set('user_id', $this->getParam('user_id'));

        }
    }

    function errorAction() {

        // return login form with error msg
        $this->setView('base.loginForm');
        $this->set('go', $go);
        //$this->set('error_code', 2002);
        $this->set('user_id', $this->getParam('user_id'));
    }
}

?>