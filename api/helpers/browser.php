<?php
/*
 * This contains functions for storing all the additional utilties that are used
 * through out the Framework
 *  
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("checkBrowser")) {
	function checkBrowser() {
			if(!_server('HTTP_USER_AGENT')) {
				return array(
				"browser" => "",
				"version" => "",
				"platform" => "",
				"userAgent" => "");
			}
			$browsers = "mozilla msie gecko firefox ";
			$browsers.= "konqueror safari netscape navigator ";
			$browsers.= "opera mosaic lynx amaya omniweb chrome";
			$browsers = explode(" ", $browsers);
			$userAgent = strtolower(_server('HTTP_USER_AGENT'));
			$l = count($browsers);
			for ($i=0; $i<$l; $i++) {
				$browser = $browsers[$i];
				$n = stristr($userAgent, $browser);
				if(strlen($n)>0) {
					  $version = "";
					  $navigator = $browser;
					  $j=strpos($userAgent, $navigator)+$n+strlen($navigator)+1;
					  for (; $j<=strlen($userAgent); $j++) {
							$s = substr ($userAgent, $j, 1);
							if(is_numeric($version.$s) )
							   $version .= $s;
							else
							   break;
					  }
				}
			}
			if(strpos($userAgent, 'linux')) {
				$platform = 'linux';
			}
			elseif(strpos($userAgent, 'macintosh') || strpos($userAgent, 'mac platform x')) {
				$platform = 'mac';
			}
			elseif(strpos($userAgent, 'windows') || strpos($userAgent, 'win32')) {
				$platform = 'windows';
			}
			return array(
				"browser" => $navigator,
				"version" => $version,
				"platform" => $platform,
				"userAgent" => $userAgent);
	}
}
?>
