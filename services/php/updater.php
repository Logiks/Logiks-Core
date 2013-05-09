<?php
if (!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();
isAdminSite();

$noUpdatesMsg="<tr align=center valign=center><td colspan=30><h1 class=okIcon>Congratulations, No Updates Found <i style='color:maroon;'>aka</i> You Are On Latest CodeBase.</h1></td></tr>";
$errMsg="<tr align=center valign=center><td colspan=30><h1 class=errIcon>%s</h1></td></tr>";
$notAllowedMsg="<tr align=center valign=center><td colspan=30><h1 class=forbiddenIcon>Sorry, Updates Forbidden.</h1></td></tr>";

loadHelpers("files");
if(!defined("updateCMD")) {
	LoadConfigFile(ROOT . "config/olks.cfg");
}

if(!defined("updateCMD") || strlen(updateCMD)<=0) {
	echo sprintf($errMsg,"Update Command Link Not Defined ...");
	exit();
}

$updateDir=ROOT.UPDATES_FOLDER;
$cacheDir=ROOT.CACHE_FOLDER."updates/";
$updateLink=updateCMD."format=xml";
$lastUpdates=$updateDir."lastUpdates.dat";

if(!file_exists($updateDir)) {
	mkdirs($updateDir);	
}
if(!file_exists($cacheDir)) {
	mkdirs($cacheDir);
}

if(!file_exists($lastUpdates)) {
	createLastUpdatesFile($lastUpdates);
}

if(isset($_REQUEST["action"])) {
	if($_REQUEST["action"]=="find") {
		$cols="";
		$forApps="";
		
		if(isset($_REQUEST['cols'])) $cols=$_REQUEST['cols'];
		if(isset($_REQUEST['apps'])) $forApps=$_REQUEST['apps'];
		
		$cols=explode(",",$cols);
		
		//Select All Apps/Plugins/Themes For Updates
		if($forApps=="*") {
			$forApps="Core Framework";
		} else {
			
		}
		//loadUpdates();
		
		//ToDo :: Fetch Update XMLs
		
		//echo printUpdate(123,567,array("package"=>"testing","appEngine"=>"Core","level"=>1),$cols);
		//echo printUpdate(123,234,array("package"=>"testing23","appEngine"=>"Core","level"=>1),$cols);
		echo sprintf($errMsg,"Error Finding Updates :: $forApps");
		//echo $noUpdatesMsg;
	} elseif($_REQUEST["action"]=="install") {
		if(isset($_REQUEST['uids'])) $toUpdateList=$_REQUEST['uids']; else $toUpdateList="";
		$toUpdateList=explode(",",$toUpdateList);
		
		//echo "<tr><td colspan=10>$toUpdateList</td></tr>";
		echo $noUpdatesMsg;
	} elseif($_REQUEST["action"]=="legends") {
		loadModuleLib("updater","legend");
	}
} else {
	echo sprintf($errMsg,"Wrong Update Command");
}
exit();
function loadUpdates() {
	$root = $doc->add_root('members');
	$member = $root->new_child('member','');
	
	$member->new_child('lastName','John');
	$member->new_child('firstName','Adams');
	$member->new_child('contribution','3400');
	
	$member = $root->new_child('member','');
	
	$member->new_child('lastName','Debra');
	$member->new_child('firstName','Hones');
	$member->new_child('contribution','2400');
	
	$member = $root->new_child('member','');
	
	$member->new_child('lastName','Jake');
	$member->new_child('firstName','Tudor');
	$member->new_child('contribution','1200');
	
	$fp = @fopen($updateDir.'members.xml','w');
	if(!$fp) {
	    die('Error cannot create XML file');
	}
	fwrite($fp,$doc->dumpmem());
	fclose($fp);
  
}
function printUpdate($uid, $buildID, $arr, $cols) {
	if(count($arr)<=0) return "";
	$s="<tr class='update'><td class='checkbox' align=center><input class='rowselector' uid='$uid' buildID='$buildID' type=checkbox /></td>";
	foreach($cols as $a) {
		if(isset($arr[$a])) $v=$arr[$a]; else $v="";
		$cls="";
		$attr="";
		
		if(strpos("##".strtolower($a),"level")>=2) {
			$attr="align=center ";
			$v="<b>$v</b>";
		} elseif(strpos("##".strtolower($a),"size")>=2) {
			if(is_numeric($v)) {
				$v=getFileSizeInString($v);				
			}
		} else {
			$pr=2;
			$pr+=strpos("##".strtolower($a),"date")>=2?1:0;
			$pr+=strpos("##".strtolower($a),"time")>=2?1:0;			
			$pr+=strpos("##".strtolower($a),"id")>=2?1:0;
			$pr+=strpos("##".strtolower($a),"version")>=2?1:0;
			$pr+=strpos("##".strtolower($a),"count")>=2?1:0;
			
			if($pr>2) $attr="align=center ";
			
			$v=ucwords($v);
		}		
		$s.="<td col='$a' class='$cls' $attr >$v</td>";
	}
	$s.="</tr>";
	return $s;
}
function createLastUpdatesFile($lastUpdates,$lastUpdateID="0000000000000000",$hashFunc="sha1") {	
	$hashData="UPDATES_".date("Ymd-His").microtime().SITENAME;
	
	if($hashFunc=="md5") $hashData=md5($hashData);
	elseif($hashFunc=="sha1") $hashData=sha1($hashData);
	
	$s="$lastUpdateID\n".date("Ymd-His")."\n$hashFunc\n$hashData\n".SITENAME;
	file_put_contents($lastUpdates,$s);
}
function uploadFileToDir($name, $path) {
	$maxFileSize = getConfig("MAX_UPLOAD_FILE_SIZE");
	
	$fileType = $_FILES[$name]['type'];
	$fileSize = $_FILES[$name]['size'];
	$fileName = $_FILES[$name]['name'];
	$tmpName  = $_FILES[$name]['tmp_name'];
	
	$target_path= $path . strtolower($fileName);
	
	if(strpos($target_path,".php")!=strlen($target_path)-4) {
		echo "Please Upload PHP Files only";
		return;
	}
	
	if ($fileSize<$maxFileSize) {
		if (!move_uploaded_file($tmpName,$target_path)) {
			echo "Error Moving script : $fileName";
		} else {
			chmod($target_path,0777);
			echo "Uploaded Script $fileName";
		}
	} else {
		echo "Error uploading script : Please check the file size";
	}
}
?>
