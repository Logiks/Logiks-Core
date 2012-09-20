<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if (!function_exists('startsWith')) {
	function forceDownloadFile($filename = '') {
		if ($filename == '') {
			return FALSE;
		}
		if(!file_exists($filename)) {
			if(file_exists(ROOT.$filename)) {
				$filename=ROOT.$filename;
			} else {
				return FALSE;
			}
		}
		$extension="*";
		if (strpos($filename, '.')>0) {
			$x = explode('.', $filename);
			$extension = end($x);
		}
		
		/*if (!isset($mimes[$extension]))	{
			$mime = 'application/octet-stream';
		} else {
			$mime = (is_array($mimes[$extension])) ? $mimes[$extension][0] : $mimes[$extension];
		}*/
		
		$data=file_get_contents($filename);
		forceDownloadData($data,$extension,basename($filename));
	}
	
	function forceDownloadData($data = '',$extension="*", $filename="download") {
		include(ROOT.'config/mimes.php');
		
		$mime = getMimeTypeFor($extension);
		
		if($extension=="*") $extension="";
		
		if(!(substr($filename,strlen($filename)-strlen($extension))==$extension)) {
			$filename.="." . $extension;
		}
		
		if (strpos($_SERVER['HTTP_USER_AGENT'], "MSIE") !== FALSE) {
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header("Content-Transfer-Encoding: binary");
			header('Pragma: public');
			header("Content-Length: ".strlen($data));
		} else {
			header('Content-Type: "'.$mime.'"');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header("Content-Transfer-Encoding: binary");
			header('Expires: 0');
			header('Pragma: no-cache');
			header("Content-Length: ".strlen($data));
		}
		
		exit($data);
	}
}
?>
