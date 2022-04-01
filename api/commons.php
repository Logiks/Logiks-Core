<?php
/*
 * This contains all the Commonly Required functions that are used through out the Framework
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

include_once __DIR__."/libs/logikssession.inc";

if (!function_exists('printArray')) {
	function printArray($arr,$noPrint=false) {
		if($arr==null) return;
		if($noPrint) {
			ob_start();
			echo "<pre>";
			print_r($arr);
			echo "</pre>";
			$data=ob_get_contents();
			ob_clean();
			return $data;
		} else {
			echo "<pre>";
			print_r($arr);
			echo "</pre>";
		}
	}
	function println($line) {
		echo "$line<br/>";
	}
	function toTitle($s,$process=false) {
		if($s==null || strlen($s)<=0) return "";
		if($process && function_exists("_replace")) {
			$s=_replace($s);
		}
		$s=str_replace("_"," ",$s);
		
		if(strtoupper($s)!=$s && strtolower($s)!=$s) {
			$s = preg_replace("([A-Z])", " $0", $s);
		}
		
		$s=strtolower($s);
		$s=trim($s);
		if(strlen($s)<=3) {
			$s=strtoupper($s);
		} else {
			$s=strtolower($s);
			$s=ucwords($s);
		}
		return $s;
	}
	function cleanSpecial($data) {
		$data = str_replace(' ', '_', $data); // Replaces all spaces with hyphens.
		return preg_replace('/[^A-Za-z0-9\-_]/', '', $data); // Removes special chars.
	}
	function cleanText($data) {
		$s=htmlspecialchars_decode($data);
		$s=str_replace("\\\"","\"",$s);
		$s=str_replace("\\'","'",$s);
		$s=stripslashes($s);
		$data=trim($s);
		return $data;
	}
	function cleanCode($data) {
		$data=cleanText($data);
		$data=str_replace("<!--?","<?",$data);
		$data=str_replace("?-->","?>",$data);
		return $data;
	}
	function cleanForDB($data) {
		$data=cleanCode($data);
		//$data=mysqli::real_escape_string($data);
		
		$search = array("\\",  "\x00", "\n",  "\r",  "'",  '"', "\x1a");
    	$replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");

    	$data=str_replace($search, $replace, $data);

		return $data;
	}
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		$str=cleanForDB($str);
		return $str;
	}
	function htmlClean($value) {
		$value=html_entity_decode($value);
		$value=str_replace("%7B","{",$value);
		$value=str_replace("%7D","}",$value);
		$value=str_replace("%22","\"",$value);
		$value=str_replace("%3A",":",$value);
		$value=str_replace("%2C",",",$value);
		$value=str_replace("%5B","[",$value);
		$value=str_replace("%5D","]",$value);
		return $value;
	}
	function rstrstr($haystack,$needle) {
        return substr($haystack, 0,strpos($haystack, $needle));
    }
	function strip($value) {
		//if(get_magic_quotes_gpc() != 0)
		if(is_array($value))  {
			if (array_is_associative($value) ) {
				foreach( $value as $k=>$v) {
					$tmp_val[$k] = stripslashes($v);
				}
				$value = $tmp_val;
			} else  {
				for($j = 0; $j < sizeof($value); $j++) {
					$value[$j] = stripslashes($value[$j]);
				}
			}
		} else {
			$value = stripslashes($value);
		}
		return $value;
	}
	function array_is_associative ($array) {
		if (is_array($array) && !empty($array) ) {
			for ($iterator = count($array) - 1; $iterator; $iterator-- ) {
				if (! array_key_exists($iterator, $array) ) { return true; }
			}
			return !array_key_exists(0, $array);
		}
		return false;
	}
	function array_implode_associative ($glueWord, $glueMiddle,$array) {
		//array_walk($array, create_function('&$i,$k','$i=" $k'.$glueMiddle.'\"$i\"";'));
		array_walk($array, function(&$i,$k) {
			global $glueMiddle;
			return $i="{$k}{$glueMiddle}\"{$i}\"";
		});
		return implode($glueWord,$array);

	}
	function split_by_commas($str) {
		$buffer = '';
		$stack = array();
		$depth = 0;
		$len = strlen($str);
		for ($i=0; $i<$len; $i++) {
			$char = $str[$i];
			switch ($char) {
				case '(':
					$depth++;
					break;
				case ',':
					if (!$depth) {
						if ($buffer !== '') {
							$stack[] = $buffer;
							$buffer = '';
						}
						continue 2;
					}
					break;
				case ' ':
					if (!$depth) {
						continue 2;
					}
					break;
				case ')':
					if ($depth) {
						$depth--;
					} else {
						$stack[] = $buffer.$char;
						$buffer = '';
						continue 2;
					}
					break;
			}
			$buffer .= $char;
		}
		if ($buffer !== '') {
			$stack[] = $buffer;
		}
		return $stack;
	}
	function splitByCaps($string){
		return preg_replace('/([a-z0-9])?([A-Z])/','$1 $2',$string);
	}
	function getHash($txt,$method="md5") {
		$unique_salt=getConfig("HASH_SALT");
		switch($method) {
			case "md5":
				return md5($pwd);
			case "sha1":
				return sha1($pwd);
			case "blowfish":
				$p=crypt($pwd, '$2a$10$'.$unique_salt);
				return substr($p,strlen('$2a$10$'.$unique_salt));
			case "des":
				$p=crypt($pwd, 'rl');
				return substr($p,2);
			case "sha256":
				$p=crypt($pwd, '$5$rounds=5000$'.$unique_salt);
				return $p;
			case "method1":
				$hash = sha1($unique_salt.$pwd);
				for ($i = 0; $i < 1000; $i++) {
					$hash = sha1($hash);
				}
				return $hash;
		}
		return md5($pwd);
	}
	function arrayToHTML($arr=array(),$format="table",$classes="") {
		$out="";
		//printArray($arr);
		switch ($format) {
			case 'list':
				$out="<ul class='{$classes}'>";
				foreach($arr as $a=>$v){
					$a=toTitle($a);
					if(is_array($v)){
						$out.="<li>$a : </li>";
						$out.=arrayToHTML($v,$format);
					} else {
						$out.="<li>$a : $v</li>";
					}
				}
				$out.="</ul>";
				break;
			case 'table':
			default:
				$out = "<table class='{$classes}' width=100%>";
				foreach($arr as $a=>$v){
					$a=toTitle($a);
					if(is_array($v)){
				      $out .= '<tr><td colspan=10>'.arrayToHTML($v).'</td></tr>';
				    }else{
				      $out .= "<tr><td>$a</td><td>$v</td></tr>";
				    }
				}
				$out.='</table>';
				break;
		}
		return $out;
	}
	function createTimeStamp($encoded=true) {
		if($encoded) {
			$s=date(getConfig("TIMESTAMP_FORMAT")).microtime();
			if(function_exists("md5")) {
				$s=md5($s);
			} else {
				$s=base64_encode($s);
			}
			return $s;
		} else {
			$s=date(getConfig("TIMESTAMP_FORMAT")).microtime();
			return $s;
		}
	}
	function parseTags($txt) {
		$breaks = array("<br />","<br>","<br/>");
		$abstractText = str_ireplace($breaks, "\r\n", $txt);
		$abstract=strip_tags($abstractText);
		$pattern = '/\s*#(.+?)\s/';
		preg_match_all($pattern, $txt, $matches);
		$tags=$matches[1];
		return $tags;
	}
	function parseUserid($txt) {
		$breaks = array("<br />","<br>","<br/>");
		$abstractText = str_ireplace($breaks, "\r\n", $txt);
		$abstract=strip_tags($abstractText);
		$pattern = '/\s*@(.+?)\s/';
		preg_match_all($pattern, $txt, $matches);
		$tags=implode(",",$matches[1]);
		return $tags;
	}
	function logiks_replace($subject) {
		if(function_exists("_replace")) {
			return _replace($subject);
		} elseif(class_exists("LogiksReplace")) {
			$lr=new LogiksReplace();
			return $lr->_replace($subject);
		} else {
			$subject=str_replace("#ROOT#", ROOT, $subject);
			if(defined("SITENAME")) {
				$subject=str_replace("#SITENAME#", SITENAME, $subject);
				if(defined("APPS_FOLDER")) $subject=str_replace("#APPROOT#", APPS_FOLDER.$site."/", $subject);
			}
			if(defined("THEME_FOLDER")) {
				if(defined("APPS_THEME")) $subject=str_replace("#THEME#", THEME_FOLDER.APPS_THEME, $subject);
				else $subject=str_replace("#THEME#", THEME_FOLDER."default/", $subject);
			}
		}
		return $subject;
	}
	function getSysHash() {
		if(isset($_SESSION['SYS_HASH'])) {
			return $_SESSION['SYS_HASH'];
		} else {
			$hashStr=session_id();
			if(getConfig("SECURE_IPLOCK")===true) {
				$hashStr.=_server('REMOTE_ADDR');
			}
			if(isset($_SESSION['SESS_USER_ID'])) {
				$hashStr.=$_SESSION['SESS_USER_ID'];
			}
			$hash=md5($hashStr);
			$_SESSION['SYS_HASH']=$hash;
			return $hash;
		}
	}
}

if(!function_exists("_session")) {
	function _server($var) {
		$data=LogiksSession::getInstance()->data('SERVER');
		if(isset($data[$var])) {
			return $data[$var];
		}
		return false;
	}
	function _session($var,$value=null) {
		if($value==null) {
			$data=LogiksSession::getInstance()->data('SESSION');
			if(isset($data[$var])) {
				return $data[$var];
			}
		} else {
			_envData("SESSION",$var,$value);
			return $value;
		}
	}
	function _env($var,$value=null) {
		if($value==null) {
			$data=LogiksSession::getInstance()->data('ENV');
			if(isset($data[$var])) {
				return $data[$var];
			}
		} else {
			_envData("ENV",$var,$value);
			return $value;
		}
	}
	function _cookie($key,$val=null,$period=0) {
	    if($val===false) {
	      //delete cookie
	      $currentCookieParams = session_get_cookie_params();
	      setcookie(
	          $key,//name
	          "",//value
	          time()-3600,//expires at end of session
	          $currentCookieParams['path'],//path
	          $currentCookieParams['domain'],//domain
	          isHTTPS() //secure
	      );
	      return true;
	    } elseif($val===null) {
	      if(isset($_COOKIE[$key])) return $_COOKIE[$key];
	      else return false;
	    } else {
	      $currentCookieParams = session_get_cookie_params();
	      setcookie(
	          $key,//name
	          $val,//value
	          $period,//expires at end of session
	          $currentCookieParams['path'],//path
	          $currentCookieParams['domain'],//domain
	          isHTTPS() //secure
	      );
	      $_COOKIE[$key] = $val;
	      _envData("COOKIE",$key,$val);
	      return $val;
	    }
	  }
	function _envData($key,$name,$value) {
		LogiksSession::getInstance()->set(strtoupper($key),$name,$value);
	}
	function isHTTPS() {
		if(defined("FORCE_HTTPS")) {
			if(FORCE_HTTPS===true || FORCE_HTTPS==="true" || FORCE_HTTPS==="1") return true;
	  }
		if(isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS'])) {
			return true;
		}
		return false;
	}
}
?>
