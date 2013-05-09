<?php
//This is the dbLogger for Logiks Framework. It helps in Logging Events 
//Specific to Logiks Into Database Tables. This is advanced Logger.
//This is table Specific.
if(!defined('ROOT')) exit('No direct script access allowed');

include "libs/syslogger.php";

if(!function_exists("log_ErrorEvent")) {	
	function logCentral($module,$msg,$errCode=500,$arr=array(),$type="system") {
		if($type=="system") {
			log_SystemEvent($msg, $errCode, 2, $module, null);
		} elseif($type=="activity") {
			log_ActivityEvent($msg, $errCode, 2, $module, null);
		} elseif($type=="error") {
			ob_start();
			var_dump($arr);
			$log=ob_get_contents();
			ob_clean();
			log_ErrorEvent($errCode, $msg, $log);
		}
	}
	
	function log_ErrorEvent($errorCode,$errorMsg=null,$errorLog=null,$source=null) {
		$xmsg="TriggeredError +$errorCode @".$_SERVER["REQUEST_URI"]." #$errorMsg [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,3);
		return LogDB::singleton()->log_ErrorEvent($errorCode,$errorMsg,$errorLog,$source);
	}
	//$priority= Integer :: Lower The Better
	function log_SystemEvent($logData, $codeType="500", $priority=2, $module=null, $source=null) {
		$xmsg="TriggeredError +$codeType @".$_SERVER["REQUEST_URI"]." #$logData [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,1);
		return LogDB::singleton()->log_SystemEvent($logData,$codeType, $priority, $module, $source);
	}
	//$priority= Integer :: Lower The Better
	function log_ActivityEvent($logData, $codeType="500", $priority=2, $module=null, $source=null) {
		$xmsg="TriggeredError +$codeType @".$_SERVER["REQUEST_URI"]." #$logData [".date("Y/m/d G:i:s")."]";
		log_SysEvent($xmsg,2);
		return LogDB::singleton()->log_ActivityEvent($logData,$codeType, $priority, $module, $source);
	}
	function log_SearchEvent($stext) {
		return LogDB::singleton()->log_SearchEvent($stext);
	}
	function log_Requests() {
		return LogDB::singleton()->log_Requests();
	}
	function log_VisitorEvent() {
		return LogDB::singleton()->log_VisitorEvent();
	}
	
	function get_LOG_EVENTS_VISITOR() {
		$arr=array();
		$arr["None"]="none";
		$arr["Always (Once A Session)"]="always";
		$arr["Once A Day"]="onceaday";
		$arr["Once A Month"]="onceamonth";
		$arr["Once A Year"]="onceayear";
		$arr["Once In Life Time"]="lifetime";
		return $arr;
	}
}
class LogDB {
	private static $instance=null;
	private static $noErr=array();
	private $logSQLs=array(
				"error"=>"INSERT INTO %tbl% (id,DATE,TIME,site,user,error_code,error_msg,error_log,backtrace,source,client,user_agent,device) VALUES 
							(0,'%date%',\"%time%\",\"%site%\",\"%user%\",\"%error_code%\",\"%error_msg%\",\"%error_log%\",\"%backtrace%\",\"%source%\",\"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
				"system"=>"INSERT INTO %tbl% (id, DATE, TIME, site, user, priority, category, module, source,  log_data, client, user_agent,device) VALUES 
							(0,'%date%','%time%',\"%site%\",\"%user%\",\"%priority%\",\"%category%\",\"%module%\",\"%source%\",\"%log_data%\",\"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
				"activity"=>"INSERT INTO %tbl% (id, DATE, TIME, site, user, priority, category, module, source, log_data, client, user_agent,device) VALUES 
							(0,'%date%','%time%',\"%site%\",\"%user%\",\"%priority%\",\"%category%\",\"%module%\",\"%source%\",\"%log_data%\",\"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
				"sql"=>"INSERT INTO %tbl% (id, DATE, TIME, sql_query, sql_type, tbl, db, msg, userid, page, site, client) VALUES 
							(0,'%date%','%time%',\"%sql_query%\",\"%sql_type%\",\"%dbtbl%\",\"%db%\",\"%msg%\",\"%user%\",\"%page%\",\"%site%\",\"%REMOTE_ADDR%\")",
				"requests"=>"INSERT INTO %tbl% (id, DATE, TIME, script, source, page, site, user, client, user_agent, device) VALUES 
							(0,'%date%','%time%','%script%','%source%','%page%',\"%site%\",\"%user%\",\"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
				"search"=>"INSERT INTO %tbl% (id, DATE, TIME, site, user, script, source, page, searchtxt,client,user_agent,device) VALUES 
							(0, '%date%','%time%', \"%site%\", \"%user%\",'%script%',\"%source%\",'%page%',\"%searchtxt%\",\"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
				"visitor"=>"INSERT INTO %tbl% (id, DATE, TIME, site, script, source, page, server_info, client, user_agent, device) VALUES 
							(0, '%date%','%time%', \"%site%\", '%script%','%source%','%page%', \"%server_info%\", \"%REMOTE_ADDR%\",\"%user_agent%\",\"%device%\")",
			);
	
	private $dbCon=null;
	
	protected function __construct() {
		$noErr=explode(",",LOG_NO_EVENTS_ON_ERROR);
		foreach($noErr as $a=>$b) {
			$noErr[$a]=trim($b);
		};
		$dbFile=ROOT."config/logdb.cfg";
		if(file_exists($dbFile)) {
			LoadConfigFile($dbFile);
			if(strlen(getConfig("LOGDB_DATABASE"))>0) {
				try {
					$con=new Database(getConfig("LOGDB_DRIVER"));
					$a=$con->logConnect(getConfig("LOGDB_USER"),getConfig("LOGDB_PASSWORD"),getConfig("LOGDB_HOST"),getConfig("LOGDB_DATABASE"));
					if($a) $this->dbCon=$con;
					else $this->dbCon=null;
				} catch(Exception $e) {
					$this->dbCon=null;
				}
				if($this->dbCon==null) {
					log_SysEvent("Failure To Launch LogDbLibs Central DB Configurations",3);
					if(MASTER_DEBUG_MODE=="true") {
						echo "Failure To Launch LogDbLibs Central DB Configurations";
					}
				}
			} else {
				setConfig("LOGDB_PREFIX",$GLOBALS['DBCONFIG']["DB_SYSTEM"]);
				$this->dbCon=_db(true);
			}
		} else {
			setConfig("LOGDB_PREFIX",$GLOBALS['DBCONFIG']["DB_SYSTEM"]);
			$this->dbCon=_db(true);
		}
	}
	
	public static function singleton() {
		if(!isset(self::$instance)) {
			$c=__CLASS__;
			self::$instance=new $c;
		}
		return self::$instance;
	}
	public function getLogDBCon() {
		return $this->dbCon;
	}
	public function findLog($type,$where="",$cols="*") {
		$sql=$this->dbCon->executeQuery("log_".$type,true);
		$sql="SELECT $cols FROM $sql";
		if(strlen($where)>0) {
			$sql.=" WHERE $where";
		}
		$r=$this->dbCon->executeQuery($sql);
		if($r) {
			if($cols=="count(*)") {
				$d=$this->dbCon->fetchData($r);
				return $d[$cols];
			} else {
				return $r;
			}
		} else {
			return null;
		}
	}
	public function countLog($type,$where="") {
		return $this->findLog($type,$where,"count(*)");
	}
	
	private function processSQLfunction($in) {
		$in=$in[0];
		$in=substr($in,1,strlen($in)-2);
		if(isset($_SESSION["LOGPARAMS"][$in])) return $_SESSION["LOGPARAMS"][$in];
		elseif(isset($_SESSION["LOGPARAMS"]["errorParams"][$in])) return $_SESSION["LOGPARAMS"]["errorParams"][$in];
		elseif(isset($_REQUEST[$in])) return $_REQUEST[$in];
		elseif(isset($_SESSION[$in])) return $_SESSION[$in];
		elseif(isset($_SERVER[$in])) return $_SERVER[$in];
		else return "";
	}
	
	private function insertLog($type,$errorParams) {
		if(isset($errorParams["module"])) $module=$errorParams["module"]; else $module=null;
		if(isset($errorParams["source"])) $source=$errorParams["source"]; else $source=null;
		
		if(isset($_REQUEST["page"])) $page=$_REQUEST["page"]; else $page="NA";
		if(isset($_REQUEST["site"])) $site=$_REQUEST["site"]; else $site=SITENAME;
		
		if($this->dbCon!=null) {
			$sql=$this->logSQLs[$type];
			
			$_SESSION["LOGPARAMS"]["tbl"]=getConfig("LOGDB_PREFIX")."_log_".$type;
			$_SESSION["LOGPARAMS"]["uInfo"]=getUserInfo();
			$_SESSION["LOGPARAMS"]["user"]=$_SESSION["LOGPARAMS"]["uInfo"]["SESS_USER_ID"];
			$_SESSION["LOGPARAMS"]["user_agent"]=$this->getUserAgentForLogging();
			$_SESSION["LOGPARAMS"]["date"]=date("Y-m-d");
			$_SESSION["LOGPARAMS"]["time"]=date("H:i:s");
			$_SESSION["LOGPARAMS"]["module"]=$this->getModule($module);
			$_SESSION["LOGPARAMS"]["script"]=$_SERVER['SCRIPT_NAME'];
			$_SESSION["LOGPARAMS"]["rmethod"]=$_SERVER['REQUEST_METHOD'];
			$_SESSION["LOGPARAMS"]["source"]=$this->getSourceQuery($source);
			$_SESSION["LOGPARAMS"]["page"]=$page;
			$_SESSION["LOGPARAMS"]["site"]=$site;
			$_SESSION["LOGPARAMS"]["errorParams"]=$errorParams;
			$_SESSION["LOGPARAMS"]["device"]=$this->getMyDevice();
			
			$sql=preg_replace_callback("/%[a-zA-Z0-9-_]+%/",array($this,"processSQLfunction"),$sql);
			
			//printArray($_SESSION["LOGPARAMS"]);
			//exit($sql);
			$this->dbCon->executeQuery($sql);
		}
		unset($_SESSION["LOGPARAMS"]);
		return true;
	}
	
	public function log_ErrorEvent($errorCode,$errorMsg=null,$errorLog=null,$source=null) {
		if(LOG_EVENTS_ERROR=="false") return true;
		if(in_array($errorCode,LogDB::$noErr)) {
			return false;
		}
		if($source==null && isset($_GET['page'])) {
			$source=$_GET['page'];
		}
		if($errorLog==null) {
			$errorLog=$_SERVER['REQUEST_URI'];
		}
		if($errorMsg==null) {
			include ROOT."config/errors.php";
			if(array_key_exists($errorCode,$error_codes)) {
				$errorMsg=$error_codes[$errorCode][0];
				if($errorLog==null || strlen($errorLog)==0) {
					$errorLog=$error_codes[$errorCode][1];
				}
			} else {
				$errorMsg="Unknown Error Code";
				if($errorLog==null || strlen($errorLog)==0) {
					$errorLog="Error Code Database Does Not Yet Support This Error Code.";
				}
			}
		}
		
		$trace=debug_backtrace();
		$caller=array_shift($trace);
		$infoData="";
		
		ob_start();
		var_dump($caller);
		$infoData=ob_get_contents();
		$infoData=str_replace("\"","'",$infoData);
		ob_end_clean();
		
		$errorLog=str_replace("\"","'",$errorLog);
		
		$params=array();
		$params["error_code"]=$errorCode;
		$params["error_msg"]=$errorMsg;
		$params["error_log"]=$errorLog;
		$params["source"]=$source;
		$params["backtrace"]=$infoData;
		$params["caller"]=$caller;
		
		return $this->insertLog("error",$params);
	}
	
	//$priority= Integer :: Lower The Better
	public function log_SystemEvent($logData,$category="General", $priority=2, $module=null, $source=null) {
		if(LOG_EVENTS_SYSTEM=="false") return true;
		
		$logData=str_replace("\"","'",$logData);
		
		$params=array();
		$params["category"]=$category;
		$params["priority"]=$priority;
		$params["module"]=$module;
		$params["source"]=$source;
		$params["log_data"]=$logData;
		
		return $this->insertLog("system",$params);
	}
	
	//$priority= Integer :: Lower The Better
	public function log_ActivityEvent($logData, $category="Default", $priority=4,$module=null, $source=null) {
		if(LOG_EVENTS_ACTIVITY=="false") return true;
		
		$logData=str_replace("\"","'",$logData);
		
		$params=array();
		$params["category"]=$category;
		$params["priority"]=$priority;
		$params["module"]=$module;
		$params["source"]=$source;
		$params["log_data"]=$logData;
		
		return $this->insertLog("activity",$params);
	}
	
	//SQL Logger
	public function log_SQLEvent($query,$dbtbl=null,$db,$msg="") {
		if(LOG_EVENTS_SQL=="false") return true;
		if(strlen($query)<=0) return false;
		$dbtbl="";
		$qtype="";
		
		$qtype=explode(" ",$query);
		$qtype=trim($qtype[0]);
		
		if($dbtbl==null) {
			$dbtbl=$this->getSQLTableName($query);
		}
		if($db==null) {			
			$db=_db()->getdbName();
		}
		
		$query=str_replace('"','\"',$query);
		//$query=str_replace("'","\'",$query);
		
		$params=array();
		$params["sql_query"]=$query;
		$params["sql_type"]=$qtype;
		$params["dbtbl"]=$dbtbl;
		$params["db"]=$db;
		$params["msg"]=$msg;
		
		if($qtype=="SELECT") {
			if(LOG_EVENTS_SQL_SELECT=="true") {
				return $this->insertLog("sql",$params);
			} else {
				return true;
			}
		} else {
			return $this->insertLog("sql",$params);
		}
	}
	
	public function log_SearchEvent($stext) {
		if(LOG_EVENTS_SEARCH=="false") return true;
		
		$params=array();
		$params["searchtxt"]=$stext;	
		
		return $this->insertLog("search",$params);
	}
	public function log_Requests() {
		if(LOG_EVENTS_REQUESTS=="false") return true;
		
		$params=array();
		
		return $this->insertLog("requests",$params);
	}
	public function log_VisitorEvent() {
		if(LOG_EVENTS_VISITOR=="none") {
			return;
		}
		$infoQuery="";
		if(LOG_EVENTS_VISITOR=="lifetime") {
			$infoQuery="client='".$vInfo["REMOTE_ADDR"]."' AND site='$site'";
		} elseif(LOG_EVENTS_VISITOR=="onceaday") {
			$infoQuery="client='".$vInfo["REMOTE_ADDR"]."' AND site='$site' AND date=CURDATE()";
		} elseif(LOG_EVENTS_VISITOR=="onceamonth") {
			$infoQuery="client='".$vInfo["REMOTE_ADDR"]."' AND site='$site' AND MONTH(CURDATE())=MONTH(date)";
		} elseif(LOG_EVENTS_VISITOR=="onceayear") {
			$infoQuery="client='".$vInfo["REMOTE_ADDR"]."' AND site='$site' AND YEAR(CURDATE())=YEAR(date)";
		} elseif(LOG_EVENTS_VISITOR=="always") {
			$infoQuery="1=1";
		} else {
			return false;
		}
		if(!isset($_SESSION["SESS_USER_ID"]) || $_SESSION["SESS_USER_ID"]=="guest") {
			$where="";
			if(LOG_VISITORS_PAGE=="true" || LOG_VISITORS_PAGE==1) {
				$where="page='$page' ";
			}
			if(strlen($infoQuery)>0) {
				if(strlen($where)>0) $where.="AND $infoQuery";
				else $where=$infoQuery;
			}
			$cnt=$this->countLog("visitor",$where);
			if($cnt<=0) {
				if(LOG_VISITOR_TOTAL_INFO=="true" || LOG_VISITOR_TOTAL_INFO==1) {
					$vInfo=$_SERVER;
					unset($vInfo["HTTP_COOKIE"]);
					ob_start();
					var_dump($vInfo);
					$infoData=ob_get_contents();
					$infoData=str_replace("\"","'",$infoData);
					ob_clean();
				} else {
					$infoData="";
				}
				
				$params=array();
				$params["server_info"]=$infoData;
				
				return $this->insertLog("visitor",$params);
			}
			return true;
		} else {
			return false;
		}
	}
	
	function getModule($module) {
		if($module==null) {
			if(isset($_REQUEST["mod"])) $module=$_REQUEST["mod"];
			elseif(isset($_REQUEST["module"])) $module=$_REQUEST["module"]; 
			else $module="ERRHANDLER";
		}		
		return $module;
	}
	function getSourceQuery($source) {
		if($source==null) {
			$source=$_SERVER["QUERY_STRING"];
		}
		return $source;
	}
	function getUserAgentForLogging() {
		if(LOG_USER_AGENTS=="true") return $_SERVER["HTTP_USER_AGENT"];
		else return "";
	}
	function getMyDevice() {
		if(isset($_COOKIE['USER_DEVICE']) && strlen($_COOKIE['USER_DEVICE'])>0) {
			return $_COOKIE['USER_DEVICE'];
		} else {
			if(function_exists("getUserDeviceType"))
				return getUserDeviceType();
			else {
				setCookie("USER_DEVICE","pc",null,"/");
				return "pc";
			}
		}
	}
	function getSQLTableName($query) {
		$query=str_replace("\n"," ",$query);
		$query=str_replace("\t"," ",$query);
		$query=str_replace("    "," ",$query);
		$query=str_replace("   "," ",$query);
		$query=str_replace("  "," ",$query);		
		$query=strtoupper(trim($query));
		$dbtbl="";
		$n=0;
		if(($n=strpos($query,"FROM "))>2) {
			$arrQ=explode(" ",substr($query,$n));
			$dbtbl=trim($arrQ[1]);
		} elseif(($n=strpos($query,"INTO "))>2) {
			$arrQ=explode(" ",substr($query,$n));
			$dbtbl=trim($arrQ[1]);
		} elseif(($n=strpos($query,"UPDATE "))===0) {
			$arrQ=explode(" ",substr($query,$n));
			$dbtbl=trim($arrQ[1]);
		} elseif(($n=strpos($query,"TABLE "))>2) {
			$arrQ=explode(" ",substr($query,$n));
			$dbtbl=trim($arrQ[1]);
		}
		return $dbtbl;
	}
}
?>
