<?php
/**
 * Get Tracking Code for a Site
 */

require_once('owa_env.php');
require_once(OWA_DIR.'owa.php');

$site_id = 'c1597ea04ab6dfca2312ff36ed06ade5'; // Practical DevSecOps site ID

// Initialize OWA
define('OWA_INSTALLING', false);
$config = ['instance_role' => 'cli'];
$owa = new owa($config);

// Get the tracking code
$tracking_code = owa_coreAPI::getJsTrackerTag($site_id);

echo "=== TRACKING CODE FOR PRACTICAL DEVSECOPS ===\n\n";
echo $tracking_code;
echo "\n\n";
echo "=== INSTRUCTIONS ===\n";
echo "1. Copy the tracking code above\n";
echo "2. Add it to your website's HTML pages (in the <head> section or before </body>)\n";
echo "3. Once added, visit your website and the data will start showing in OWA\n";
echo "\n";
echo "Site ID: $site_id\n";
echo "Domain: https://www.practical-devsecops.com\n";


