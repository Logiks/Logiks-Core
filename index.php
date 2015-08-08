<?php
/*
 * This is default page for any request landing up on the server apart from REST/services requests.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */

error_reporting(-1);
ini_set('display_errors', 'On');

define ('ROOT', dirname(__FILE__) . '/');

define("MASTER_DEBUG_MODE",true);
require_once ('api/initialize.php');

?>
