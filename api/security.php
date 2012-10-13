<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//Functions ::  session_check,isAdminSite,user_admin_check,checkUserSiteAccess,isLinkAccessable
// 				checkDevMode, checkBlacklist,checkSiteMode
if(!function_exists("session_check")) {
	function session_check($redirect=false,$showErrorMsg=false) {
		if(!defined("SITENAME")) {
			if($redirect) {
				$relink=SiteLocation . "login.php?site=".SITENAME;
				redirectTo($relink,"SESSION Expired. Going To Login Page");
				exit();
			} else {
				if($showErrorMsg) {
					trigger_ForbiddenError("Accessing Forbidden Page");
				}
				return false;
			}
		}
		if(isset($_SESSION['SESS_USER_ID']) && isset($_SESSION['SESS_PRIVILEGE_ID']) && isset($_SESSION['SESS_TOKEN'])
			&& isset($_SESSION['SESS_LOGIN_SITE']) && isset($_SESSION['SESS_ACCESS_SITES'])
			&& isset($_SESSION['SESS_ACCESS_ID'])) {
			
			$siteAccessArr=$_SESSION['SESS_ACCESS_SITES'];
			if(!is_array($siteAccessArr)) $siteAccessArr=explode(",",$siteAccessArr);
			if(in_array(SITENAME,$siteAccessArr)) return true;
		}
		if($redirect) {
			$relink=SiteLocation . "login.php?site=".SITENAME;
			redirectTo($relink,"SESSION Expired. Going To Login Page");
			exit();
		} else {
			if($showErrorMsg) {
				trigger_ForbiddenError("Accessing Forbidden Page");
			}
			return false;
		}
		sessionExpired();
	}
	function isAdminSite($autoExit=true) {
		if(!defined("ADMIN_APPSITES")) {
			$f=ROOT.CFG_FOLDER."lists/adminsites.lst";
			$f=file_get_contents($f);
			$f=explode("\n",$f);
			if(strlen($f[count($f)-1])==0) unset($f[count($f)-1]);
			define("ADMIN_APPSITES",implode(",",$f));
		}
		$site=SITENAME;
		$acp=explode(",",ADMIN_APPSITES);
		if(in_array($site, $acp)) return true;
		else {
			if($autoExit) {
				if(function_exists("printErr"))	{
					printErr("AccessDenial","Requested Site Is Forbidden To Current User.");
				} else {
					dispErrMessage("Requested Site Is Forbidden To Current User.");
				}
				exit();
			}
			return false;
		}
	}
	function user_admin_check($redirect=false,$msg="This Is System Administrator Only Page.") {
		$a=session_check(false);
		if($a) {
			isAdminSite();
			$acp=$_SESSION['SESS_ACCESS_SITES'];
			if(!in_array(SITENAME,$acp)) {
				if($redirect) trigger_ForbiddenError($msg);
				return false;
			}
			return true;
		} else {
			if($redirect) trigger_ForbiddenError($msg);
			return false;
		}		
	}
	function checkUserSiteAccess($site=null,$autoExit=true) {
		if($site==null) $site=SITENAME;
		if($site=="*" && $_SESSION["SESS_ACCESS_ID"]=="1") {
			return true;
		}
		if(in_array($site, $_SESSION["SESS_ACCESS_SITES"])) return true;
		else {
			if($autoExit) {
				if(function_exists("printErr"))	{
					printErr("AccessDenial","Requested Site Is Forbidden To Current User.");
				} else {
					dispErrMessage("Requested Site Is Forbidden To Current User.");
				}
				exit();
			}
			return false;
		}
		return false;
	}
	function checkDevMode($site=null) {
		if($site==null) $site=SITENAME;
		
		if(defined("DEV_MODE_IP") && strlen(DEV_MODE_IP)>0) {
			$ips=explode(",",DEV_MODE_IP);
			if(sizeOf($ips)>0) {
				loadHelpers("devmode"); 
				__initDevMode($ips);
			}
		}
	}
	function checkSiteMode($site=null) {
		if(!defined("PUBLISH_MODE")) return false;
		if($site==null) $site=SITENAME;
		
		if(checkBlacklist($site)) {
			trigger_ForbiddenError("Your IP Is Banned By Admin",
						"<div style='margin-top:20px;font:14px Georgia;'>Sorry, your IP has been banned/restricted/blocked by Server Administrator.<br/><br/>
						Please contact <b>".getConfig("APPS_COMPANY")."</b> or email @ <a href='mailto:".WEBMASTER_EMAIL."'>".WEBMASTER_EMAIL."</a> 
						for further details<br/><br/>"
						. "<h4>". getConfig("APPS_COMPANY") . " Team</h4>"
						. "<h4>".  date("d/m/y H:m:s")."</h4></div>"
						);
			exit();
		}
		
		if(strtolower(PUBLISH_MODE)=="blocked") {
			trigger_ForbiddenError("Site <b>'{$_SERVER['HTTP_HOST']}'</b> Is Currently Blocked.",
						"<div style='margin-top:20px;font:14px Georgia;'><h2>Sorry ..................</h2>
						<h3>This site is currently blocked by <i>Server Administrator</i></h3>
						If you are the webmaster for this site or you own this site, please contact <b>Server Administrator</b> or email @ 
						<a href='mailto:".WebMasterMail."'>".WebMasterMail."</a> for activating this site.<br/><br/>"
						. "<h4><b>Root Administrator</b></h4>"
						. "<h4>".  date("d/m/y H:m:s")."</h4></div>"
						);
			exit();
		} elseif(strtolower(PUBLISH_MODE)=="restricted" || strtolower(PUBLISH_MODE)=="whitelist") {
			$client=$_SERVER["REMOTE_ADDR"];
			
			$f=ROOT.CACHE_IPLIST_FOLDER."{$site}/whitelist.dat";
			if(!file_exists($f)) {
				Security::generateIPListCache("whitelist");
			} elseif((time()-filectime($f))>PERMISSION_CACHE_PERIOD) {
				Security::generateIPListCache("whitelist");
			}
			if(!file_exists($f)) {
				dispErrMessage("Security Inconsistancy Found. Please Contact Admin.");
				exit();
			}
			$data=file_get_contents($f);
			$ipArr=explode("\n",$data);
			if(strlen($ipArr[count($ipArr)-1])==0) unset($ipArr[count($ipArr)-1]);
			if(!in_array($client,$ipArr)) {
				trigger_ForbiddenError("Site <b>'{$_SERVER['HTTP_HOST']}'</b> Is Currently In Restrictive/Whitelist Only Mode.",
							"<div style='margin-top:20px;font:14px Georgia;'>Sorry, currently this Site is running in Whitelist/Restrictive mode as per Server Administrator.
							In this mode you will be allowed to access the domain/site only if your ip belongs to the WhiteList IP Address for the site.<br/><br/>
							Please contact <b>".getConfig("APPS_COMPANY")."</b> or email @ <a href='mailto:".WEBMASTER_EMAIL."'>".WEBMASTER_EMAIL."</a> 
							for further details<br/><br/>"
							. "<h4>". getConfig("APPS_COMPANY") . " Team</h4>"
							. "<h4>".  date("d/m/y H:m:s")."</h4></div>"
							);
				exit();
			}
		} elseif(strtolower(PUBLISH_MODE)=="maintainance") {
			trigger_ForbiddenError("Site <b>'{$_SERVER['HTTP_HOST']}'</b> Is Down For Maintenance.",
						"<h2>OOPs........., Wrong Time :-(</h2>
						<h3>Currently we are upgrading new features sets.</h3>
						Please Visit After Some Time.<br/><br/><i>Thank You</i><br/>"
						. "<i>". getConfig("APPS_COMPANY") . " Team</i>"
						);
			exit();
		} elseif(strtolower(PUBLISH_MODE)=="underconstruction") {
			trigger_ForbiddenError("Site <b>'{$_SERVER['HTTP_HOST']}'</b> Is Under-Construction.",
						"<h2>OOPs........., Wrong Time :-(</h2>
						<h3>Thank you for visiting us. We are still in the process of creating this appliation.</h3>
						Please Visit After Some Time.<br/><br/><i>Thank You</i><br/>"
						. "<i>". getConfig("APPS_COMPANY") . " Team</i>"
						);
			exit();
		}
	}
	function isLinkAccessable($link=null) {
		if(strpos($_SERVER["QUERY_STRING"],"page")<=0 || $_REQUEST['page']=="home" || $_REQUEST['page']=="dashboard") {
			return true;
		}
		if($link==null) {
			$link=array();
			foreach($_GET as $a=>$b) {
				if($a=='forsite') continue;
				elseif($a=='forSite') continue;
				array_push($link,"$a=$b");
			}
			if(!isset($_REQUEST['site'])) array_push($link,"site=".SITENAME);
			if(!isset($_REQUEST['page'])) array_push($link,"site=home");
			$link=implode("&",$link);
		}
		
		$pridid="10";
		if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $pridid=$_SESSION["SESS_PRIVILEGE_ID"]; else $pridid="Guest";
		if($pridid<=3) return true;
		$site=SITENAME;
		$privilege=md5($_SESSION["SESS_PRIVILEGE_ID"]);
		$f=ROOT.CACHE_PERMISSIONS_FOLDER."{$site}/{$privilege}.dat";
		if(!file_exists($f)) {
			Security::generateUserLinksCache($privilege);
		} elseif((time()-filectime($f))>PERMISSION_CACHE_PERIOD) {
			Security::generateUserLinksCache($privilege);
		}
		if(!file_exists($f)) {
			dispErrMessage("Security Inconsistancy Found. Please Contact Admin.");
			exit();
		}
		$data=file_get_contents($f);
		$menuArr=explode("\n",$data);
		if(count($data)>1) if(strlen($data[count($data)-1])==0) unset($data[count($data)-1]);
		if(in_array($link,$menuArr)) {
			return true;
		} else {
			foreach($menuArr as $a) {
				if(strlen($a)>0 && strpos($link,$a)===0) {
					return true;
				}
			}
			if(MASTER_DEBUG_MODE=="true") {
				echo "<div align=center>Link Not Accessible :: <b style='color:maroon;'>$link</b></div>";
				echo "<div id=errorMsgPopup style='display:none'>";
				printArray($menuArr);
				echo "</div>";
			}
			return false;
		}
	}
	function checkBlacklist($site=null) {
		if($site==null) $site=SITENAME;
		$client=$_SERVER["REMOTE_ADDR"];
		$f=ROOT.CACHE_IPLIST_FOLDER."{$site}/blacklist.dat";
		
		if(!file_exists($f)) {
			Security::generateIPListCache("blacklist");
		} elseif((time()-filectime($f))>PERMISSION_CACHE_PERIOD) {
			Security::generateIPListCache("blacklist");
		}
		if(!file_exists($f)) {
			dispErrMessage("Security Inconsistancy Found. Please Contact Admin.");
			exit();
		}
		$data=file_get_contents($f);
		$ipArr=explode("\n",$data);
		if(strlen($ipArr[count($ipArr)-1])==0) unset($ipArr[count($ipArr)-1]);
		if(in_array($client,$ipArr)) {
			return true;
		}
		return false;
	}
}
class Security {
	public static function isBlacklisted($dbLink=null,$site) {
		if($dbLink==null) $dbLink=getSysDBLink();
		//$q="SELECT client, type FROM lgks_blacklist WHERE client='".$userid."' OR client='".."'";
		$q="SELECT count(*) as cnt FROM ".$dbLink->getSysTable('sys_iplist')." WHERE ipaddress='{$_SERVER['REMOTE_ADDR']}' AND allow_type='blacklist' AND (site='*' OR site='$site') AND active='true'";
		$r=$dbLink->executeQuery($q);
		if($r) {
			$d=$dbLink->fetchData($r);
			if(isset($d['cnt']) && $d['cnt']>0) {
				return true;
			}
		}
		return false;
	}
	public static function generateIPListCache($type,$site=null) {
		if($site==null) $site=SITENAME;
		$tbl=_dbtable("sys_iplist",true);
		$f=ROOT.CACHE_IPLIST_FOLDER."{$site}/{$type}.dat";
		if(!is_dir(dirname($f))) {
			mkdir(dirname($f),0777,true);
			chmod(dirname($f),0777);
		}
		if(!is_dir(dirname($f))) {
			return false;
		}
		$sql="SELECT id,ipaddress from $tbl where (site='{$site}' OR site='*') AND allow_type='{$type}'";
		//echo $sql;
		$r=_dbQuery($sql,true);
		$s="";
		if($r) {
			$data=_dbData($r);
			foreach($data as $a) {
				if($a['ipaddress']=="#" || strlen($a['ipaddress'])<=0) continue;
				$s.="{$a['ipaddress']}\n";
			}
		}
		file_put_contents($f,$s);
	}
	public static function generateUserLinksCache($privilege) {
		if(isset($_SESSION['SESS_PRIVILEGE_NAME'])) $priId=$_SESSION['SESS_PRIVILEGE_NAME']; else $priId="Guest";
		$site=SITENAME;
		$tbl=_dbtable("links");
		$sys=false;
		if(getSysDBLink()->getdbName()==getAppsDBLink()->getdbName()) {
			$tbl=_dbtable("admin_links",true);
			$sys=true;
		}
		
		$f=ROOT.CACHE_PERMISSIONS_FOLDER."{$site}/{$privilege}.dat";
		if(!is_dir(dirname($f))) {
			mkdir(dirname($f),0777,true);
			chmod(dirname($f),0777);
		}
		if(!is_dir(dirname($f))) {
			return false;
		}
		
		$sql="SELECT id,link from $tbl where (site='{$site}' OR site='*') AND blocked='false' AND (privilege LIKE '%$priId,%' or privilege='*')";
		//echo $sql;
		$r=_dbQuery($sql,$sys);
		$pArr=array();
		if($r) {
			$data=_dbData($r);
			foreach($data as $a) {
				if($a['link']=="#") continue;
				$lnks=explode("&",$a['link']);
				$link=array();
				if(isset($_REQUEST['site'])) array_push($link,"site=".$_REQUEST['site']); 
				else array_push($link,"site=$site");
				foreach($lnks as $a=>$b) {
					array_push($link,$b);
				}
				$ls=implode("&",$link);
				array_push($pArr,$ls);
			}
		}
		
		$fM=APPROOT."config/menugenerator.json";
		if(file_exists($fM)) {
			$data=file_get_contents($fM);
			if(strlen($data)>2) {
				$arrD=json_decode($data,true);
				if($arrD==null) $arrD=array();
				foreach($arrD as $a=>$b) {
					$b['table']=_dbtable($b['table']);
					$sql="SELECT id FROM {$b['table']} ";
					$where="where (site='{$site}' OR site='*') AND blocked='false' AND (privilege LIKE '%{$priId},%' or privilege='*')";
					$sql=$sql.$where;
					$r=_dbQuery($sql,$sys);
					if($r) {
						$data=_dbData($r);
						foreach($data as $a) {
							$ls=sprintf($b['lnk'],$a['id']);
							array_push($pArr,"site={$site}&{$ls}");
						}
					}
				}
			}
		}
		file_put_contents($f,implode("\n",$pArr));
	}
}
?>
