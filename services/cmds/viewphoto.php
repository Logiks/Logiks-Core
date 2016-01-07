<?php
if(!defined('ROOT')) exit('No direct script access allowed');

//http://localhost/projects/logiks3/services/?scmd=viewphoto&type=view&loc=local&image=images/good_code2.png
//http://localhost/projects/logiks3/services/?scmd=viewphoto&type=view&loc=db&dbtbl=crew_photos&image=1

if(isset($_REQUEST['image'])) {
	if(!isset($_REQUEST['loc'])) {
		$_REQUEST['loc']="local";
	}
	if(!isset($_REQUEST['type'])) {
		$type="view";
	} else {
		$type=$_REQUEST['type'];
	}
	if(strtolower($_REQUEST['loc'])=="local") {
		displayLocalImage($_REQUEST['image'],$type);
	} elseif(strtolower($_REQUEST['loc'])=="db") {
		displayDBImage($_REQUEST['image'],$type);
	} else {
		displayLocalImage("images/forbidden.png","view");
	}
} else {
	displayLocalImage("images/warning.png","view");
}
exit();

function getImageType($imgFile) {
	$a=explode(".",$imgFile);
	$type=$a[sizeOf($a)-1];
	return "image/" . $type;
}

function findMedia($name,$pathType="local") {
	if(strlen($name)<=0) return "";
	$paths=array();
	array_push($paths,MEDIA_FOLDER . $name);
	if(defined("APPS_MEDIA_FOLDER")) {
		array_push($paths,BASEPATH . APPS_MEDIA_FOLDER . $name);
	}
	if(defined("APPS_USERDATA_FOLDER")) {
		array_push($paths,BASEPATH . APPS_USERDATA_FOLDER . $name);
	}
	if(defined("SITENAME")) {
		array_push($paths,MEDIA_FOLDER . SITENAME . "/" . $name);
	}
	foreach($paths as $a) {
		if(file_exists(ROOT . $a)) {
			if($pathType=="local") return ROOT . $a;
			elseif($pathType=="web") return SiteLocation . $a;
			else return SiteLocation . $a;
		}
	}
	return $name;
}
function printHeader($filename,$type) {
	$mime=getImageType($filename);
	if($type=="view") {
		header("Content-type: $mime");
		header("Content-Transfer-Encoding: binary\n");
		header("Expires: 0");
		//header('Content-length: '.sizeOf($imgcode));
	} elseif($type=="download") {
		if(strpos(_server('HTTP_USER_AGENT'), "MSIE") !== FALSE) {
			header("Content-type: $mime");
			header("Content-Disposition: attachment; filename=$filename");
			header("Expires: 0");
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			//header("Content-Length: ".strlen($data));
		} else {
			header("Content-type: $mime");
			header("Content-Disposition: attachment; filename=$filename");
			header("Content-Transfer-Encoding: binary");
			header("Expires: 0");
			header('Pragma: no-cache');
			//header("Content-Length: ".strlen($data));
		}
	}
}

function displayLocalImage($imgFile,$type="view") {
	$filename=basename($imgFile);
	$imgFile=findMedia($imgFile);
	if(!file_exists($imgFile)) {
		$imgFile=findMedia("images/notfound/image.png");
	}
	printHeader($filename, $type);
	//$data=file_get_contents($imgFile);
	//echo $data;
	readfile($imgFile);
}
function displayDBImage($imgID,$type="view") {
	$dbtbl="";
	if(isset($_REQUEST['dbtbl'])) {
		$dbtbl=$_REQUEST['dbtbl'];
	} else {
		$dbtbl=_dbTable("photos");
	}
	$sql="SELECT image_type,image_data,image_size FROM $dbtbl WHERE ID=$imgID";
	$result=_db()->executeQuery($sql);
	if($result) {
		if(_db()->recordCount($result)>0) {
			$record=_db()->fetchData($result);
			$ext=str_replace("image/","",$record["image_type"]);
			printHeader("download.$ext",$type);
			echo $record["image_data"];
			exit();
		}
	}
	displayLocalImage("images/warning.png","view");
}
?>
