<?php
//ShortHand Functions For Some Most Used functions
if(!defined('ROOT')) exit('No direct script access allowed');

/*Style And Theme Related Functions*/
if(!function_exists("_css")) {
	function _css($cssLnk,$themeName="*",$browser="",$media="") {
		$css=CssPHP::singleton();
		if(defined("APPS_THEME")) $css->loadTheme(APPS_THEME);
		else define("APPS_THEME","default");
		if(is_array($cssLnk)) {
			foreach($cssLnk as $a=>$b) {
				$css->loadCSS($b,$themeName,$browser,$media);
			}
		} else {
			$css->loadCSS($cssLnk,$themeName,$browser,$media);
		}
		return $css->display();
	}
}
if(!function_exists("_js")) {
	function _js($jsLnk) {
		$js=JsPHP::singleton();
		if(is_array($jsLnk)) {
			foreach($jsLnk as $a=>$b) {
				$js->loadJS($b);
			}
		} else {
			$js->loadJS($jsLnk);
		}
		$js->display();
	}
}
if(!function_exists("_skin")) {
	function _skin($skin) {
		$css=CssPHP::singleton();
		if(is_array($skin)) {
			foreach($skin as $a=>$b) {
				$css->loadSkin($b);
			}
		} else {
			$css->loadSkin($skin);
		}
		$css->display();
	}
}
/*Database Oriented ShortHands*/
if(!function_exists("_db")) {
	function _db($sys=false) {
		if($sys) {
			if(function_exists("getSysDBLink"))	return getSysDBLink();
			else return null;
		} else {
			if(function_exists("getAppsDBLink")) return getAppsDBLink();
			else return null;
		}
	}
}
if(!function_exists("_dbQuery")) {
	function _dbQuery($q,$sys=false) {
		if(_db($sys)!=NULL) {
			$r=_db($sys)->executeQuery($q);
			return $r;
		} else {
			return null;
		}
	}
}
if(!function_exists("_dbFetch")) {
	function _dbFetch($result,$format="assoc") {
		if($result && _db()!=NULL) {
			$r=_db()->fetchData($result,$format);
			return $r;
		} else {
			return array();
		}
	}
}
if(!function_exists("_dbData")) {
	function _dbData($result,$format="assoc") {
		if($result && _db()!=NULL) {
			$r=_db()->fetchAllData($result,$format);
			return $r;
		} else {
			return array();
		}
	}
}
if(!function_exists("_dbFree")) {
	function _dbFree($result,$sys=false) {
		if($result && _db($sys)!=NULL)
			_db($sys)->freeResult($result);
	}
}
if(!function_exists("_dbtable")) {
	function _dbtable($tblName,$sys=false) {
		if($sys) {
			return Database::getSysTable($tblName);
		} else {
			return Database::getAppsTable($tblName);
		}
	}
}
if(!function_exists("__dbtable")) {
	function __dbtable($tblName) {
		return Database::getTable($tblName);
	}
}
if(!function_exists("_dataBus")) {
	function _dataBus($keyTag,$val=null) {
		$keyTag=explode(".",$keyTag);
		if(count($keyTag)==1) {
			$a=$keyTag[0];
			$keyTag[0]="/";
			$keyTag[1]=$a;
		}
		if($val==null && !is_array($val)) {
			return DataBus::singleton()->getData($keyTag[1],$keyTag[0]);
		} else {
			return DataBus::singleton()->setData($keyTag[1],$val,$keyTag[0]);
		}
	}
}
/*Cache And Template Oriented Functions*/
if(!function_exists("_cache")) {
	function _cache($lnk,$cacheId=null,$reCache=false) {
		$cache=CacheManager::singleton();
		if(strpos($lnk,"http://")===0 || strpos($lnk,"https://")===0) {
			$a=$cache->getCacheURL($lnk,$cacheId,$reCache);
			if(strlen($a)>0) {
				return file_get_contents($a);
			}
		} else {
			return $cache->getCacheData($lnk,$cacheId,$reCache);
		}
	}
}
if(!function_exists("_template")) {
	//$file=group.file_name;
	function _template($file,$dataArr=null,$sqlData=null,$editable=true) {
		//$file=str_replace(".","/",$file);
		if(strtolower(strstr($file,"."))!=".tpl") {
			$file.=".tpl";
		}
		if(!file_exists($file)) {
			if(file_exists(APPROOT.TEMPLATE_FOLDER.$file)) {
				$file=APPROOT.TEMPLATE_FOLDER.$file;
			} elseif(file_exists(ROOT.TEMPLATE_FOLDER.$file)) {
				$file=ROOT.TEMPLATE_FOLDER.$file;
			} else {
				return false;
			}
		}
		$fileInfo=pathinfo($file);
		$fname=$fileInfo['filename'];
		$bdir=$fileInfo['dirname']."/";

		$sqlFile=$bdir.$fname.".sql";
		$jsFile=$bdir.$fname.".js";
		$cssFile=$bdir.$fname.".css";

		$templates=new TemplateEngine();
		$templates->loadTemplate($file);

		if($sqlData==null) {
			if(file_exists($sqlFile)) {
				$sqlData=file_get_contents($sqlFile);
			}
		}
		if(strlen($sqlData)>0) $templates->loadSQL($sqlData);

		if($dataArr==null) {
			$dataArr=array();
			$dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			$dataArr["time"]=date(getConfig("TIME_FORMAT"));
			$dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));

			$dataArr["site"]=SITENAME;
			if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";

			if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
		} else {
			if(!isset($dataArr["date"])) $dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			if(!isset($dataArr["time"])) $dataArr["time"]=date(getConfig("TIME_FORMAT"));
			if(!isset($dataArr["datetime"])) $dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));

			if(!isset($dataArr["site"])) $dataArr["site"]=SITENAME;
			if(!isset($dataArr["page"])) {
				if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";
			}
			if(!isset($dataArr["user"])) {
				if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			}
			if(!isset($dataArr["privilege"])) {
				if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			}
			if(!isset($dataArr["username"])) {
				if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			}
			if(!isset($dataArr["privilegename"])) {
				if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
			}
		}

		ob_start();
		$templates->display($dataArr);
		$body=ob_get_contents();
		ob_clean();

		$body=TemplateEngine::processTemplate($body,$dataArr,$editable);
		if(file_exists($jsFile)) {
			$body.="<script language=javascript>";
			$body.=file_get_contents($jsFile);
			$body.="</script>";
		}
		if(file_exists($cssFile)) {
			$body.="<style>";
			$body.=file_get_contents($cssFile);
			$body.="</style>";
		}
		return $body;
	}
}
if(!function_exists("_templateData")) {
	function _templateData($templateData,$dataArr=null,$sqlData="",$editable=true) {
		$templates=new TemplateEngine();
		if(strlen($sqlData)>0) $templates->loadSQL($sqlData);

		if($dataArr==null) {
			$dataArr=array();
			$dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			$dataArr["time"]=date(getConfig("TIME_FORMAT"));
			$dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));

			$dataArr["site"]=SITENAME;
			if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";

			if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
		} else {
			if(!isset($dataArr["date"])) $dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			if(!isset($dataArr["time"])) $dataArr["time"]=date(getConfig("TIME_FORMAT"));
			if(!isset($dataArr["datetime"])) $dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));

			if(!isset($dataArr["site"])) $dataArr["site"]=SITENAME;
			if(!isset($dataArr["page"])) {
				if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";
			}
			if(!isset($dataArr["user"])) {
				if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			}
			if(!isset($dataArr["privilege"])) {
				if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			}
			if(!isset($dataArr["username"])) {
				if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			}
			if(!isset($dataArr["privilegename"])) {
				if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
			}
		}

		$body=TemplateEngine::processTemplate($templateData,$dataArr,$editable);

		return $body;
	}
}
/*URL Oriented Functions*/
if(!function_exists("_link")) {
	function _link($page="", $query="", $site=SITENAME) {
		$ssr1=stristr($page,"http://");
		$ssr2=stristr($page,"https://");
		$ssn=strlen($ssr1)+strlen($ssr2);
		if($ssn>0) return $page;

		return generatePageRequest($page, $query, $site);
	}
}
if(!function_exists("_service")) {
	function _service($scmd, $action="", $format="json", $params=array(), $site=SITENAME) {
		$s=SiteLocation."services/?site={$site}&scmd={$scmd}";
		if(strlen($action)>0) $s.="&action={$action}";
		if(strlen($format)>0) $s.="&format={$format}";
		if(is_array($params)) foreach($params as $a=>$b) $s.="&{$a}={$b}";
		return $s;
	}
}
if(!function_exists("_site")) {
	function _site($site=SITENAME) {
		return generatePageRequest("", "" , $site);
	}
}
if(!function_exists("_url")) {
	function _url($params="") {
		$url="{$_SERVER['HTTP_HOST']}/{$_SERVER['REQUEST_URI']}";
		$url=str_replace("//","/",$url);
		$url=str_replace("//","/",$url);
		if(isset($_SERVER["HTTPS"]) && strlen($_SERVER["HTTPS"])>0) $url="https://$url";
		elseif($_SERVER["SERVER_PROTOCOL"]=="HTTP/1.1")	$url="http://$url";
		if(strpos($url,"?")>2) {
			$url.=$params;
		} elseif(strlen($params)>0) {
			$url.="?$params";
		}
		return $url;
	}
}
/*Date And Time Functions*/
if(!function_exists("_time")) {
	//Used Generally To Convert UserFormatted Times To DB Formatted (H:i:s) times
	function _time($time=null, $inFormat="*", $outFormat="H:i:s") {
		if($inFormat=="*") $inFormat=getConfig("TIME_FORMAT");
		if(strlen($time)<=0 || $time==null) {
			$time=date($inFormat);
		}
		$dArr=array("h"=>"","H"=>"","i"=>"","u"=>"","s"=>"","g"=>"","G"=>"");

		$inFormat=str_replace("-","/",$inFormat);
		$inFormat=str_replace(TIME_SEPARATOR,":",$inFormat);

		if($inFormat==$outFormat) return $time;

		if($inFormat=="H:i:s") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["H"]=$regs[1];
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]="0";

			$dArr["g"]=intval($dArr["H"]%12);
			$dArr["G"]=intval($dArr["H"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="h:i:s") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["h"]=$regs[1]%12;
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]="0";

			$dArr["g"]=intval($dArr["h"]);
			$dArr["G"]=intval($dArr["g"]>12?($dArr["g"]+12):$dArr["g"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="G:i:s") {
			preg_match ("/([0-9]*):([0-9]*)+:([0-9]*)/", $time, $regs);
			$dArr["G"]=intval($regs[1]);
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]="0";

			$dArr["g"]=$dArr["G"]%12;
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="g:i:s") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["g"]=intval($regs[1]%12);
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]="0";

			$dArr["G"]=intval($dArr["g"]>12?($dArr["g"]+12):$dArr["g"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="H:i:s:u") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["H"]=$regs[1];
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]=$regs[4];

			$dArr["g"]=intval($dArr["H"]%12);
			$dArr["G"]=intval($dArr["H"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="h:i:s:u") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["h"]=$regs[1]%12;
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]=$regs[4];

			$dArr["g"]=intval($dArr["h"]);
			$dArr["G"]=intval($dArr["g"]>12?($dArr["g"]+12):$dArr["g"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="G:i:s:u") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["G"]=intval($regs[1]);
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]=$regs[4];

			$dArr["g"]=$dArr["G"]%12;
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		} elseif($inFormat=="g:i:s:u") {
			preg_match ("/([0-9]*):([0-9]*):([0-9]*):([0-9]*)/", $time, $regs);
			$dArr["g"]=intval($regs[1]%12);
			$dArr["i"]=$regs[2];
			$dArr["s"]=$regs[3];
			$dArr["u"]=$regs[4];

			$dArr["G"]=intval($dArr["g"]>12?($dArr["g"]+12):$dArr["g"]);
			$dArr["h"]=strlen($dArr["g"])>1?$dArr["g"]:"0{$dArr['g']}";
			$dArr["H"]=strlen($dArr["G"])>1?$dArr["G"]:"0{$dArr['G']}";
		}
		$a=explode(TIME_SEPARATOR,$outFormat);
		$d1="";
		foreach($a as $q=>$w) {
			$d1.=$dArr[$w].TIME_SEPARATOR;
		}
		$d1=substr($d1,0,strlen($d1)-1);
		$d1=str_replace(TIME_SEPARATOR.TIME_SEPARATOR,TIME_SEPARATOR,$d1);
		if($d1==TIME_SEPARATOR) $d1="";
		return $d1;
	}
}
if(!function_exists("_date")) {
	//Used Generally To Convert UserFormatted Dates To DB Formatted (Y/m/d) dates
	function _date($date=null, $inFormat="*", $outFormat="Y/m/d") {
		if($date=="0000-00-00") return "0000-00-00";
		if($date==null || strlen($date)<=0) return "";
		if($inFormat=="*" || $inFormat=="")  $inFormat=getConfig("DATE_FORMAT");

		$dArr=array("d"=>"","m"=>"","Y"=>"","y"=>"","n"=>"","M"=>"","F"=>"");

		$outFormat=str_replace("yy","Y",$outFormat);

		$inFormat=str_replace("yy","Y",$inFormat);
		$inFormat=str_replace("-","/",$inFormat);
		$inFormat=str_replace(DATE_SEPARATOR,"/",$inFormat);

		$months=array(
				1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",
				5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",
				9=>"Sept",10=>"Oct",11=>"Nov",12=>"Dec",
			);
		$monthsFull=array(
				1=>"January",2=>"February",3=>"March",4=>"April",
				5=>"May",6=>"June",7=>"July",8=>"August",
				9=>"September",10=>"October",11=>"November",12=>"December",
			);
		$days=array(
				1=>"Mon",2=>"Teu",3=>"Wed",4=>"Thu",
				5=>"Fri",6=>"Sat",7=>"Sun"
			);
		$daysFull=array(
				1=>"Monday",2=>"Teusday",3=>"Wednesday",4=>"Thursday",
				5=>"Friday",6=>"Saturday",7=>"Sunday"
			);

		if($inFormat==$outFormat) return $date;
		if($inFormat=="d/m/Y") {
			preg_match ("/([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})/", $date, $regs);
			$dArr["d"]=$regs[1];
			$dArr["m"]=$regs[2];
			$dArr["Y"]=$regs[3];
		} elseif($inFormat=="m/d/Y") {
			preg_match ("/([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})/", $date, $regs);
			$dArr["d"]=$regs[2];
			$dArr["m"]=$regs[1];
			$dArr["Y"]=$regs[3];
		} elseif($inFormat=="Y/m/d") {
			preg_match ("/([0-9]{1,4}).([0-9]{1,2}).([0-9]{2})/", $date, $regs);
			$dArr["d"]=$regs[3];
			$dArr["m"]=$regs[2];
			$dArr["Y"]=$regs[1];
		} elseif($inFormat=="Y/d/m") {
			preg_match ("/([0-9]{1,4}).([0-9]{1,2}).([0-9]{2})/", $date, $regs);
			$dArr["d"]=$regs[2];
			$dArr["m"]=$regs[3];
			$dArr["Y"]=$regs[1];
		}

		$dArr["n"]=intval($dArr["m"]);
		$dArr["j"]=intval($dArr["d"]);
		//$dArr["D"]=$days[floor($dArr["j"]%7)];
		$dArr["M"]=$months[$dArr["n"]];
		$dArr["F"]=$monthsFull[$dArr["n"]];
		$dArr["y"]=substr($dArr["Y"], 2);

		$out=$outFormat;
		foreach ($dArr as $key => $value) {
			$out=str_replace($key, $value, $out);
		}
		return $out;
	}
}
if(!function_exists("_pDate")) {
	//From Server Side To Client Side :: Print Date
	function _pDate($date=null) {
		if($date==null || strlen($date)<=0) $date="";//date("Y/m/d");
		return _date($date,"Y/m/d",getConfig("DATE_FORMAT"));
	}
}
if(!function_exists("_timestamp")) {
	function _timestamp($micro=true) {
		if($micro)
			return date(TIMESTAMP_FORMAT).microtime();
		else
			return date(TIMESTAMP_FORMAT);
	}
}
/*Xtra Functions*/
if(!function_exists("_randomid")) {
	function _randomid($d="",$hash=true) {
		$s=SITENAME."_".date("Y-m-d-G:i:s")."_".$_SERVER['REMOTE_ADDR']."_".rand(1000,9999999);
		if(isset($_SESSION['SESS_USER_ID'])) $s.="_".$_SESSION['SESS_USER_ID'];
		if($hash) return $d.md5($s);
		else return $d.$s;
	}
}
if(!function_exists("_ling")) {
	function _ling($data,$forceWord=false) {
		$ling=Lingulizer::singleton();
		if(is_array($data)) {
			foreach($data as $a=>$b) {
				$data[$a]=$ling->toLing($b);
			}
			return $data;
		} else {
			if($forceWord) {
				return $ling->toLing($data);
			} else {
				if(strpos($data," ")>0) {
					return $ling->toLingContent($data);
				} else {
					return $ling->toLing($data);
				}
			}
		}
	}
}
if(!function_exists("_msg")) {
	function _msg($msgID) {
		if(strpos($msgID,"#")===0) {
			$msg1=substr($msgID,1);
			$msg2=_ling($msg1);
			if($msg1==$msg2) return "";
			else return $msg2;
		} else {
			return _ling($msgID);
		}
	}
}
if(!function_exists("_process")) {
	function _replace($str,$glue="#") {
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+@[a-zA-Z]+{$glue}/","replaceFromEnviroment",$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+![0-9]+{$glue}/","replaceFromEnviroment",$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+{$glue}/","replaceFromEnviroment",$str);
		return $str;
	}
}
?>
