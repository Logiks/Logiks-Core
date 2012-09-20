<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("transferFileHTTPToLocal")) {
	function transferFileHTTPToLocal($src,$dest="",$autoExtension=false) {
		if(strlen($dest)<=0) $dest=(ROOT . CACHE_FOLDER);
		if(strpos($src,"http://")!=0 && strpos($src,"https://")!=0) return false;
		
		$fpath=$dest;
		if($autoExtension) {
			$arr=explode("/",$src);
			$ext=$arr[sizeOf($arr)-1];
			$fpath=$dest.$ext;
		}
		
		if (!function_exists('curl_init')){
			//Use File_get_contents
			$fh1=fopen($src, "rb");
			if ($fh1) {
				$fh=fopen($fpath,"w");
				if($fh) {
					while(!feof($fh1)){
						 $contents = fread($fh1, 8192);
						 fwrite($fh,$contents);
					}
					return $fpath;
				} else {
					return false;
				}
			}
			return false;
		} else {
			//Use CURL
			$site=SiteLocation.$_SERVER["SCRIPT_NAME"];
			$site=str_replace("http://","h1",$site);
			$site=str_replace("https://","h2",$site);
			$site=str_replace("//","/",$site);
			$site=str_replace("h1","http://",$site);
			$site=str_replace("h2","https://",$site);
			
			$ch = curl_init();
			// Set URL to download
			curl_setopt($ch, CURLOPT_URL, $src);
			// Set a referer
			curl_setopt($ch, CURLOPT_REFERER, $site);
			// User agent
			curl_setopt($ch, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
			// Include header in result? (0 = yes, 1 = no)
			curl_setopt($ch, CURLOPT_HEADER, 0);
			// Should cURL return or print out the data? (true = return, false = print)
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Timeout in seconds
			curl_setopt($ch, CURLOPT_TIMEOUT, 100);
			// Download the given URL, and return output
			$data = curl_exec($ch);
			// Close the cURL resource, and free system resources
			curl_close($ch);
			file_put_contents($fpath,$data);
			return $fpath;
		}
		return false;
	}
	
	function transferFileFTPToLocal($ftp_location, $ftp_login, $ftp_password, $src,$dest="") {
		if(strlen($dest)<=0) $dest=(ROOT . CACHE_FOLDER);
		
		$conn_id=ftp_connect($ftp_location);
		$login=ftp_login($conn_id,$ftp_login,$ftp_password);

		if((!$conn_id) || (!$login)){
			echo "FTP connection has failed!";
			return false;
		}
		
		$arr=explode("/",$src);
		$fname=$arr[sizeOf($arr)-1];
		
		$remote_file=$src;
		$local_file=$dest . $fname;
		
		$fh=fopen($local_file,'w');
		if($fh) {			
			if (ftp_fget($conn_id, $fh, $remote_file, FTP_BINARY, 0)) {
				fclose($fh);
				ftp_close($conn_id);
				return $local_file;
			} else {
				echo "There was a problem while downloading $remote_file to $local_file\n";
				fclose($fh);
				ftp_close($conn_id);
				return false;
			}
		}
		echo "Destiny Not Writtable.";
		return false;
	}
	
	function transferFileLocalToFTP($ftp_location, $ftp_login, $ftp_password, $src,$dest="") {
		if(strlen($dest)<=0) $dest="/";
		
		$conn_id=ftp_connect($ftp_location);
		$login=ftp_login($conn_id,$ftp_login,$ftp_password);

		if((!$conn_id) || (!$login)){
			echo "FTP connection has failed!";
			return false;
		}
		
		$arr=explode("/",$src);
		$fname=$arr[sizeOf($arr)-1];
		
		$remote_file=$dest . $fname;
		
		$fh=fopen($src,'r');
		if($fh) {
			if (ftp_fput($conn_id, $remote_file, $fh, FTP_BINARY, 0)) {
				fclose($fh);
				ftp_close($conn_id);
				return $remote_file;
			} else {
				echo "There was a problem while Uploading $remote_file to $src\n";
				fclose($fh);
				ftp_close($conn_id);
				return false;
			}
		}
		echo "Source Not Readable.";
		return false;
	}
}
?>
