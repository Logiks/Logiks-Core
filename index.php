<?php
/*
 * This is default page for any request landing up on the server apart from REST/services requests.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 2.0
 */

define ('ROOT', dirname(__FILE__) . '/');

ini_set('display_errors', 'On');
error_reporting('On');

//Start the flow
require_once ('api/initialize.php');

//Time To Start Router System
require_once ('api/router.php');
?>
