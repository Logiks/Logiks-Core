<?php
/*
 * This is default page for any request landing up on the server apart from REST/services requests.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */

/*
 * Enable Debug mode
 */
$isDebug = array_key_exists('debug', $_REQUEST);
if($isDebug) {
    ini_set('display_errors', 1);
    error_reporting(1);
    define("MASTER_DEBUG_MODE",true);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

define("MASTER_DEBUG_MODE",true);
ini_set('display_errors', 1);
error_reporting(-1);
// ini_set('display_errors', 'On');



define ('ROOT', dirname(__FILE__) . '/');

//Start the flow
require_once ('api/initialize.php');

//Time To Start Router System
require_once ('api/router.php');
?>
