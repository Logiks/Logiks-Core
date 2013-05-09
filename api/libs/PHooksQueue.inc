<?php
/*
 * Simple PHP State Hook Queue Implementation.
 * Essentially, a HOOKs are a places in core system codes that allows you to tap into the System
 * to either provide different behavior or to react when something happens:'state' called under
 * certain circumstances. This technique employs so-called hooks to make a chain of procedures
 * as an event handlers.
 *
 * Logiks Framework, provides developers with hooks that let them take additional action when ever
 * a state is activated. If a developer doesn't implement a state, then no hooks are implemented.
 * If a developer implements a state, then all hooks created for the state are implemented.
 * If a developer implements a hook, they can have some additional code run whenever the state is run.
 * This code could do anything, including rolling back and/or altering the original action prior to
 * state. It could also do something unrelated to the state entirely.
 *
 * The hooks helper, uses HOOKS_FOLDER folders across core system and apps system to find and run the
 * hooks as per states. These states are mere folders in the HOOKS_FOLDER. So creating a new hook is
 * like creating a new php file and droping them into the folder.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2012
 * Author: Kshyana Prava kshyana23@gmail.com on 24/02/2012
 * Version: 1.0
 */

class PHooksQueue {
	//Runs Only Hooks Based In System Hooks Folder
	public static function runSysHooks($hookState,$autoEcho=false) {
		$arr=PHooksQueue::getHooksForState($hookState);

		foreach ($arr["CORE"] as $h) {
			@include ROOT.HOOKS_FOLDER.$hookState."/$h";
		}
	}
	//Runs All Hooks Across System And App Hooks Folder
	public static function runHooks($hookState,$autoEcho=false) {
		$arr=PHooksQueue::getHooksForState($hookState,SITENAME);

		foreach ($arr["CORE"] as $h) {
			@include ROOT.HOOKS_FOLDER.$hookState."/$h";
		}
		foreach ($arr["APPS"] as $h) {
			@include APPROOT.APPS_HOOKS_FOLDER.$hookState."/$h";
		}
	}
	//Runs All Hooks Across System And App Hooks Folder
	public static function runPluginHooks($plugin,$state) {
		$arr=PHooksQueue::getHooksForPluginState($plugin,$state);

		foreach ($arr["CORE"] as $h) {
			@include $h;
		}
		foreach ($arr["APPS"] as $h) {
			@include $h;
		}
	}
	private static function getHooksForPluginState($plugin,$state) {
		$out=array();
		$out["CORE"]=array();
		$out["APPS"]=array();
		$pname="hooks_plugins/";

		if(defined("APPS_MISC_FOLDER")) {
			$hooksFolder=APPROOT.APPS_MISC_FOLDER.$pname;
			if(!is_dir($hooksFolder)) {
				@mkdir($hooksFolder,0777,true);
				@chmod($hooksFolder,0777);
			}
			$hooksFolder.="{$plugin}/{$state}/";
			if(is_dir($hooksFolder)) {
				$arr=scandir($hooksFolder);
				unset($arr[0]);unset($arr[1]);
				foreach($arr as $b) {
					if(strpos($b,"~")!==0 && strpos($b,".")!==0 && end(explode(".",$b))=="php")
						array_push($out["APPS"],$hooksFolder.$b);
				}
			}
		}
		if(defined("MISC_FOLDER")) {
			$hooksFolder=ROOT.MISC_FOLDER.$pname;
			if(!is_dir($hooksFolder)) {
				@mkdir($hooksFolder,0777,true);
				@chmod($hooksFolder,0777);
			}
			$hooksFolder.="{$plugin}/{$state}/";
			if(is_dir($hooksFolder)) {
				$arr=scandir($hooksFolder);
				unset($arr[0]);unset($arr[1]);
				foreach($arr as $b) {
					if(strpos($b,"~")!==0 && strpos($b,".")!==0 && end(explode(".",$b))=="php")
						array_push($out["CORE"],$hooksFolder.$b);
				}
			}
		}
		natsort($out["CORE"]);
		natsort($out["APPS"]);
		return $out;
	}
	private static function getHooksForState($hookState,$site="") {
		$out=array();
		$out["CORE"]=array();
		$out["APPS"]=array();

		if(strlen($site)>0) {
			if(defined("APPS_HOOKS_FOLDER")) {
				if(is_dir(APPROOT.APPS_HOOKS_FOLDER.$hookState)) {
					$arr=scandir(APPROOT.APPS_HOOKS_FOLDER.$hookState);
					unset($arr[0]);unset($arr[1]);
					foreach($arr as $b) {
						if(strpos($b,"~")!==0 && strpos($b,".")!==0 && end(explode(".",$b))=="php")
							array_push($out["APPS"],$b);
					}
				}
			}
		}
		if(defined("HOOKS_FOLDER")) {
			if(is_dir(ROOT.HOOKS_FOLDER.$hookState)) {
				$arr=scandir(ROOT.HOOKS_FOLDER.$hookState);
				unset($arr[0]);unset($arr[1]);
				foreach($arr as $b) {
					if(strpos($b,"~")!==0 && strpos($b,".")!==0 && end(explode(".",$b))=="php")
						array_push($out["CORE"],$b);
				}
			}
		}
		natsort($out["CORE"]);
		natsort($out["APPS"]);

		PHooksQueue::registerState($hookState);
		return $out;
	}
	public static function registerState($hookState) {
		if(defined("APPS_HOOKS_FOLDER")) {
			if(!is_dir(APPROOT.APPS_HOOKS_FOLDER.$hookState) && is_writable(APPROOT.APPS_HOOKS_FOLDER)) {
				@mkdir(APPROOT.APPS_HOOKS_FOLDER.$hookState,0777,true);
				@chmod(APPROOT.APPS_HOOKS_FOLDER.$hookState,0777);
			}
		}
		if(defined("HOOKS_FOLDER")) {
			if(!is_dir(ROOT.HOOKS_FOLDER.$hookState) && is_writable(ROOT.HOOKS_FOLDER)) {
				@mkdir(ROOT.HOOKS_FOLDER.$hookState,0777,true);
				@chmod(ROOT.HOOKS_FOLDER.$hookState,0777);
			}
		}
	}
	public static function createHook($title,$code,$state,$root=false) {
		PHooksQueue::registerState($state);
		$f="";
		if($root) {
			$f=ROOT.HOOKS_FOLDER.$state."/$title.".date('Ymd:Hi').".php";
		} else {
			if(defined("APPS_HOOKS_FOLDER")) {
				$f=APPROOT.APPS_HOOKS_FOLDER.$state."/$title.".date('Ymd:Hi').".php";
			}
		}
		if(strlen($f)<=0){
			return false;
		} else {
			$a=file_put_contents($f,$code);
			if($a===false) return false;
			else {
				chmod($f,0777);
				return true;
			}
		}
	}
}
?>
