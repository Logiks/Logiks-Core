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
	
	function normalize ($buffer) {
		$buffer = strtolower ( $buffer );
	
		$buffer = str_replace ( "à", "a", $buffer );
		$buffer = str_replace ( "á", "a", $buffer );
		$buffer = str_replace ( "â", "a", $buffer );
		$buffer = str_replace ( "ä", "a", $buffer );
		
		$buffer = str_replace ( "è", "e", $buffer );
		$buffer = str_replace ( "é", "e", $buffer );
		$buffer = str_replace ( "ê", "e", $buffer );
		$buffer = str_replace ( "ë", "e", $buffer );

		$buffer = str_replace ( "ì", "i", $buffer );
		$buffer = str_replace ( "í", "i", $buffer );
		$buffer = str_replace ( "î", "i", $buffer );
		$buffer = str_replace ( "ï", "i", $buffer );

		$buffer = str_replace ( "ò", "o", $buffer );
		$buffer = str_replace ( "ó", "o", $buffer );
		$buffer = str_replace ( "ô", "o", $buffer );
		$buffer = str_replace ( "ö", "o", $buffer );

		$buffer = str_replace ( "ù", "u", $buffer );
		$buffer = str_replace ( "ú", "u", $buffer );
		$buffer = str_replace ( "û", "u", $buffer );
		$buffer = str_replace ( "ö", "u", $buffer );

		$buffer = str_replace ( "ÿ", "y", $buffer );
		$buffer = str_replace ( "ç", "c", $buffer );
		$buffer = str_replace ( "ñ", "n", $buffer );

		return $buffer;
	}
}
?>
