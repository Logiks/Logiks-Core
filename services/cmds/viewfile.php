<?php
if(!defined('ROOT')) exit('No direct script access allowed');

loadHelpers("mimes");

//http://localhost/projects/logiks3/services/?scmd=viewfile&type=view&loc=local&file=z.pdf
//http://localhost/projects/logiks3/services/?scmd=viewfile&type=view&loc=dbfile&dbtbl=do_files&file=1
//http://localhost/projects/logiks3/services/?scmd=viewfile&type=view&loc=dbdoc&dbtbl=doc_crew&file=1

if(isset($_REQUEST['file'])) {
	if(!isset($_REQUEST['loc'])) {
		$_REQUEST['loc']="local";
	}
	if(!isset($_REQUEST['type'])) {
		$type="view";
	} else {
		$type=$_REQUEST['type'];
	}
	if(strtolower($_REQUEST['loc'])=="local") {
		$filename=findMedia($_REQUEST['file']);
		if(file_exists($filename)) {
			if(is_readable($filename)) {
				printHeader($filename, $type);
				printVFile($filename);
				exit();
			} else {
				displayLocalImage("images/forbidden.png","view");
			}
		} else {
			displayLocalImage("images/warning.png","view");
		}
	} elseif(strtolower($_REQUEST['loc'])=="dbdoc") {
		$dbtbl="";
		$bpath="";
		if(isset($_REQUEST['dbtbl'])) {
			$dbtbl=$_REQUEST['dbtbl'];
		} else {
			$dbtbl=_dbTable("docs");
		}
		if(isset($_REQUEST['bpath'])) {
			$bpath=$_REQUEST['bpath']."/";
			$bpath=str_replace("//","/",$bpath);
		} else {
			$bpath="";
		}
		$sql="SELECT doclink FROM $dbtbl WHERE ID=".$_REQUEST['file'];
		
		$result=_db()->executeQuery($sql);
		if($result) {
			if(_db()->recordCount($result)>0) {
				$record=_db()->fetchData($result);
				$doc=$record["doclink"];
				$doc=str_replace("./","",$doc);
				$doc=$bpath.$doc;
				$doc=findMedia($doc);				
				if(is_readable($doc)) {
					printHeader($doc,$type);
					printVFile($doc);
					exit();
				} else {
					displayLocalImage("images/forbidden.png","view");
					exit();
				}				
			}
		}
		displayLocalImage("images/warning.png","view");
	} elseif(strtolower($_REQUEST['loc'])=="dbfile") {
		$dbtbl="";
		if(isset($_REQUEST['dbtbl'])) {
			$dbtbl=$_REQUEST['dbtbl'];
		} else {
			$dbtbl=_dbTable("files");
		}
		$sql="SELECT file_name,file_type,file_data,file_size FROM $dbtbl WHERE ID=".$_REQUEST['file'];
		$result=_db()->executeQuery($sql);
		if($result) {
			if(_db()->recordCount($result)>0) {				
				$record=_db()->fetchData($result);
				$darr=explode(".",$record["file_name"]);
				$ext=$darr[sizeOf($darr)-1];
				printHeader($record["file_name"],$type);
				echo $record["file_data"];
				exit();
			}
		}
		displayLocalImage("images/warning.png","view");
	} else {
		displayLocalImage("images/forbidden.png","view");
	}
} else {
	displayLocalImage("images/warning.png","view");
}
exit();
function displayLocalImage($imgFile,$type="view") {
	$filename=basename($imgFile);
	$imgFile=findMedia($imgFile);
	if(!file_exists($imgFile)) {
		$imgFile=findMedia("images/notfound/file.png");
	}
	printHeader($filename, $type);
	printVFile($imgFile);	
}

function printHeader($filename,$type) {
	$mime=getMimeTypeForFile($filename);
	$ext=explode(".",$filename);
	$ext=$ext[sizeOf($ext)-1];
	$filename=md5($filename).".$ext";
	if($type=="view") {
		header("Content-type: $mime");
		header("Content-Transfer-Encoding: binary\n");
		header("Expires: 0");
		header("Content-Disposition: filename=$filename");
		//header('Content-length: '.sizeOf($imgcode));
	} elseif($type=="download") {
		if(strpos(_server('HTTP_USER_AGENT'), "MSIE") !== FALSE) {
			header("Content-type: $mime");
			header("Content-Disposition: attachment; filename=$filename");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
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
	if(defined("APPS_CACHE_FOLDER")) {
		array_push($paths,BASEPATH . APPS_CACHE_FOLDER . $name);
	}
	if(defined("APPS_CACHE_FOLDER")) {
		array_push($paths,BASEPATH . $name);
	}
	array_push($paths,MEDIA_FOLDER . SITENAME . "/" . $name);
	array_push($paths,CACHE_FOLDER . $name);
	array_push($paths,$name);
	
	foreach($paths as $a) {
		if(file_exists(ROOT . $a)) {
			if($pathType=="local") return ROOT . $a;
			elseif($pathType=="web") return SiteLocation . $a;
			else return SiteLocation . $a;
		}
	}	
	return $name;
}
function printVFile($f) {
	readfile($f);
	//$data=file_get_contents($imgFile);
	//echo $data;
}
?>
