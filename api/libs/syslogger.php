<?php
/*
 * Contains LogHandler, LogController classes for systematic logging
 * into system process.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

$CONFIG['Log_Priority']=array(
				"SYSTEM"=>1, //System,Core
				"USER"=>2, //ACTIVITY
				"ERROR"=>3,  //ERROR
				"WARN"=>4, //WARN
			);
$CONFIG['Log_Handlers']=array(
			"Console"=>"console",  //prints log events directly to the console
			"Display"=>"display",  //prints log events back to the browser
			
			"Syslog"=>"sysLog",	//prints log events to Syslog on Unix-like environments or the Event Log on Windows systems
			"FileLog"=>"fileLog",	    //prints log events to a text file
			"MailLog"=>"mailLog",		//prints log events to prospective mails
			//"SAPILog"=>"sapiLog",		//The log is sent directly to the SAPI logging handler.
						
			"JSConsole"=>"jsconsole",//prints log events to js consoles
			"JSAlert"=>"jsalert",	//The log is sent as alert to user using js
			
			//"SQLLog"=>"sqlLog",		//prints log events to dbTables
			
			"Null"=>"",		//prints log events to Null Void/consumes log events (akin to sending them to /dev/null).
		);
if(!is_dir(ROOT.LOG_FOLDER)) {
	mkdir(ROOT.LOG_FOLDER, 0777, true);
}

if(!function_exists("log_SysEvent")) {
	function log_SysEvent($msg,$priority=3) {
		$hls=explode(",",LOG_HANDLERS);
		foreach($hls as $a) {
			LogHandler::handleLog($msg, $priority,$a);
		}
	}
}
class LogHandler {
	public static function handleLog($msg, $priority,$handler) {
		global $CONFIG;
		if(array_key_exists($handler,$CONFIG['Log_Handlers'])) {
			$funcName=$CONFIG['Log_Handlers'][$handler];
			
			if(strtolower($funcName)=="console") {
				LogHandler::console($msg, $priority);
			} elseif(strtolower($funcName)=="display" ) {
				LogHandler::display($msg, $priority);
			} elseif(strtolower($funcName)=="syslog" ) {
				LogHandler::errLog($msg, $priority, 0);
			} elseif(strtolower($funcName)=="filelog" ) {
				LogHandler::errLog($msg, $priority, 3);
			} elseif(strtolower($funcName)=="maillog" ) {
				LogHandler::errLog($msg, $priority, 1);
			} 
			
			//SAPILog
			
			elseif(strtolower($funcName)=="jsconsole" ) {
				LogHandler::jsconsole($msg, $priority);
			} elseif(strtolower($funcName)=="jsalert" ) {
				LogHandler::jsalert($msg, $priority);
			}
			
			else {
				//NULL
			}
		}
	}	
	
	private static function console($msg, $priority) {
		$s=LogController::formatString($msg, $priority) . "\n";
		$stdout = fopen('php://stdout', 'w');
		fwrite($stdout,$s);
		fclose($stdout);
	}
	
	private static function display($msg, $priority) {
		$s=LogController::formatString($msg, $priority);
		echo $s;
	}
	
	private static function errLog($msg, $priority,$type) {
		//$type == 0 >syslog
		//$type == 1 >mailLog
		//$type == 2 >
		//$type == 3 >fileLog
		//$type == 4 >sapiLog
		$s=LogController::formatString($msg, $priority) . "\n";
		if($type==0) {
			error_log($s, 0); 
		} elseif($type==3) {
			$f=date("d-M-Y");
			$f=ROOT.LOG_FOLDER."$f.log";
			error_log($s, 3, $f); 
		} elseif($type==1) {
			$f=LOG_MAIL_TO;
			echo LOG_MAIL_TO;
			error_log($s, 1, $f,"Subject: Error Log System Mail\nFrom: ".LOG_MAIL."\n") or die("Failed To Send Error Log via Mail"); 
			//error_log($s, 1, $f,LOG_MAIL) or die("Failed To Send Error Log via Mail"); 
		}
	}
		
	private static function jsconsole($msg, $priority) {
		$s=LogController::formatString($msg, $priority);
		if($priority==1) echo "<script language=javascript>console.debug(\"$s\");</script>" ;//DEBUG
		elseif($priority==2) echo "<script language=javascript>console.info(\"$s\");</script>" ;//INFO
		elseif($priority==3) echo "<script language=javascript>console.warn(\"$s\");</script>" ;//WARN
		elseif($priority==4) echo "<script language=javascript>console.error(\"$s\");</script>" ;//ERROR
		elseif($priority==5) echo "<script language=javascript>console.log(\"$s\");</script>" ;
	}
	
	private static function jsalert($msg, $priority) {
		$s=LogController::formatString($msg, $priority);
		echo "<script language=javascript>alert('$s');</script>" ;
	}
}
class LogController {	
	public static function formatString($msg, $priority) {
		global $CONFIG;
		$trace=debug_backtrace();
		$s=LOG_FORMAT;
		$date=date(LOG_DATE);
		$time=date(LOG_TIME);
		$n=LogController::getFuncErrorIndex($trace);
		if(!isset($trace[$n]["file"])) $trace[$n]["file"]=__FILE__;
		if(!isset($trace[$n]["line"])) $trace[$n]["line"]=125;
		$data=array(
				"%m"=>"$msg",												//$msg
				"%n"=>"$priority",												//$priority as Number
				"%N"=>array_search($priority,$CONFIG['Log_Priority']),			//$priority in String
				"%d"=>"$date",
				"%t"=>"$time",
				"%s"=>$_SERVER['SERVER_ADDR'],								//$server address
				"%S"=>$_SERVER['SERVER_NAME'],								//$server name
				"%c"=>$_SERVER['REMOTE_ADDR'],								//$client address
				"%p"=>$trace[$n]["file"],									//File Path
				"%F"=>basename($trace[$n]["file"]),							//File Name
				"%f"=>$trace[$n]["function"],								//Function
				"%l"=>$trace[$n]["line"],									//Line No
				"%xf"=>$trace[sizeof($trace)-1]["file"],					//First Caller File
				"%xf"=>$trace[sizeof($trace)-1]["function"],				//First Caller File's Function
				"%xl"=>$trace[sizeof($trace)-1]["line"],					//First Caller File's Line No
				"%u"=>$_SERVER["HTTP_USER_AGENT"],							//user_agent - base64
				"%q"=>"http://" . $_SERVER["HTTP_HOST"] . $_SERVER["PHP_SELF"],			//Query String
				"%Q"=>str_replace(SiteRoot,"/",$trace[$n]["file"]),			//Query File
				//""=>"",
			);
			
		foreach(array_keys($data) as $a) {
			$s=str_replace($a,$data[$a],$s);			
		}
		return $s;
	}
	
	private static function getFuncErrorIndex($trace) {
		/*echo "<pre>";
		print_r($trace);
		echo "</pre>";*/
		$cnt=0;
		foreach($trace as $a) {
			if(($a["function"]=="include" || $a["function"]=="require" ||
				$a["function"]=="include_once" || $a["function"]=="require_once")) {
				//echo $trace[$cnt-1]["line"] . "<br/>";
				return $cnt-1;
			}
			$cnt++;
		}
		return sizeof($trace)-1;
	}
}
?>
