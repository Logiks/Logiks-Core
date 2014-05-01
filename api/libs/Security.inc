<?php
/*
 * This Class is meant to contain all security related functions. This is an extraction
 * of functions from security.php.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 05/04/2014
 * Version: 1.0
 */
class Security {
	public static function isLinkRedirected($link,$site=null) {
		if($site==null) $site=SITENAME;
		if($link==null) {
			$link=SiteLocation.$_SERVER['REQUEST_PATH'];
			if(strlen($_SERVER['QUERY_STRING'])>0) $link.="?".$_SERVER['QUERY_STRING'];
			if(!isset($_REQUEST['site'])) {
				$link.="&site=".SITENAME;
			}
		}
		$link=urldecode($link);
		$linkO=$link;
		$link=str_replace("##".SiteLocation, "//", "##".$link);
		
		//Redirection code from database into cache and then checking for faster results
		$tbl=_dbtable("sys_redirects",true);
		$sql=_db(true)->_selectQ($tbl,"*",array(
				"blocked"=>"false",
			))." AND (site='*' || site='$site')";
		$sql.="AND (".
				"(link_from = '$linkO' AND redirect_rule='exact') OR".//For Exact URL Match
				"(link_from = '$link' AND redirect_rule='exact') OR".//For Exact Match
				"(substr('$link',1,length(link_from))=link_from AND redirect_rule='parent')".//For Tree Parent Match
			")";
		$r=_dbQuery($sql,true);
		$linkList=_dbData($r);
		_dbFree($r);
		if(count($linkList)>0) {
			$linkList=$linkList[0];

			global $error_codes;
			$errArr=array();
			if(isset($error_codes[$linkList['redirect_type']])) {
				$errArr=$error_codes[$linkList['redirect_type']];
			} else {
				$linkList['redirect_type']=301;
				$errArr=$error_codes[301];
			}
			if(!isset($linkList['redirect_msg']) || strlen($linkList['redirect_msg'])<=0) {
				$linkList['redirect_msg']=$errArr[1];
			}

			if(substr($linkList['link_to'], 0,2)=="//") {
				$linkList['link_to']=SiteLocation.substr($linkList['link_to'], 2);
			} elseif(substr($linkList['link_to'], 0,7)=="http://" ||
				substr($linkList['link_to'], 0,8)=="https://" ||
				substr($linkList['link_to'], 0,6)=="ftp://") {
				//$linkList['link_to']=$linkList['link_to'];
			} elseif(substr($linkList['link_to'], 0,1)=="/") {
				$linkList['link_to']=SiteLocation.substr($linkList['link_to'], 1);
			} else {
				$linkList['link_to']=SiteLocation.$linkList['link_to'];
			}
			//echo $linkList['link_to'];
			
			header("HTTP/1.1 {$linkList['redirect_type']} {$errArr[0]}");
			redirectTo($linkList['link_to'],$linkList['redirect_msg']);
		}
		//printArray($linkList);
		//exit($sql);
		return false;
	}
	public static function isBlacklisted($dbLink=null,$site=null) {
		if($dbLink==null) $dbLink=getSysDBLink();
		if($site==null) $site=SITENAME;
		//$q="SELECT client, type FROM lgks_blacklist WHERE client='".$userid."' OR client='".."'";
		$q="SELECT count(*) as cnt FROM "._dbtable("sys_iplist",true)." WHERE ipaddress='{$_SERVER['REMOTE_ADDR']}' AND allow_type='blacklist' AND (site='*' OR site='$site') AND active='true'";
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
	public static function generateUserLinksFile($pridid) {
		$privilege=md5($pridid.$_SERVER['HTTP_HOST'].SITENAME);
		$f=ROOT.CACHE_PERMISSIONS_FOLDER."{$site}/{$privilege}.dat";
		return $f;
	}
	public static function generateUserLinksCache($pridid) {
		$site=SITENAME;

		$f=Security::generateUserLinksFile($pridid);
		
		
		$sys=false;
		if(isAdminSite(false)) {
			$tbl=_dbtable("admin_links",true);
			$sys=true;
		} else {
			$tbl=_dbtable("links");
		}
		
		if(!is_dir(dirname($f))) {
			mkdir(dirname($f),0777,true);
			chmod(dirname($f),0777);
		}
		if(!is_dir(dirname($f))) {
			return false;
		}
		if($pridid==-1) $pridid="guest";
		$sql="SELECT id,link from $tbl where (site='{$site}' OR site='*') AND blocked='false' AND (find_in_set('$pridid',privilege) OR privilege='*')";
		
		$r=_dbQuery($sql,$sys);
		$pArr=array();
		if($r) {
			$data=_dbData($r);
			foreach($data as $a) {
				if($a['link']=="#" || $a['link']=="##") continue;
				if(substr($a['link'],0,8)=="https://" ||
					substr($a['link'],0,7)=="http://") {
					array_push($pArr,$a['link']);
					continue;
				}
				if(!(strpos($a['link'],"page=")===0)) {
					$a['link']="page=".$a['link'];
				}
				$lnks=explode("&",$a['link']);
				$link=array();
				//if(isset($_REQUEST['site'])) array_push($link,"site=".$_REQUEST['site']);
				//else array_push($link,"site=$site");
				
				foreach($lnks as $a=>$b) {
					if(strpos($b,"=")===false) array_push($link,$b);
					else array_push($link,substr($b,strpos($b,"=")+1));
				}
				$ls=SiteLocation.SITENAME."/".implode("/",$link);
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
					$where="where (site='{$site}' OR site='*') AND blocked='false' AND (find_in_set('$pridid',privilege) OR privilege='*')";
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