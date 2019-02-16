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
  
  //Pluggable Hook System, this function allows any function to use Generic Hook System.
  //Later on we will merge this with Logiks Hooks
  function runHookFunctions($hooks,$params=[]) {
    if($hooks==null || !is_array($hooks)) return;
    
    $_ENV['HOOKPARAMS'] = $params;
    
    if(isset($hooks['modules'])) {
      loadModules($hooks['modules']);
    }
    if(isset($hooks['api'])) {
      if(!is_array($hooks['api'])) $hooks['api']=explode(",",$hooks['api']);
      foreach ($hooks['api'] as $apiModule) {
        loadModuleLib($apiModule,'api');
      }
    }
    if(isset($hooks['helpers'])) {
      loadHelpers($hooks['helpers']);
    }
    if(isset($hooks['method'])) {
      if(!is_array($hooks['method'])) $hooks['method']=explode(",",$hooks['method']);
      foreach($hooks['method'] as $m) call_user_func($m,$_ENV['FORM-HOOK-PARAMS']);
    }
    if(isset($hooks['file'])) {
      if(!is_array($hooks['file'])) $hooks['file']=explode(",",$hooks['file']);
      foreach($hooks['file'] as $m) {
        if(file_exists($m)) include $m;
        elseif(file_exists(APPROOT.$m)) include APPROOT.$m;
      }
    }
  }
}
?>
