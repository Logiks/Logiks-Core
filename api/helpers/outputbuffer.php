<?php
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("startBuffer")) {
	function startOPBuffer() {
		$_SESSION["do_gzip_compress"]=false;
		if(getConfig("BUFFER_ENCODING")=="gzip") {
			$phpver 	= phpversion();
			$useragent 	= '';
			$canZip 	= '';
			if(isset($_SERVER['HTTP_USER_AGENT'])) $useragent=$_SERVER['HTTP_USER_AGENT'];
			if(isset($_SERVER['HTTP_ACCEPT_ENCODING'])) $canZip=$_SERVER['HTTP_ACCEPT_ENCODING'];
			
			$gzip_check 	= 0;
			$zlib_check 	= 0;
			$gz_check		= 0;
			$zlibO_check	= 0;
			$sid_check		= 0;
			
			if(strpos($canZip,'gzip') !== false) { $gzip_check = 1; }
			if(extension_loaded( 'zlib')) { $zlib_check = 1; }
			if(function_exists('ob_gzhandler')) { $gz_check = 1; }
			if(ini_get('zlib.output_compression')) { $zlibO_check = 1; }
			if(ini_get('session.use_trans_sid')) { $sid_check = 1; }
			
			if($phpver >= '4.0.4pl1' && ( strpos($useragent,'compatible') !== false || strpos($useragent,'Gecko')	!== false ) ) {
				// Check for gzip header or northon internet securities or session.use_trans_sid
				if ( ( $gzip_check || isset( $_SERVER['---------------']) ) && $zlib_check && $gz_check && !$zlibO_check && !$sid_check ) {
					// You cannot specify additional output handlers if zlib.output_compression is activated here
					ob_start( 'ob_gzhandler' );
					return;
				}
			} elseif ( $phpver > '4.0' ) {
				if ( $gzip_check ) {
					if ( $zlib_check ) {
						$_SESSION["do_gzip_compress"]=true;
						ob_start();
						ob_implicit_flush(0);

						header( 'Content-Encoding: gzip' );
						return;
					}
				}
			}
		}
		ob_start();
	}
	function printOPBuffer() {
		if(getConfig("BUFFER_ENCODING")=="gzip" && $_SESSION["do_gzip_compress"]) {
			$gzip_contents = ob_get_contents();
			ob_end_clean();

			$gzip_size = strlen($gzip_contents);
			$gzip_crc = crc32($gzip_contents);

			$gzip_contents = gzcompress($gzip_contents, 9);
			$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);
			
			$_SESSION["do_gzip_compress"]=false;

			echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
			echo $gzip_contents;
			echo pack('V', $gzip_crc);
			echo pack('V', $gzip_size);
		} else {
			ob_end_flush();
		}
	}
}
?>
