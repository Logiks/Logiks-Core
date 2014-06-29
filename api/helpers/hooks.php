<?php
/*
 * Hooks is supporting file for all hook related operations.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("runHooks")) {
	function runHooks($hookState) {
		if(ENABLE_HOOKS=="true") {
			PHooksQueue::runHooks($hookState);
		}
	}
	function runSysHooks($hookState) {
		if(ENABLE_HOOKS=="true") {
			PHooksQueue::runSysHooks($hookState);
		}
	}
	function runPluginHooks($plugin,$state) {
		if(ENABLE_HOOKS=="true") {
			PHooksQueue::runPluginHooks($plugin,$state);
		}
	}
	function activateAutoHookSystem() {
		if(defined("ENABLE_AUTO_HOOKS") && ENABLE_AUTO_HOOKS=="true") {
			register_shutdown_function("runHooks","shutdown");
		}
	}
}
?>
