<?php
if(!defined('ROOT')) exit('No direct script access allowed');
//This class is used for storing all the Commonly Required functions that are used
//through out the Framework

if (!function_exists('printArray')) {
	function printArray($arr) {
		if($arr==null) return;
		echo "<pre>";
		print_r($arr);
		echo "</pre>";
	}
	function println($line) {
		echo "$line<br/>";
	}
	function toTitle($s,$process=false) {
		if($process) {
			$s=_replace($s);
		}
		$s=str_replace("_"," ",$s);
		$s=strtolower($s);
		$s=trim($s);
		$s=ucwords($s);
		return $s;
	}
	function cleanText($data) {
		$s=htmlspecialchars_decode($data);
		$s=str_replace("\\\"","\"",$s);
		$s=str_replace("\\'","'",$s);
		$data=trim($s);
		return $data;
	}
	function cleanCode($data) {
		$data=cleanText($data);
		$data=str_replace("<!--?","<?",$data);
		$data=str_replace("?-->","?>",$data);
		return $data;
	}
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}		
		$str=mysql_real_escape_string($str);
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
	function Strip($value) {
		if(get_magic_quotes_gpc() != 0) {
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
	//Called from Template Parsing Engines
	function replaceFromEnviroment($in,$dataArr=null,$glue="#") {
		if(is_array($in)) {
			$in=$in[0];
		}
		if(strlen($glue)>0) {
			$in=substr($in,1,strlen($in)-2);
		}
		if($dataArr==null) {
			$dataArr=array();
			$dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
			$dataArr["time"]=date(getConfig("TIME_FORMAT"));
			$dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));
			
			$dataArr["site"]=SITENAME;
			if(isset($_REQUEST["page"])) $dataArr["page"]=$_REQUEST["page"]; else $dataArr["page"]="home";
			
			if(isset($_SESSION["SESS_USER_ID"])) $dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $dataArr["user"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $dataArr["privilege"]="Guest";
			if(isset($_SESSION["SESS_USER_NAME"])) $dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $dataArr["user_name"]="Guest";
			if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $dataArr["privilege_name"]="Guest";
		}
		if(isset($dataArr[$in])) return $dataArr[$in];
		elseif(isset($_REQUEST[$in])) return $_REQUEST[$in];
		elseif(isset($_SESSION[$in])) return $_SESSION[$in];
		elseif(isset($_SERVER[$in])) return $_SERVER[$in];
		return getConfig($in);
	}
	function getHash($txt,$method="md5") {
		$unique_salt=HASH_SALT;
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
}
?>
