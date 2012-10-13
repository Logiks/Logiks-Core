<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//All functions and resources to be used by error system used by services alone.

ini_set("display_errors",1);
ini_set("log_errors",1);

if(SERVICE_ERROR_HANDLER=="logiks") {
	//set error handler
	set_error_handler("servicesErrorHandler");
	//set exception handler
	set_exception_handler("servicesExceptionHandler");
}

function servicesErrorHandler($errLvl, $errMsg,$file, $line) {
	$errName=phpErrorLevelNames($errLvl);
	if (!(error_reporting() & $errLvl)) {
		//This error code is not included in error_reporting
        return false;
    }
	$msg="Error Parsing Source Script. Please contact Server Maintaince.";
	if(strtolower(SERVICE_DEBUG)=="true") {
		if(strlen(SERVICE_DEBUG_MESSAGE)==0 || SERVICE_DEBUG_MESSAGE=="#default") {
			$msg="<b style='color:red'>#errName [#errLvl] :</b><b style='color:green'>#errMsg</b> In File #file On Line #line<br/>";
		} else {
			$msg=SERVICE_DEBUG_MESSAGE."<br/>";
		}
		$msg=str_replace("#errName","$errName",$msg);
		$msg=str_replace("#errLvl","$errLvl",$msg);
		$msg=str_replace("#errMsg","$errMsg",$msg);
		$msg=str_replace("#file",str_replace($_SERVER['DOCUMENT_ROOT'],"",$file),$msg);
		$msg=str_replace("#line","$line",$msg);
	}
	if(strtolower(SERVICE_DEBUG_TRACE)=="true") {
		$trace=debug_backtrace();
		ob_start();
		printArray($trace);
		$trace=ob_get_contents();
		ob_clean();
		$msg.="<div style='width:800px;height:300px;margin:auto;overflow:auto;border:2px solid #aaa;' align=left>$trace</div>";
	}
	printErr("SourceError",$msg);
	return true;
}
function servicesExceptionHandler($exception) {
	if(strtolower(EXCEPTION_HANDLER)=="true") {
		$errno=$exception->getCode();
		$errMsg=$exception->getMessage();
		$file=$exception->getFile();
		$line=$exception->getLine();
		//echo "Uncaught exception: " , $exception->getMessage(), "\n";
		servicesErrorHandler($errno, $errMsg, $file, $line);
	}
}

//All Error Printing Funcs
function printErr($str,$msg="",$noimage=false) {
	if(strlen($msg)<=0) {
		$msg=DEFAULT_ERROR_MESSAGE;
	}
	printServiceErrorMsg($str,$msg,"services/images/error.png");
}
function printBug($msg="Bugged Instance !") {
	printServiceErrorMsg("Bug",$msg,"services/images/bug.png");
}
function printLoading($msg="Loading ...") {
	//Always HTML
	global $loadImg;
	$envelop=getMsgEnvelop();
	echo "{$envelop['start']}<table width=100% height=100% style='border:0px;'><tr><td width=100% align=center valign=center style='border:0px;'>$loadImg<p style='color:#0055AA'>" . $msg . 
			"</p></td></tr></table>{$envelop['end']}";
}
function getErrorMsg($err) {
	global $services_error_codes,$error_codes;
	if(array_key_exists($err,$services_error_codes)) {
		return $services_error_codes[$err];
	} elseif(array_key_exists($err,$error_codes)) {
		$errArr=$error_codes[$err];
		/*if(strlen($errArr[1])>0) return $errArr[1];
		else return $errArr[0];*/
		return $err."::".$errArr[0]."<br/><h4 style='color:#338833;font:15px Arial;'>$errArr[1]</h4>";
	} else {
		return $services_error_codes["*"];
	}
}
function passErrorMsg($msg) {
	echo "<script language='javascript' type='text/javascript'>self.parent.showError('#$msg');</script><h3>$msg</h3>";
}
?>
