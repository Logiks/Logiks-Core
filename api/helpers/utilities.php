<?php
/*
 * This class is used for storing all the additional utilties that are used 
 * through out the Framework
 *  
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("cleanSpecialCharacters")) {	
	function cleanSpecialCharacters($string) {
		// Replace other special chars
		$specialCharacters = array(
			'#' => '',
			'$' => '',
			'%' => '',
			'&' => '',
			'@' => '',
			'.' => '',
			'€' => '',
			'+' => '',
			'=' => '',
			'§' => '',
			'/' => '',
			'\'' => ''
		);

		while (list($character, $replacement) = each($specialCharacters)) {
			$string = str_replace($character, '-' . $replacement . '-', $string);
		}

		/*$string = strtr($string,
		"ÀÁÂÃÄÅ? áâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ",
		"AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn"
		);*/

		// Remove all remaining other unknown characters
		$string = preg_replace('/[^a-zA-Z0-9-]/', ' ', $string);
		$string = preg_replace('/^[-]+/', '', $string);
		$string = preg_replace('/[-]+$/', '', $string);
		$string = preg_replace('/[-]{2,}/', ' ', $string);

		return $string;
	}
	function cleanLink($page) {
		$page=strip_tags($page);
		$page=stripcslashes($page);
		return $page;
	}	
	function processHTMLToText($document) {
	// $document should contain an HTML document.  This will remove HTML tags, javascript sections
	// and white space. It will also convert some common HTML entities to their text equivalent.
		$search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript
						 "'<[/!]*?[^<>]*?>'si",          // Strip out HTML tags
						 "'([rn])[s]+'",                // Strip out white space
						 "'&(quot|#34);'i",                // Replace HTML entities
						 "'&(amp|#38);'i",
						 "'&(lt|#60);'i",
						 "'&(gt|#62);'i",
						 "'&(nbsp|#160);'i",
						 "'&(iexcl|#161);'i",
						 "'&(cent|#162);'i",
						 "'&(pound|#163);'i",
						 "'&(copy|#169);'i",
						 "'&#(d+);'e");                    // evaluate as php

		$replace = array ("", "", "\1", "\"", "&", "<", ">", " ", chr(161), chr(162),
						 chr(163), chr(169), "chr(\1)");

		$document = preg_replace("<br/>", "\n", $document);
		$document = preg_replace("&nbsp;", " ", $document);
		$text = preg_replace($search, $replace, $document);
		return $text;
	}
	
	function ampReplace( $text ) {
		$text = str_replace( '&&', '*--*', $text );
		$text = str_replace( '&#', '*-*', $text );
		$text = str_replace( '&amp;', '&', $text );
		$text = preg_replace( '|&(?![\w]+;)|', '&amp;', $text );
		$text = str_replace( '*-*', '&#', $text );
		$text = str_replace( '*--*', '&&', $text );
		return $text;
	}
	
	function parseSize($size,$n=0) {
		$size1=$size;
		for($i=0;$i<$n;$i++) {
			$size1=$size1/1024;
		}
		if($size1>1024 && $n<=3) {
			return parseSize($size1,$n+1);
		} else {
			$nx=strpos($size1,".");
			if($nx>0) $size1=substr($size1,0,$nx+3);
			
			if($n<=0) return $size1." bytes";
			else if($n==1) return $size1." kb";
			else if($n==2) return $size1." mb";
			else if($n==3) return $size1." Gb";
			else return $size1." Tb";
		}
	}
}
?>
