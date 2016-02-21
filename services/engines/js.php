<?php
if(!defined('ROOT')) exit('No direct script access allowed');

// go on even if user "stops" the script by closing the browser, closing the terminal etc.
ignore_user_abort(true);
// set script running time to unlimited
set_time_limit(0);

$lastLine=system("nodejs $file", $retval);
?>