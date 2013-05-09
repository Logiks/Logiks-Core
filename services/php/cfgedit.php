<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(!isset($_REQUEST['cfgfile'])) {
	exit("Configuration File Not Specified");
}
if(!isset($_REQUEST['action'])) {
	exit("Form Action Not Specified");
}

$cfgfile=$_REQUEST['cfgfile'];
if(isset($_REQUEST["forsite"])) {
	$cfgfile=findAppCfgFile($cfgfile);
	if(strlen($cfgfile)<=0) {
		exit("Can't Find Configuration For {$_REQUEST['cfgfile']}");
	}
} else {
	$cfgfile=findCfgFile($cfgfile);
	if(strlen($cfgfile)<=0) {
		exit("Can't Find System Configuration For {$_REQUEST['cfgfile']}");
	}
}
unset($_REQUEST['cfgfile']);

$action=$_REQUEST['action'];
unset($_REQUEST['action']);

if($action=='save') {
	saveConfigs(ROOT.$cfgfile);
} elseif($action=='get') {
	echo json_encode(getConfigs(ROOT.$cfgfile));
} elseif($action=='view') {
	echo "<pre>".viewConfigFile(ROOT.$cfgfile)."</pre>";
} elseif($action=='addfield') {
	$_POST[$_REQUEST['field']]="";
	saveConfigs(ROOT.$cfgfile);
} else {
	exit("Configuration Action Not Understood");
}
exit();

function viewConfigFile($file) {
	$arr=loadCFGFile($file);
	foreach($_REQUEST as $a=>$b) {
		if(array_key_exists($a,$arr[0])) {
			$arr[1][$arr[0][$a]][$a]=$b;
		}
	}
	$s="";
	foreach($arr[1] as $a=>$b) {
		$s.="[$a]\n";
		foreach($b as $x=>$y) {
			$s.="$x=$y\n";
		}
	}
	return $s;
}
function saveConfigs($file) {
	if(!is_writable($file)) {
		exit("Configuration File Is Write Protected.");
	}
	$arr=loadCFGFile($file);
	$keys=array_keys($arr[1]);
	foreach($_POST as $a=>$b) {
		if(array_key_exists($a,$arr[0])) {
			$arr[1][$arr[0][$a]][$a]=$b;
		} elseif(count($keys)==1) {
			$arr[1][$keys[0]][$a]=$b;
		} else {
			exit("Sorry, adding fields are not supported in multi mode files.");
		}
	}
	$s="";
	foreach($arr[1] as $a=>$b) {
		$s.="[$a]\n";
		foreach($b as $x=>$y) {
			$s.="$x=$y\n";
		}
	}
	$a=file_put_contents($file,$s);
	if($a) {
		echo "Successfully Saved Configuration";
	} else {
		echo "Error While Saving Configuration";
	}
}
function getConfigs($file) {
	$arr=loadCFGFile($file);
	$out=array();
	foreach($_POST as $a=>$b) {
		if(array_key_exists($a,$arr[0])) {
			$out[$a]=$arr[1][$arr[0][$a]][$a];
		}
	}
	return $out;
}
function loadCFGFile($file) {
	if(!file_exists($file)) {
		exit("Required Config File Missing");
	} else {
		$data=file_get_contents($file);
		$out=array();
		$mst=array();
		$data=explode("\n",$data);
		$mode="DEFINE";
		foreach($data as $a=>$s) {
			if(substr($s,0,2)=="//") continue;
			if(substr($s,0,1)=="#") continue;
			if(strlen($s)>0) {
				$n1=strpos($s, "=");
				if($n1>0) {
					$name=substr($s,0,$n1);
					$value=substr($s,$n1+1);
					$out[$mode][$name]=$value;
					$mst[$name]=$mode;
				} else {
					if($s=="[DEFINE]") $mode="DEFINE";
					else if($s=="[SESSION]") $mode="SESSION";
					else if($s=="[CONFIG]") $mode="CONFIG";
					else if($s=="[DBCONFIG]") $mode="DBCONFIG";
					else if($s=="[PHPINI]") $mode="PHPINI";
					else if($s=="[ENV]") $mode="ENV";
					else if($s=="[COOKIE]") $mode="COOKIE";
					else $mode="DEFINE";
				}
			}
		}
		return array($mst,$out);
	}
}
function findCfgFile($f) {
	if(file_exists(ROOT."$f.cfg")) {
		return "$f.cfg";
	} elseif(file_exists(ROOT."config/$f.cfg")) {
		return "config/$f.cfg";
	}
	return "";
}
function findAppCfgFile($f) {
	if(file_exists(ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/$f.cfg")) {
		return APPS_FOLDER.$_REQUEST["forsite"]."/$f.cfg";
	} elseif(file_exists(ROOT.APPS_FOLDER.$_REQUEST["forsite"]."/config/$f.cfg")) {
		return APPS_FOLDER.$_REQUEST["forsite"]."/config/$f.cfg";
	}
	return "";
}
?>
