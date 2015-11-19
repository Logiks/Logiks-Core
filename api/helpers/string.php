<?php
/*
 * String related functions
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if (!function_exists('singular')) {
	function startsWith($haystack,$needle,$case=true) {
		if($case){return (strcmp(substr($haystack, 0, strlen($needle)),$needle)===0);}
		return (strcasecmp(substr($haystack, 0, strlen($needle)),$needle)===0);
	}
	function endsWith($haystack,$needle,$case=true) {
		if($case){return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);}
		return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
	}
	
	function singular($str) {
		$str = strtolower(trim($str));
		$end = substr($str, -3);

		if ($end == 'ies'){
			$str = substr($str, 0, strlen($str)-3).'y';
		} elseif ($end == 'ses'){
			$str = substr($str, 0, strlen($str)-2);
		}
		else{
			$end = substr($str, -1);

			if ($end == 's')
			{
				$str = substr($str, 0, strlen($str)-1);
			}
		}

		return $str;
	}

	function plural($str, $force = FALSE) {
		$str = strtolower(trim($str));
		$end = substr($str, -1);

		if ($end == 'y'){
			// Y preceded by vowel => regular plural
			$vowels = array('a', 'e', 'i', 'o', 'u');
			$str = in_array(substr($str, -2, 1), $vowels) ? $str.'s' : substr($str, 0, -1).'ies';
		} elseif ($end == 'h'){
			if (substr($str, -2) == 'ch' OR substr($str, -2) == 'sh')
			{
				$str .= 'es';
			}
			else
			{
				$str .= 's';
			}
		} elseif ($end == 's'){
			if ($force == TRUE)
			{
				$str .= 'es';
			}
		}
		else{
			$str .= 's';
		}

		return $str;
	}

	function camelize($str) {
		$str = 'x'.strtolower(trim($str));
		$str = ucwords(preg_replace('/[\s_]+/', ' ', $str));
		return substr(str_replace(' ', '', $str), 1);
	}

	function underscore($str) {
		return preg_replace('/[\s]+/', '_', strtolower(trim($str)));
	}

	function humanize($str) {
		return ucwords(preg_replace('/[_]+/', ' ', strtolower(trim($str))));
	}

   /*
	* @author Arun [arun@smartinfologiks.com]
	*/
	function byte_format($num, $precision = 1) {
		if ($num >= 1000000000000){
	        $num = round($num / 1099511627776, $precision);
	        $unit = _ling('TB');
	    } elseif ($num >= 1000000000){
	        $num = round($num / 1073741824, $precision);
	        $unit = _ling('GB');
	    } elseif ($num >= 1000000){
	        $num = round($num / 1048576, $precision);
	        $unit = _ling('MB');
	    } elseif ($num >= 1000){
	        $num = round($num / 1024, $precision);
	        $unit = _ling('KB');
	    } else {
	        $unit = _ling('Bytes');
	        return number_format($num).' '.$unit;
	    }
	    return number_format($num, $precision).' '.$unit;
	}
	
	function wordLimiter($str, $limit = 100, $end_char = '&#8230;') {
		if (trim($str) == '') {
			return $str;
		}

		preg_match('/^\s*+(?:\S++\s*+){1,'.(int) $limit.'}/', $str, $matches);

		if (strlen($str) == strlen($matches[0])) {
			$end_char = '';
		}

		return rtrim($matches[0]).$end_char;
	}
	function characterLimiter($str, $n = 500, $end_char = '&#8230;') {
		if (strlen($str) < $n) {
			return $str;
		}

		$str = preg_replace("/\s+/", ' ', str_replace(array("\r\n", "\r", "\n"), ' ', $str));

		if (strlen($str) <= $n) {
			return $str;
		}

		$out = "";
		foreach (explode(' ', trim($str)) as $val) {
			$out .= $val.' ';

			if (strlen($out) >= $n) {
				$out = trim($out);
				return (strlen($out) == strlen($str)) ? $out : $out.$end_char;
			}
		}
	}
	
	function wordCensor($str, $censored, $replacement = '') {
		if ( ! is_array($censored)) {
			return $str;
		}

		$str = ' '.$str.' ';

		// \w, \b and a few others do not match on a unicode character
		// set for performance reasons. As a result words like über
		// will not match on a word boundary. Instead, we'll assume that
		// a bad word will be bookeneded by any of these characters.
		$delim = '[-_\'\"`(){}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

		foreach ($censored as $badword) {
			if ($replacement != '') {
				$str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/i", "\\1{$replacement}\\3", $str);
			} else {
				$str = preg_replace("/({$delim})(".str_replace('\*', '\w*?', preg_quote($badword, '/')).")({$delim})/ie", "'\\1'.str_repeat('#', strlen('\\2')).'\\3'", $str);
			}
		}

		return trim($str);
	}
	
	function word_wrap($str, $charlim = '76') {
		// Se the character limit
		if ( ! is_numeric($charlim)) $charlim = 76;

		// Reduce multiple spaces
		$str = preg_replace("| +|", " ", $str);

		// Standardize newlines
		if (strpos($str, "\r") !== FALSE) {
			$str = str_replace(array("\r\n", "\r"), "\n", $str);
		}

		// If the current word is surrounded by {unwrap} tags we'll
		// strip the entire chunk and replace it with a marker.
		$unwrap = array();
		if (preg_match_all("|(\{unwrap\}.+?\{/unwrap\})|s", $str, $matches)) {
			for ($i = 0; $i < count($matches['0']); $i++) {
				$unwrap[] = $matches['1'][$i];
				$str = str_replace($matches['1'][$i], "{{unwrapped".$i."}}", $str);
			}
		}

		// Use PHP's native function to do the initial wordwrap.
		// We set the cut flag to FALSE so that any individual words that are
		// too long get left alone.  In the next step we'll deal with them.
		$str = wordwrap($str, $charlim, "\n", FALSE);

		// Split the string into individual lines of text and cycle through them
		$output = "";
		foreach (explode("\n", $str) as $line) {
			// Is the line within the allowed character count?
			// If so we'll join it to the output and continue
			if (strlen($line) <= $charlim) {
				$output .= $line."\n";
				continue;
			}

			$temp = '';
			while((strlen($line)) > $charlim) {
				// If the over-length word is a URL we won't wrap it
				if (preg_match("!\[url.+\]|://|wwww.!", $line)) {
					break;
				}

				// Trim the word down
				$temp .= substr($line, 0, $charlim-1);
				$line = substr($line, $charlim-1);
			}

			// If $temp contains data it means we had to split up an over-length
			// word into smaller chunks so we'll add it back to our current line
			if ($temp != '') {
				$output .= $temp."\n".$line;
			} else {
				$output .= $line;
			}

			$output .= "\n";
		}

		// Put our markers back
		if (count($unwrap) > 0) {
			foreach ($unwrap as $key => $val) {
				$output = str_replace("{{unwrapped".$key."}}", $val, $output);
			}
		}

		// Remove the unwrap tags
		$output = str_replace(array('{unwrap}', '{/unwrap}'), '', $output);

		return $output;
	}
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
