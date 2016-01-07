<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("printContent")) {
	function loadContent($refID,$category="",$silent=false,$autoCreate=false) {
		$sql="SELECT title,category,text,blocked FROM "._dbTable("contents")." WHERE (ID='$refID' OR reflink='$refID')";
		if(strlen($category)>0) $sql.=" AND category='$category'";
		$sql.=" AND (site='*' OR site='".SITENAME."')";
		$rs=_dbQuery($sql);
		$data=_dbData($rs);
		_dbFree($rs);
		$text="";
		if(count($data)>0) {
			if($data[0]['blocked']=="false") {
				return array(
						"title"=>$data[0]['title'],
						"category"=>$data[0]['category'],
						"text"=>$data[0]['text'],
					);
			}
		}
		return array(
				"title"=>"Content Not Available",
				"category"=>$category,
				"text"=>"Page Content Is Not Available",
			);
	}
	function printContent($refID,$category="",$silent=false,$autoCreate=false) {
		$sql="SELECT title,category,text,blocked FROM "._dbTable("contents")." WHERE (ID='$refID' OR reflink='$refID')";
		if(strlen($category)>0) $sql.=" AND category='$category'";
		$sql.=" AND (site='*' OR site='".SITENAME."')";
		$rs=_dbQuery($sql);
		$data=_dbData($rs);
		_dbFree($rs);
		$text="";
		if(count($data)>0) {
			if($data[0]['blocked']=="false")
				$text=$data[0]['text'];
			else {
				if(!$silent) {
					echo "<div class=divError>";
					dispErrMessage("Page Content Currently Not Available For <i class='text2'>$refID</i><br/><br/>Visit Us Again ...","Not Available!","400","notfound/file.png");
					echo "</div>";
				}
			}

			if(strlen($text)>0) {
				echo $text;
				return $data[0]['title'];
			} else {
				if(!$silent) {
					echo "<div class=divError>";
					dispErrMessage("Page Content Not Found For <i class='text2'>$refID</i>","Content Not Found OR Missing !","404","notfound/file.png");
					echo "</div>";
				}
				return false;
			}
		} elseif($autoCreate) {
			$date=date("Y-m-d");
			$cols="id,reflink,title,category,text,blocked,site,userid,doc,doe";
			$vals="0,'$refID','".toTitle($refID)."','{$category}','','false','".SITENAME."','auto','$date','$date'";
			$sql="INSERT INTO "._dbTable("contents")." ($cols) VALUES ($vals)";
			_dbQuery($sql);
			return true;
		}
		return false;
	}
}
?>
