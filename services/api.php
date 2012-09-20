<?php
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');
//All functions and resources to be used by service system

//All System Functions
function getRelativePathToROOT($file) {
	$basepath="";
	
	$s=str_replace(ROOT,"",dirname($file) . "/");
	$s=str_replace("//","/",$s);
	for($j=0;$j<substr_count($s,"/");$j++) {
		$basepath.="../";
	}
	
	return $basepath;
}
function getRequestPath() {
	return 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
}
function parseHTTPReferer() {
	$arr=array();
	$arr["SERVER_PROTOCOL"]="";
	$arr["HTTP_HOST"]="";
	$arr["REQUEST_URI"]="";
	$arr["SCRIPT_NAME"]="";
	$arr["QUERY_STRING"]="";
	
	$arr["SITE"]="";
	$arr["PAGE"]="";
	$arr["MODULE"]="";
	
	//printArray($_SERVER);
	if(isset($_SERVER["HTTP_REFERER"]) && strlen($_SERVER["HTTP_REFERER"])>0) {
		$s=$_SERVER["HTTP_REFERER"];
		$a1=substr($s,0,strpos($s,"://"));
		$s=substr($s,strpos($s,"://")+3);
		$a2=substr($s,0,strpos($s,"/"));
		$s=substr($s,strpos($s,"/")+1);
		$a3=$s;
		$a4=substr($s,0,strpos($s,"?"));
		$s=substr($s,strpos($s,"?")+1);
		$a5=$s;
		
		$n1=strpos($s,"site=");
		if($n1!==false) {
			$w="";
			if($n1>=0) {
				$n2=strpos($s,"&",$n1+5);
				$w=substr($s,$n1+5,$n2-$n1-5);
			} else {
				$w=$_REQUEST['site'];
			}
		} else {
			$w=$_REQUEST['site'];
		}
		
		$n1=strpos($s,"page=");
		$p="";
		if($n1!==false) {
			if($n1>=0) {
				$n2=strpos($s,"&",$n1+5);
				$p=substr($s,$n1+5,$n2-$n1-5);
			}
		}
		
		$n1=strpos($s,"mod=");
		$m="";
		if($n1!==false) {
			if($n1>=0) {
				$n2=strpos($s,"&",$n1+5);
				$m=substr($s,$n1+4,$n2-$n1-4);
			}
		}
				
		$arr["SERVER_PROTOCOL"]=strtoupper($a1);
		$arr["HTTP_HOST"]=$a2;
		$arr["REQUEST_URI"]=$a3;
		$arr["SCRIPT_NAME"]=$a4;
		$arr["QUERY_STRING"]=$a5;
		$arr["SITE"]=$w;
		$arr["PAGE"]=$p;
		$arr["MODULE"]=$m;		
	}
	if(strlen($arr["SITE"])==0 && isset($_REQUEST['site'])) {
		$arr["SITE"]=$_REQUEST['site'];
	}
	return $arr;
}
function getServiceCMD() {
	$scmd=$_REQUEST['scmd'];
	return $scmd;
}
?>
