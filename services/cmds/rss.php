<?php
if(!defined('ROOT')) exit('No direct script access allowed');

$rsstable="rss";
if(isset($_REQUEST['src'])) {
	$rsstable=$_REQUEST['src'];
}

if(isset($_REQUEST['rss'])) {
	$rssid=$_REQUEST['rss'];
	$tbl=_dbtable($rsstable);
	$temp_FULL_MEDIA_PATH=getConfig("FULL_MEDIA_PATH");
	setConfig("FULL_MEDIA_PATH","true");
	$rss=RSSGen::generateFromDB($tbl,$rssid);
	setConfig("FULL_MEDIA_PATH",$temp_FULL_MEDIA_PATH);
	if(strlen($rss)>0) {
		RSSGen::printRSSHeader();
		echo $rss;
	}
	exit();
} elseif(isset($_REQUEST['list'])) {
	if(!isset($_REQUEST['format'])) {
		$_REQUEST['format']="json";
	}
	$tbl=_dbtable($rsstable);
	$list=RSSGen::listFeeds($tbl);
	if(count($list)>0) {
		foreach($list as $a=>$b) {
			$list[$a]['link']=SiteLocation."services/?scmd=rss&rss={$b['rssid']}";
		}
		if($_REQUEST['format']=="json") {
			echo json_encode($list);
		} elseif($_REQUEST['format']=="table") {
			$s="<table width=100% cellpadding=2 cellspacing=0 border=0>";
			foreach($list as $a) {
				$s.="<tr>";
				foreach($a as $m=>$n) {
					$s.="<td name='$m'>$n</td>";
				}
				$s.="</tr>";
			}
			$s.="</table>";
			echo $s;
		} elseif($_REQUEST['format']=="select") {
			$s="";
			foreach($list as $a) {
				$s.="<option value='{$a['link']}' title='{$a['author']}'>{$a['title']} [{$a['category']}]</option>\n";
			}
			echo $s;
		} elseif($_REQUEST['format']=="list") {
			$s="";
			foreach($list as $a) {
				$s.="<li title='{$a['author']}'><a href='{$a['link']}'>{$a['title']} [{$a['category']}]</a></li>\n";
			}
			echo $s;
		}
	}
	exit();
}
RSSGen::printRSSHeader();
echo RSSGen::generateErrorRSS("Wrong Command Structure","Please check the service command for errors.");
exit();
?>
