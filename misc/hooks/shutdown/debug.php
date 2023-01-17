<?php
if(!defined('ROOT')) exit('No direct script access allowed');

// Memory usage: real: 6291456, emalloc: 9513576
// Code Profiler	Time	Cnt	Emalloc	RealMem
// Average query length: 0.00070427498727475 seconds
// Queries per second: 1419.8999227128
// Longest query length: 0.0047740936279297
// Longest query: 

if(!defined("DEBUG_LOG") || DEBUG_LOG) {
	if(getConfig("APPS_STATUS")!="production" && getConfig("APPS_STATUS")!="prod") {
		if(!defined("SERVICE_ROOT") || !defined("TEST_ROOT")) {
			if(isset($_SESSION['REQUEST_PAGE_START'])) $timeStart = $_SESSION['REQUEST_PAGE_START'];
			elseif(_server("REQUEST_TIME_FLOAT")) {
				$timeStart = _server("REQUEST_TIME_FLOAT");
			} else {
				$timeStart = false;
			}
			$renderingTime = ceil((microtime(true) - $timeStart)/1000);
			_log("PAGE RENDERING TIME - ".$renderingTime." ms","console");

			$memUsage1 = ceil(memory_get_usage()/1000000);
			$memUsage2 = ceil(memory_get_peak_usage(true)/1000000);
			_log("MEMORY USAGE - ".$memUsage1." MB, Peak Usage - ".$memUsage2." MB", "console");

			//Executed queries, 106
			//Longest query, 106
		}
	}
}
?>