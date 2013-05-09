<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(isset($_REQUEST["action"])) {
	$s=$_SESSION["LGKS_SESS_SITE"];
	$_REQUEST["action"]=strtolower($_REQUEST["action"]);
	
	loadHelpers("specialcfgfiles");
	
	$pDir=ROOT.APPS_FOLDER."$s/tmp/published/";
	
	if(!is_dir($pDir)) {
		mkdir($pDir,0777,true);
		chmod($pDir,0777);
	}
	
	if($_REQUEST["action"]=="publish" && isset($_POST["file"])) {
		$Expires="";
		$Once_Only="false";
		
		$Target=$_POST["file"];
		if(isset($_POST["expires"])) $Expires=$_POST["expires"];
		if(isset($_POST["once_only"])) $Once_Only=$_POST["once_only"];
		
		if(!file_exists(ROOT.APPS_FOLDER."$s/{$Target}")) {
			printErr("404");
			exit();
		}
		
		$pArr=array();
		$pArr["Target"]=$Target;
		$pArr["Expires"]=$Expires;
		$pArr["Once_Only"]=$Once_Only;
		$pArr["Last_Visited"]="";
		$pArr["Active"]="true";
		
		$code=md5($s.$Target);//.time()
		$pf=$pDir."{$code}.ini";
		SpecialCfgFiles::SaveIniFile($pf,$pArr);
		
		exit($code);
	} elseif($_REQUEST["action"]=="view" && isset($_REQUEST['pid'])) {
		$pid=$_REQUEST['pid'];
		$pf=$pDir.$pid.".ini";
		if(file_exists($pf)) {
			$pArr=SpecialCfgFiles::LoadIniFile($pf);
			//printArray($pArr);
			if($pArr["Active"]=="true") {
				if(strlen($pArr["Expires"])<=0 || $pArr["Expires"]>time()) {
					$pArr["Last_Visited"]=time();
					SpecialCfgFiles::SaveIniFile($pf,$pArr);
					
					$f=ROOT.APPS_FOLDER.$s."/".$pArr["Target"];
					if(file_exists($f)) {
						printFile($f);
						
						if($pArr["Once_Only"]=="true") {
							$pArr["Active"]="false";
							$pArr["Last_Visited"]=time();
							
							SpecialCfgFiles::SaveIniFile($pf,$pArr);
							unlink($pf);
						}						
						exit();
					} else {
						disposePublishConfig($pf,$pArr);
					}
				} else {
					disposePublishConfig($pf,$pArr);
				}
			} else {
				disposePublishConfig($pf,$pArr);
			}
		} else {
			printErr("404");
		}
		exit();
	}
}
printErr("WrongFormat");
exit();

function move($src,$dest) {
	if(copy($src,$dest)) {
		return unlink($src);
	}
	return false;
}
function disposePublishConfig($pf,$pArr) {
	$pArr["Active"]="false";
	$pArr["Last_Visited"]=time();
	
	SpecialCfgFiles::SaveIniFile($pf,$pArr);
	unlink($pf);
	printErr("404");
}
function printFile($f) {
	$filename=$f;
	
	include ROOT."/config/mimes.php";
	
	$mime=getMimeTypeForFile($filename);
	$ext=explode(".",$filename);
	$ext=$ext[sizeOf($ext)-1];
	$filename=md5($filename).".$ext";

	header("Content-type: $mime");
	header("Content-Transfer-Encoding: binary\n");
	header("Expires: 0");
	
	readfile($f);
}
?>
