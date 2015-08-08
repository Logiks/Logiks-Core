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
	function registerShutdownHook($func,$obj=null) {
		if(!isset($_ENV['SOFTHOOKS']['SHUTDOWN'])) {
			$_ENV['SOFTHOOKS']['SHUTDOWN']=array();
		}
		$_ENV['SOFTHOOKS']['SHUTDOWN'][]=array("FUNC"=>$func,"OBJ"=>$obj);
	}
	function runHooks($hookState) {
		if(ENABLE_HOOKS) {
			PHooksQueue::runHooks($hookState);
		}
	}
	function runSysHooks($hookState) {
		if(ENABLE_HOOKS) {
			PHooksQueue::runSysHooks($hookState);
		}
	}
	function runPluginHooks($plugin,$state) {
		if(ENABLE_HOOKS) {
			PHooksQueue::runPluginHooks($plugin,$state);
		}
	}
}
?>
