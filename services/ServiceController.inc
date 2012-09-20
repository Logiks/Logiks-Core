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
	private $serviceCmds=array("scmd","stype","enc","site");
	
	function checkQuery() {
		if(!isset($_REQUEST['scmd'])) {
			printErr('WrongFormat');
			exit();
		}
		$a=checkServiceRoles(APPROOT);
		if($a) return true;
		else {
			printErr('AccessDenial');
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
		
		/*if(count($sarr)>1) {
			if($sarr[0]!=SITENAME) {
			}
		}*/
		
		$scmd=str_replace('.','/',$scmd);
		
		$query['scmd']=$scmd;
		if(PRECLEAN_SERVICE_REQUEST=="true") {
			//TODO
			/*foreach($_REQUEST as $a=>$b) {
				if(in_array($a,$this->serviceCmds)) continue;
				//if($encoded!='none') $_REQUEST[$a]=$this->processEncoded($b, $encoded);
				$scmd=strip_tags($scmd);
				$scmd=stripslashes($scmd);
				$scmd=str_replace('/',' ',$scmd);
				$str = @trim($str);
				if(get_magic_quotes_gpc()) {
					$str = stripslashes($str);
				}
				//$str=mysql_real_escape_string($str);
			}*/			
		}
		
		return $query;
	}
	function executeRequest($request) {
		$ext=strtolower($request['ext']);
		$sfile=SERVICE_FOLDER.$ext."/".$request['scmd'].".".$ext;
		$sfile1=SERVICE_FOLDER.$ext."/".SITENAME."/".$request['scmd'].".".$ext;
		$sfile2=ROOT.APPS_FOLDER.SITENAME."/services/".$ext."/".$request['scmd'].".".$ext;
		
		if(ALLOW_APPS_LOCAL_SERVICES=="true" && file_exists($sfile2)) {
			$this->executeFile($sfile2,$ext);
		} elseif(file_exists($sfile1)) {
			$this->executeFile($sfile1,$ext);
		} elseif(file_exists($sfile)) {
			$this->executeFile($sfile,$ext);			
		} else {
			printErr('NotFound');
			exit();
		}
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
