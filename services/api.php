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

function printServiceErrorMsg($errCode,$errMsg,$errorImg="") {
	$envelop=getMsgEnvelop();

	if($errorImg!=null && strlen($errorImg)>0) {
		$errorImg=SiteLocation.$errorImg;
	}
	$arr=array();
	$arr['ErrorCode']=$errCode;
	$arr['ErrorMessage']=$errMsg;
	$arr['ErrorIcon']=$errorImg;
	$arr['RequestedCommand']=$_REQUEST['scmd'];
	$arr['RequestedSite']=$_REQUEST['site'];

	header("Content-Type:text/{$_REQUEST['format']}");
	header("Status: ERROR::$errCode");
	//http_response_code(404);
	//header(":",true,404);

	if($_REQUEST['format']=="html") {
		if($errorImg!=null && strlen($errorImg)>0) {
			echo "{$envelop['start']}<table width=100% height=100% style='border:0px;'><tr><td width=100% align=center valign=center style='border:0px;'>
				<img src='{$errorImg}'  width=48 height=48><p style='color:#AA0000;font:20px Arial;'>" .
				getErrorMsg($errCode) . "</p>$errMsg</td></tr></table>{$envelop['end']}";
		} else {
			echo "{$envelop['start']}<table width=100% height=100% style='border:0px;'><tr><td width=100% align=center valign=center style='border:0px;'><h3 style='color:#AA0000;font:20px Arial;'>" .
				getErrorMsg($errCode) . "</h3>$errMsg</td></tr></table>{$envelop['end']}";
		}
	} elseif($_REQUEST['format']=="select") {
		echo "<option>$errCode :: $errMsg :: ".getErrorMsg($errCode)."</option>";
	} elseif($_REQUEST['format']=="xml") {
		$xml=new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><service></service>");
		$arr=arrayToXML($arr,$xml);
		echo $xml->asXML();
	} elseif($_REQUEST['format']=="json") {
		echo json_encode($arr);
	} elseif($_REQUEST['format']=="text") {
		$s="";
		foreach($arr as $a=>$b) {
			$s.="$a=$b\n";
		}
		echo $s;
	} else {
		//html
		if($errorImg!=null && strlen($errorImg)>0) {
			echo "{$envelop['start']}<table width=100% height=100% style='border:0px;'><tr><td width=100% align=center valign=center style='border:0px;'>
				<img src='{$errorImg}'  width=48 height=48><p style='color:#AA0000;font:20px Arial;'>" .
				getErrorMsg($errCode) . "</p>$errMsg</td></tr></table>{$envelop['end']}";
		} else {
			echo "{$envelop['start']}<table width=100% height=100% style='border:0px;'><tr><td width=100% align=center valign=center style='border:0px;'><h3 style='color:#AA0000;font:20px Arial;'>" .
				getErrorMsg($errCode) . "</h3>$errMsg</td></tr></table>{$envelop['end']}";
		}
	}
}

function printServiceMsg($msgData,$msgCode=200,$msgImage="") {
	$envelop=getMsgEnvelop();

	if($msgImage!=null && strlen($msgImage)>0) {
		$msgImage=SiteLocation.$msgImage;
	}

	$arr=array();
	$arr['MessageCode']=$msgCode;
	$arr['Data']="";
	$arr['MessageIcon']=$msgImage;
	$arr['RequestedCommand']=$_REQUEST['scmd'];
	$arr['RequestedSite']=$_REQUEST['site'];

	header("Content-Type:text/{$_REQUEST['format']}");

	if($_REQUEST['format']=="html" || $_REQUEST['format']=="table") {
		if(is_array($msgData)) {
			printFormattedArray($msgData,true,"table");
		} else {
			$out=getMsgEnvelop();
			echo $out['start'];
			echo $msgData;
			echo $out['end'];
		}
	} elseif($_REQUEST['format']=="select") {
		if(is_array($msgData)) {
			printFormattedArray($msgData,false,"select");
		} else {
			echo "<option>$msgCode :: $msgData </option>";
		}
	} elseif($_REQUEST['format']=="xml") {
		$arr['Data']=$msgData;

		foreach($arr as $a=>$b) {
			if($b==null) $arr[$a]="";
		}
		$xml=new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"UTF-8\" ?><service></service>");
		$arr=arrayToXML($arr,$xml);
		echo $xml->asXML();
	} elseif($_REQUEST['format']=="json") {
		//array_walk_recursive($msgData, create_function('&$item, &$key','$item=urlencode($item);'));
		$arr['Data']=$msgData;
		echo json_encode($arr);
	} elseif($_REQUEST['format']=="text") {
		if(is_array($msgData)) {
			printFormattedArray($msgData);
		} else {
			$msgData=strip_tags($msgData);
			echo $msgData;
		}
	} else {
		if(is_array($msgData)) {
			printFormattedArray($msgData);
		} else {
			$out=getMsgEnvelop();
			echo $out['start'];
			echo $msgData;
			echo $out['end'];
		}
	}
}
function printContentHeader($format='*') {
	include_once ROOT."config/mimes.php";
	printMimeHeader($format);
}
?>
