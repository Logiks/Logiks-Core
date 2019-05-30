<?php
/*
 * This centralizes the logout operation
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
define ('ROOT', dirname(__FILE__) . '/');

ini_set('display_errors', 'On');

//Start the flow
require_once ('api/initialize.php');

include ROOT."api/logout.php";
?>
<h5>Redirecting To Login Screen ...</h5>
