<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("unzipFile")) {
	function unzipFile($srcZip, $destDir="") {
		if(strlen($destDir)<=0) $destDir=(ROOT . CACHE_FOLDER._randomid("zip_",true)."/");
		if(!file_exists($srcZip)) {
			echo "Source Zip File Not Found.";
			return false;
		}
		
		$zip = zip_open($srcZip);
		if($zip) {
			while ($entry = zip_read($zip)) {
				$entry_name = zip_entry_name($entry);
				// only proceed if the file is not 0 bytes long
				if (zip_entry_filesize($entry)) {
					$dir = dirname($entry_name);
					// make all necessary directories in the file's path
					if(!is_dir($destDir.$dir)) { mkdir($destDir.$dir,0777,true); chmod($destDir.$dir,0777); }
					$file = basename($entry_name);
					if (zip_entry_open($zip,$entry)) {
						if ($fh = fopen($destDir.$dir.'/'.$file,'w')) {
							// write the entire file
							fwrite($fh,zip_entry_read($entry,zip_entry_filesize($entry)));
							   // or error_log("can't write: $php_errormsg");
							fclose($fh); //or error_log("can't close: $php_errormsg");
							chmod($destDir.$dir.'/'.$file,0777);
						} else {
							//error_log("can't open $dir/$file: $php_errormsg");
						}
						zip_entry_close($entry);
					} else {
						//error_log("can't open entry $entry_name: $php_errormsg");
					}
				}
			}
			zip_close($zip);
			return $destDir;
		}
		echo "Failed To Open Zip File";
		return false;
	}
	function zipFolder($src,$dst) {
		if (!extension_loaded('zip') || !file_exists($src)) {
			return false;
		}

		if(substr($src,-1)==='/') {$src=substr($src,0,-1);}
		if(substr($dst,-1)==='/') {$dst=substr($dst,0,-1);}
		$path=strlen(dirname($src).'/');
		//@unlink($dst);
		
		$zip = new ZipArchive;
		$res = $zip->open($dst, ZipArchive::CREATE);
		if($res !== TRUE){
			echo 'Error: Unable to create zip file';
			return false;
		}
		if(is_file($src)) {		
			$zip->addFile($src,substr($src,$path));
		}
		else {
			if(!is_dir($src)){
				 $zip->close();
				 //@unlink($dst);
				 echo 'Error: File not found';
				 return false;
			}
			recurseZip($src,$zip,$path);
		}
		return $zip->close(); 
	}
	function recurseZip($src,&$zip,$path) {
		$dir = opendir($src);
		while(false !== ( $file = readdir($dir)) ) {
			if (( $file != '.' ) && ( $file != '..' )) {
				if(is_readable($src."/".$file)){
					if ( is_dir($src . '/' . $file) ) {
						recurseZip($src . '/' . $file,$zip,$path);
					} else {					 
						$zip->addFile($src . '/' . $file,substr($src . '/' . $file,$path));
						//echo $src . '/' . $file."<br>";
					}
				} else{
					echo "Nonreadable :".$src."/".$file."<br>";
				}
				
			}
		}
		closedir($dir);
	}
}
?>
