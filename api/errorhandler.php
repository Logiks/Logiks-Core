<?php
if(!defined('ROOT')) exit('No direct script access allowed');

ini_set("display_errors",1);
ini_set("log_errors",1);

if(!function_exists("errorHandler")) {
	function loadAutoErrorHandlers() {
		//set error handler
		set_error_handler("errorHandler");
		//set exception handler
		set_exception_handler("exceptionHandler");
	}
	
	//ERROR Handler Function
	function errorHandler($errLvl, $errMsg,$file, $line) {
		$errName=phpErrorLevelNames($errLvl);
		$msgFormat=ERROR_MESSAGE_FORMAT;
		if(strlen(ERROR_MESSAGE_FORMAT)==0 || ERROR_MESSAGE_FORMAT=="#default") {
			$msg="<b style='color:red'>#errName [#errLvl] : </b><b style='color:green'>#errMsg</b> In File #file On Line #line<br/>";
		} else {
			$msg=ERROR_MESSAGE_FORMAT."<br/>";
		}
		$msg=str_replace("#errName","$errName",$msg);
		$msg=str_replace("#errLvl","$errLvl",$msg);
		$msg=str_replace("#errMsg","$errMsg",$msg);
		$msg=str_replace("#file",str_replace($_SERVER['DOCUMENT_ROOT'],"",$file),$msg);
		$msg=str_replace("#line","$line",$msg);
		
		if (!(error_reporting() & $errLvl)) {
			//This error code is not included in error_reporting
			return false;
		}
		if(strtolower(ERROR_DISP_TYPE)=="plain") {
			echo $msg;
		} elseif(strtolower(ERROR_DISP_TYPE)=="hidden") {
			echo "<div style='display:none'>";
			echo $msg;
			if(strtolower(ERROR_TRACE)=="true") {
				$trace=debug_backtrace();
				echo "<pre>";
				print_r($trace);
				echo "</pre>";
			}
			echo "</div>";
		} elseif(strtolower(ERROR_DISP_TYPE)=="mordern") {
			if(function_exists("createTimeStamp"))
				$ts=createTimeStamp();
			else
				$ts=md5(date("H:i:s").microtime());
			
			echo "<div style='display:inline-block;vertical-align:top;' class=errblock>";
			echo "<div id=err_$ts style='display:none;border:2px solid #CC0000;margin-right:5px;padding-right:5px;padding-left:5px;' class=errmsg>";
			
			if(strtolower(ERROR_TRACE)=="true") {
				echo "<button onclick='";
				
				if(ERROR_VIEWER!="inline" && strlen(ERROR_VIEWER)>1) {
					echo ERROR_VIEWER . "(\"err_trace_$ts\");";
				} else {
					echo "if(document.getElementById(\"err_trace_$ts\").style.display==\"none\") 
								document.getElementById(\"err_trace_$ts\").style.display=\"table\"; 
						  else document.getElementById(\"err_trace_$ts\").style.display=\"none\";";
				}
				
				echo "'style='display:inline-block;float:left;margin-left:-5px;margin-top:0px;margin-right:8px;background:FFEC96;border:1px solid gray;' title='Show Error Trace !'>$</button>";
			}
			echo "<button onclick='";
			if(strtolower(ERROR_TRACE)=="true") {
				echo "document.getElementById(\"err_trace_$ts\").style.display=\"none\";";
			}
			echo "document.getElementById(\"err_$ts\").style.display=\"none\";
				  document.getElementById(\"errbtn_$ts\").style.display=\"inline-block\";' 
				  style='display:inline-block;float:right;margin-right:-5px;margin-top:0px;margin-left:8px;background:FFEC96;border:1px solid gray;' >X</button>";
			
			echo "<b style='color:red'>Error:</b> [$errLvl] $errMsg In File $file On Line $line<br/>";
			if(strtolower(ERROR_TRACE)=="true") {
				$trace=debug_backtrace();
				echo "<div id=\"err_trace_$ts\" style='display:none' class=errtrace><pre>";
				print_r($trace);
				echo "</pre></div>";
			}
			
			echo "</div>";
			echo "<button id=\"errbtn_$ts\" onclick='document.getElementById(\"err_$ts\").style.display=\"inline-block\";this.style.display=\"none\";' 
						style='margin-left:3px;margin-right:3px;border:1px solid red;background:FFEC96;'
						title='Show Error Infos !'>?</button>";
			echo "</div>";
		} elseif(strtolower(ERROR_DISP_TYPE)=="errpage" || strtolower(ERROR_DISP_TYPE)=="page") {
			$params=array(
				"tpl"=>"errors.tpl",
				"errIcon"=>"general.gif",
				"errno"=>"$errLvl",
				"msg"=>"$errMsg",
				"file"=>"$file",
				"line"=>"$line",
				"type"=>"500",
			);
			$params=array(
				"type"=>500,
				"msg_header"=>"OOPs!, Error:$errLvl",
				"msg_body"=>"Dear Visitor,<br/><br/>
							$msg Please try again a some other time.	Thank you, for your support.<br/><br/>
							".getConfig("APPS_COMPANY")." Team.",
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"",
				"launchDate"=>"",				
			);	
			dispErrPage($params);
		} else {
			if(function_exists("log_ErrorEvent"))  log_ErrorEvent($errLvl,$errMsg,"Error in $file:$line");
		}
		return true;
	}
		
	function exceptionHandler($exception) {
		if(strtolower(EXCEPTION_HANDLER)=="true") {
			$errno=$exception->getCode();
			$errMsg=$exception->getMessage();
			$file=$exception->getFile();
			$line=$exception->getLine();
			//echo "Uncaught exception: " , $exception->getMessage(), "\n";
			errorHandler($errno, $errMsg, $file, $line);
		}
	}
			
	function trigger_ErrorCode($errorCode,$mbody=null,$errFile="") {
		global $error_codes;
		if(array_key_exists($errorCode,$error_codes)) {
			$errorMsg=$error_codes[$errorCode][0];
			$errorLog=$error_codes[$errorCode][1];
		} else {
			$errorMsg="Unknown Error Code :: <b>$errorCode</b>";
			$errorLog="Error Code Database Does Not Yet Support This Error Code.";
			$mbody="Error Remains Unresolved For Code ".$_REQUEST["code"].
				". Please contact server admin for furthur details.<br/><span style='color:#007700'>Sorry For The Inconvenience Caused</span>";
		}
		if($mbody==null) {
			$mbody="Dear Visitor,<br/><br/>
							$errorLog <br/><br/>
							Please come back later.<br/><br/>
							" . getConfig("APPS_COMPANY") . " Team";
		}
		$params=array(
				"type"=>$errorCode,
				"msg_header"=>$errorMsg,				
				"msg_body"=>$mbody,
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"red",
				"launchDate"=>"",
			);
		dispErrPage($params,$errFile);
		exit();
	}
	
	function trigger_UnderConstruction($msg,$mbody=null,$errFile="") {
		if($mbody==null) {
			$mbody="Dear Visitor,<br/><br/>
							You seem to have checked at the wrong time.<br/>
							The site is underconstruction. <br/>
							Please come back later.<br/><br/>
							" . getConfig("APPS_COMPANY") . " Team";
		}
		$params=array(
				"type"=>"501",								
				"msg_header"=>"$msg",				
				"msg_body"=>$mbody,
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"yellow",
				"launchDate"=>"",//August 13, 2011 12:00:00
			);		
		dispErrPage($params,$errFile);
	}
	function trigger_NotFound($msg,$mbody=null,$errFile="") {
		if($mbody==null) {
			$mbody="Dear Visitor,<br/><br/>
							OOPS!, The Requested Page Was Not Found.<br/>
							Don't worry, WebMaster has been notified.<br/>
							Check back soon.<br/><br/>
							" . getConfig("APPS_COMPANY") . " Team";
		}
		$params=array(
				"type"=>"404",//Not Found				
				"msg_header"=>"$msg",				
				"msg_body"=>$mbody,
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"blue",
				"launchDate"=>"",//August 13, 2011 12:00:00
			);
		dispErrPage($params,$errFile);		
	}
	
	function trigger_LoginError($msg,$mbody=null,$errFile="") {
		if($mbody==null) {
			$mbody="Dear Visitor,<br/><br/>
							There was error in Login. Either UserID/Password is Wrong. <br/>
							Please contact the WebMaster for furthur details.<br/><br/>
							" . getConfig("APPS_COMPANY") . " Team";
		}
		$params=array(
				"type"=>"401",//Unauthorized				
				"msg_header"=>"$msg",				
				"msg_body"=>$mbody,
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"red",
				"launchDate"=>"purple",//August 13, 2011 12:00:00
			);
		dispErrPage($params,$errFile);
	}
	
	function trigger_ForbiddenError($msg,$mbody=null,$errFile="") {
		if($mbody==null) {
			$mbody="Dear Visitor,<br/><br/>
							Access Forbidden To The Requested Page. <br/>
							Please contact the WebMaster for furthur details.<br/><br/>
							" . getConfig("APPS_COMPANY") . " Team";
		}
		$params=array(
				"type"=>"403",
				"msg_header"=>"$msg",
				"msg_body"=>$mbody,
				"msg_tips"=>"<h1>".	date('F') . " " . date('d') . " " . date('Y') . "</h1>",
				"theme"=>"",
				"launchDate"=>"",//August 13, 2011 12:00:00				
			);
		dispErrPage($params,$errFile);
	}	
	
	function dispErrPage($params,$errFile="") {
		global $error_pages, $ERROR_ICON_LOCATION;
		
		$errorParams=array(
			"errorType"=>"404",
			"msg_header"=>"OOOPs!, Error Occured",
			"msg_body"=>"Dear Visitor, <br><br>
							An Error Occured During Your Request. Please try again a some other time. Thank you, for your support.<br><br>
							Logiks Team<br>",
			"msg_tips"=>"<h1>" . "</h1>",
			"posted_on"=>date('F') . " " . date('d') . " " . date('Y'),
			"posted_by"=>"WebMaster",
			"theme"=>"",
			"launchDate"=>"",
		);
		if(isset($params["type"])) $errorParams["errorType"]=$params["type"];
		if(isset($params["msg_header"])) $errorParams["msg_header"]=$params["msg_header"];
		if(isset($params["msg_body"])) $errorParams["msg_body"]=$params["msg_body"];
		if(isset($params["msg_tips"])) $errorParams["msg_tips"]=$params["msg_tips"];
		if(isset($params["theme"])) $errorParams["theme"]=$params["theme"];
		if(isset($params["launchDate"])) $errorParams["launchDate"]=$params["launchDate"];
		if(isset($params["posted_by"])) $errorParams["posted_by"]=$params["posted_by"];
		if(isset($params["posted_on"])) $errorParams["posted_on"]=$params["posted_on"];
		
		$ERR_FILE=$errFile;
		if(file_exists($errFile)) {
			$ERR_FILE=$errFile;
		} else if(array_key_exists($params["type"],$error_pages)) {
			$ERR_FILE=$error_pages[$params["type"]];
		} elseif(array_key_exists('default',$error_pages)) {
			$ERR_FILE=$error_pages['default'];
		} else {
			$t=array_keys($error_pages);
			$ERR_FILE=$error_pages[$t[0]];
		}
		
		if($errorParams["errorType"]!=501) {
			log_ErrorEvent($errorParams["errorType"],$errorParams["msg_header"]);
		}

		if(function_exists("_css")) {
			_css("error");
			_css("errorprint","*","","print");
		} else {
			$css=CssPHP::singleton();
			$css->loadCSS("error");
			$css->loadCSS("errorprint","*","","print");
			$css->display();
		}
		if(file_exists($ERR_FILE)) {
			include $ERR_FILE;
			exit();
		} else {
			//$errorParams["posted_by"]
		?>
		<div id="errormsg" align=center style='margin-top:50px;text-align:justify;'>
			<img src='<?=$icon?>' style='float:left'/>
			<h1 align=center><?=$errorParams["msg_header"]?></h1>
			<br/>
			<hr/><br/>
			<h3><?=$errorParams["msg_body"]?></h3>
			<br/><hr/>
			<h5>Dated On [<?=$errorParams["posted_on"]?>]</h5>
			<h5><?=APPS_COPYRIGHT?></h5>
		</div>
		<?php
			exit();
		}
	}
	function dispErrMessage($msg,$msgTitle="Error Info",$errCode="400",$icon='') {
		if(strlen($icon)<=0) {
			$icon="media/images/notfound/process.png";
		} else {
			if(!is_file(ROOT.$icon)) {
				if(file_exists(ROOT."media/images/notfound/$icon.png")) {
					$icon="media/images/notfound/$icon.png";
				} elseif(file_exists(ROOT."media/images/$icon")) {
					$icon="media/images/$icon";
				} else {
					$icon="media/images/notfound/process.png";
				}
			} else {
				$icon=$icon;
			}
		}
		
		if(function_exists("log_ErrorEvent")) log_ErrorEvent($errCode,$msgTitle);
		if($icon==null || strlen($icon)==0 || !file_exists(ROOT.$icon)) $icon=getErrorIcon($errCode);
		elseif(!file_exists(ROOT.$icon)) {
			if(file_exists(ROOT."media/images/notfound/$icon.png")) {
				$icon=SiteLocation."media/images/notfound/$icon.png";
			} else {
				$icon=findMedia($icon);
			}
		}
		if(is_file(ROOT.$icon)) {
			$icon=SiteLocation.$icon;
		}
		_css("error");
		_css("errorprint","*","","print");
		?>
			<div id="errormsg" align=center style='margin-top:50px;text-align:justify;'>
				<img src='<?=$icon?>' style='float:left'/>
				<h1 align=center><?=$msgTitle?></h1>
				<br/>
				<hr/><br/>
				<h3><?=$msg?></h3>
				<br/><hr/>
				<h5>Dated On [<?=date("d/m/Y H:m")?>]</h5>
				<h5>Server On [<?=$_SERVER["HTTP_HOST"]?>]</h5>
			</div>
		<?php
	}
	function getErrorIcon($errCode) {
		global $ERROR_ICON_LOCATION;
		$errorIcon="";
		if(file_exists(ROOT.$ERROR_ICON_LOCATION."msg_$errCode.gif")) {
			$errorIcon=$ERROR_ICON_LOCATION."msg_$errCode.gif";
		} elseif(file_exists(ROOT.$ERROR_ICON_LOCATION."msg_$errCode.png")) {
			$errorIcon=$ERROR_ICON_LOCATION."msg_$errCode.png";
		} elseif(file_exists(ROOT.$ERROR_ICON_LOCATION."msg_default.png")) {
			$errorIcon=$ERROR_ICON_LOCATION."msg_default.png";
		} elseif(file_exists(ROOT.$ERROR_ICON_LOCATION."msg_default.gif")) {
			$errorIcon=$ERROR_ICON_LOCATION."msg_default.gif";
		}
		return $errorIcon;
	}
	function printTrace() {
		$trace=debug_backtrace();
		echo "<div id='traceholder' align=left><pre>";
		print_r($trace);
		echo "</pre></div>";
	}
}
//header("location: " . SITELINK . "index.php?error&ecode=$code&eurl=" . $url . "&emsg=$msg");
?>
