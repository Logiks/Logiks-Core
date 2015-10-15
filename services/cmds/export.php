<?php
if (!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

//type=view/download/	... mail/publish/ ..... /share/
//src=form/memsql/csv/html ... direct
//format::o/p=html/csv/tsv/xml/

//$_REQUEST['data']::[*optional] O/P data
//$_REQUEST['sqlid']::SQL Fetch From Session w=using $_SESSION

//http://localhost/projects/logiks3/services/?scmd=export&type=view&format=html&src=form
//http://localhost/projects/logiks3/services/?scmd=export&src=memsql&format=xml

if(!isset($_REQUEST['type'])) $_REQUEST['type']="view";
if(!isset($_REQUEST['format'])) $_REQUEST['format']="html";

if(!isset($_REQUEST['src'])) {
	displayLocalImage("images/warning.png","view");
	exit();
}

$format=$_REQUEST['format'];
$type=$_REQUEST['type'];
$out="";
$arr=array();
$style="";

if($_REQUEST['src']=="form") {
	$arr=parseForm($_POST);
	$out=exportArray($arr,$format,"dataform",
			".dataform {width:800px;height:auto !important;margin:auto;} .dataform tr td:nth-child(2) {width:200px;}");
} elseif($_REQUEST['src']=="memsql") {
	if(isset($_REQUEST['sqlid'])) {
		if(isset($_SESSION[$_REQUEST['sqlid']])) {
			$sql=$_SESSION[$_REQUEST['sqlid']];
			$arr=parseSQL($sql);
			$out=exportArray($arr,$format,"datatable",
				".datatable {margin:auto;width:100%;} .datatable tr td {min-width:75px;}");
		} else {
			displayLocalImage("images/warning.png","view");
			exit();
		}
	} else {
		displayLocalImage("images/warning.png","view");
		exit();
	}
} elseif($_REQUEST['src']=="csv") {
	if(isset($_REQUEST['data'])) {
		$arr=parseCSV($_REQUEST['data']);
		$out=exportArray($arr,$format);
	} else {
		displayLocalImage("images/warning.png","view");
		exit();
	}
} elseif($_REQUEST['src']=="html") {
	if(isset($_REQUEST['data'])) {
		$out=$_REQUEST['data'];
	} else {
		displayLocalImage("images/warning.png","view");
		exit();
	}
	if($format=="pdf") {
		$out=exportPDF($out);
	} else {
		$format="html";
	}
} else {
	if(isset($_REQUEST['data'])) {
		$out=$_REQUEST['data'];
	} else {
		displayLocalImage("images/warning.png","view");
		exit();
	}
}

if($type=="view" || $type=="download") {
	printHeader($type,$format);
	echo $out;
} else {
	displayLocalImage("images/warning.png","view");
}
exit();
//----------------------------------------------------------------------
//Export Functions
function parseForm($arr) {
	$out=array();
	foreach($arr as $a=>$b) {
		$out[sizeOf($out)]=array("$a","$b");
	}
	return $out;
}
function parseSQL($sql) {
	$result=_db()->executeQuery($sql);
	$out=array();
	if($result) {
		if(_db()->recordCount($result)>0) {			
			while($record=_db()->fetchData($result,'assoc')) {
				$n=sizeOf($out);
				$out[$n]=array();
				foreach($record as $a=>$b) {
					$out[$n][$a]=$b;
				}
			}
		}
	}
	return $out;
}
function parseCSV($data,$separator=",") {
	$data=explode("\n",$data);
	$out=array();
	foreach($data as $a=>$b) {
		$d=explode($separator,$b);
		if(strlen($b)>0 && sizeOf($d)>0) {
			$n=sizeOf($out);
			$out[$n]=array();
			foreach($d as $o=>$p) {
				$out[$n][sizeOf($out[$n])]=$p;
			}
		}
	}
	return $out;
}

function exportArray($arr,$format,$bClass="",$style="") {
	if($format=="html") {
		$css="<style>";
		$css.=file_get_contents(SiteLocation."misc/themes/default/dataexport.css");
		$css.=$style;
		$css.="</style>";
		$css=str_replace("\n","",$css);
		$css=str_replace("	"," ",$css);
		$css=str_replace("  "," ",$css);
		$s=$css."\n\n".createHTMLForArray($arr,"Form Export","Thank You",$bClass);
		return $s;
	} elseif($format=="csv") {
		return createCSVForArray($arr,',');
	} elseif($format=="tsv") {
		return createCSVForArray($arr,'\t');
	} elseif($format=="xml") {		
		return createXMLForArray($arr);
	} elseif($format=="pdf") {		
		return "Exporting Data Into PDF Format Is Not Yet Supported";
	} else {
		return "Exporting Data To '$format' Format Not Supported";
	}
}
function exportPDF($html) {
	
}
//Other Functions
function printHeader($ftype, $format, $fname="file") {
	if(strtolower($ftype)=="download") {
		$cntrl=true;
	} else {
		$cntrl=false;
	}
	$format=strtolower($format);
	
	loadHelpers("mimes");
	
	header("Cache-Control: private");
	header("Pragma: no-cache");
	header("Content-Type: ".getMimeTypeFor($format));
	
	if($cntrl) {
		header("Content-Transfer-Encoding: binary");
		header("Content-Description: File Transfer");		
		header("Content-Disposition: attachment; filename=$fname.$format");
		header("Content-Type: application/zip");
	}
}
function createHTMLForArray($data,$title="",$footer="", $divClass="") {
	$body="";
	$body.="<div class='datatable $divClass' align=center>";
	$body.="<table class=form align='center' width='100%' border='0' cellpadding='0' cellspacing='0' style='align:center'>";
	$body.="<caption>$title</caption>";
	$x=0;
	foreach($data as $a=>$b) {
		if($x%2==0)
			$body .= "<tr class='columnName even'>";
		else
			$body .= "<tr class='columnName odd'>";
		$body .= "<td width=50px>$x</td>";
		$y=0;
		foreach($b as $q=>$w) {
			if($y==0) {
				$w=str_replace("_"," ",$w);
				$w=ucwords($w);				
			}
			$body .= "<td name='$q'>$w</td>";
			$y++;
		}		
		$body.="</tr>";
		$x++;
	}
	$body.="</table>";
	$body.="<p class='footer'>$footer</p></div></div>";
	$body.="</div>";
	return $body;
}
function createCSVForArray($data,$separator) {
	$body="";
	foreach($data as $a=>$b) {
		foreach($b as $q=>$w) {
			$body.=$w.$separator;
		}
		$body.="\n";
	}
	return $body;
}
function createXMLForArray($data,$root='data') {
	$body="<?xml version='1.0' encoding='UTF-8'?>\n\n";
	$body.="<$root>\n";
	foreach($data as $a=>$b) {
		$body.="\t<row id='$a'>\n";
		foreach($b as $q=>$w) {
			$body.= "\t\t<cell id='$q'>". $w."</cell>\n";
		}
		$body.="\t</row>\n";
	}
	$body.="</$root>\n";
	return $body;
}
function displayLocalImage($imgFile,$type="view") {
	$imgFile=SiteLocation."media/".$imgFile;
	if(!file_exists($imgFile)) {
		$imgFile=SiteLocation."media/images/notfound/process.png";
	}
	$ext=explode(".",$imgFile);
	$ext=$ext[sizeOf($ext)-1];
	header("Cache-Control: private");
	header("Pragma: no-cache");
	header("Content-Type: image/$ext");
	$data=file_get_contents($imgFile);
	echo $data;
}
?>
