<?php
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
