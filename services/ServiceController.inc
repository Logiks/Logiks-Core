<?php
/*
 * ServiceController handles the way the requested command (scmd) is executed
 * based on the command type (stype) for all sync/async requests.
 *
 * @author : Bismay Kumar Mohapatra
 */
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');
//Encoding Supported : base64
include_once ROOT."api/rolemodel.php";

class ServiceController {
	private $serviceCmds=array("scmd","stype","format","enc","site","cached");

	function checkQuery() {
		if(!isset($_REQUEST['scmd'])) {
			printErr('WrongFormat');
			exit();
		}
	}
	function preProcessQuery() {
		global $cmdFormat;
		$query=array();
		if(isset($_REQUEST['scmd'])) {
			$scmd=$_REQUEST['scmd'];
			if(strlen($scmd)<=0) {
				printErr('WrongFormat');
				exit();
			}
			$query['scmd']=$scmd;
		} else {
			printErr('WrongFormat');
			exit();
		}

		if(isset($_REQUEST['stype'])) {
			$ext=strtolower($_REQUEST['stype']);
			if(!in_array($ext,$cmdFormat)) {
				printErr('MethodNotAllowed',"Requested Command Method Is No Enabled/Found In Service Engine.");
				exit();
			}
		} else {
			$ext="php";
		}
		$query['stype']=$ext;

		foreach(array_keys($_REQUEST) as $a) {
			if(!array_key_exists($a,$query)) {
				$query[$a]=$_REQUEST[$a];
			}
		}

		$ext=$query['stype'];
		unset($query['stype']);
		$query['ext']=$ext;

		return $query;
	}
	function cleanRequest($query) {
		$scmd=$query['scmd'];
		if(isset($query['enc'])) {
			$encoded=($query['enc']=='true' || $query['enc']=='base64')?'base64':'none';
		} else $encoded='none';

		$scmd=$this->processEncoded($scmd, $encoded);

		$scmd=str_replace('/',' ',$scmd);
		$scmd=strip_tags($scmd);
		$scmd=stripslashes($scmd);

		if(substr_count($scmd," ")>0) {
			printErr('WrongFormat');
			exit();
		}

		$sarr=explode(".",$scmd);

		$scmd=str_replace('.','/',$scmd);

		$query['scmd']=$scmd;
		if(PRECLEAN_SERVICE_REQUEST=="true") {
			foreach($_GET as $a=>$b) {
				if(is_array($b)) continue;
				$b=strip_tags($b);
				$b=stripslashes($b);
				$b=mysql_real_escape_string($b);
				$_GET[$a]=$b;
				$_REQUEST[$a]=$b;
			}
		}
		return $query;
	}
	function executeRequest($request) {
		$ext=strtolower($request['ext']);
		$cmdArr=array(
				ROOT.APPS_FOLDER.SITENAME."/services/".$ext."/".$request['scmd'].".".$ext,
				SERVICE_ROOT.$ext."/".$request['scmd'].".".$ext,
				ROOT.APPS_FOLDER.SITENAME."/".APPS_PLUGINS_FOLDER."modules/".$request['scmd']."/service.$ext",
				ROOT.PLUGINS_FOLDER."modules/".$request['scmd']."/service.$ext",
				SERVICE_ROOT.$ext."/".SITENAME."/".$request['scmd'].".".$ext,
			);
		//printArray($cmdArr);
		foreach($cmdArr as $fl) {
			if(file_exists($fl)) {
				//echo $fl;
				$this->executeFile($fl,$ext);
				return;
			}
		}
		printErr('NotFound');
		exit();
	}
	function executeFile($file,$ext="php") {
		if($ext=="php") {
			include $file;
		}
		elseif($ext=="py") {
			if(function_exists("py_eval")) {
				$code=file_get_contents($file);
				py_eval($code);
			} else {
				$lastLine=system("python $file", $retval);
			}
		}
		else {
			printErr('NotSupported');
			exit();
		}
	}
	function processEncoded($cmd,$enc='base64') {
		if($enc=='base64') {
			$cmd=base64_decode($cmd);
		}
		return $cmd;
	}
}
?>
