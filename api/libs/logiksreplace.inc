<?php
/*
 * This file contains all the configuration functions
 * Supported Types :: DEFINE, SESSION, CONFIG, PHPINI, COOKIE
 * Uses $_ENV as index to the Configs
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 3.0
 */
class LogiksReplace {

  protected $dataArr=null;
  protected $glue="#";

  public function __construct() {
    $this->dataArr=array();
    $this->dataArr["date"]=date(getConfig("PHP_DATE_FORMAT"));
    $this->dataArr["time"]=date(getConfig("TIME_FORMAT"));
    $this->dataArr["datetime"]=date(getConfig("PHP_DATE_FORMAT")." ".getConfig("TIME_FORMAT"));

    if(defined("SITENAME")) $this->dataArr["site"]=SITENAME;
    if(isset($_REQUEST["page"])) $this->dataArr["page"]=$_REQUEST["page"]; else $this->dataArr["page"]="home";

    if(isset($_SESSION["SESS_USER_ID"])) $this->dataArr["user"]=$_SESSION["SESS_USER_ID"]; else $this->dataArr["user"]="guest";
    if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $this->dataArr["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $this->dataArr["privilege"]=0;
    if(isset($_SESSION["SESS_USER_NAME"])) $this->dataArr["username"]=$_SESSION["SESS_USER_NAME"];  else $this->dataArr["user_name"]="Guest";
    if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $this->dataArr["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $this->dataArr["privilege_name"]="Guest";


  }
  public function setData($data) {
    $this->dataArr=$data;
  }
  public function addData($data) {
    $this->dataArr=array_merge($this->dataArr,$data);
  }
  public function unsetKey($key) {
    if(isset($this->dataArr[$key])) unset($this->dataArr[$key]);
  }
  public function glue($newGlue=null) {
    if($newGlue!=null) {
      $this->glue=$newGlue;
    }
    return $this->glue;
  }
  public function replace($str) {
    $glue=$this->glue;
    $str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+{$glue}/",function($matches) {
      $matches[0]=substr($matches[0], 1,strlen($matches[0])-2);
      if(isset($this->dataArr[$matches[0]])) return $this->dataArr[$matches[0]];
      return $matches[0];
		},$str);
    return $str;
  }
  public function _replace($str) {
    $glue=$this->glue;
    $str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+{$glue}/",array($this,"replaceFromEnviroment"),$str);
    return $str;
  }
  public function __replace($str) {
    $glue=$this->glue;
    $str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+@[a-zA-Z]+{$glue}/",array($this,"replaceFromEnviroment"),$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+![0-9]+{$glue}/",array($this,"replaceFromEnviroment"),$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+{$glue}/",array($this,"replaceFromEnviroment"),$str);
    return $str;
  }

  //Called from Template Parsing Engines
	public function replaceFromEnviroment($in) {
    $glue=$this->glue;
		if(is_array($in)) {
			$in=$in[0];
		}
		if(strlen($glue)>0) {
			$in=substr($in,1,strlen($in)-2);
		}

		if(strpos($in,"@")>0) {
			$inArr=explode("@",$in);
			$in=$inArr[0];
			if(isset($inArr[1])) $con=strtolower($inArr[1]);
			else $con="get";
			if($con=="get" && isset($_GET[$in])) return $_GET[$in];
			elseif($con=="post" && isset($_POST[$in])) return $_POST[$in];
			elseif($con=="session" && isset($_SESSION[$in])) return $_SESSION[$in];
			elseif($con=="server" && isset($_SERVER[$in])) return $_SERVER[$in];
			elseif($con=="cookie" && isset($_COOKIE[$in])) return $_COOKIE[$in];
			return "";
		// } elseif(strpos($in,"!")>0) {
		// 	$inArr=explode("!",$in);
		// 	$in=$inArr[0];
		// 	if(isset($inArr[1])) $n=strtolower($inArr[1]);
		// 	else $n=0;
		// 	$sr="";
    //
		// 	if(isset($dataArr[$in])) $sr=$dataArr[$in];
		// 	elseif(isset($_REQUEST[$in])) $sr=$_REQUEST[$in];
		// 	elseif(isset($_SESSION[$in])) $sr=$_SESSION[$in];
		// 	elseif(isset($_SERVER[$in])) $sr=$_SERVER[$in];
		// 	elseif(isset($_COOKIE[$in])) $sr=$_COOKIE[$in];
		// 	else $sr=getConfig($in);
    //
		// 	if(!is_array($sr)) {
		// 		$sr=str_replace(",","/",$sr);
		// 		$sr=explode("/",$sr);
		// 	}
		// 	if(isset($sr[$n])) return $sr[$n];
		// 	elseif(isset($sr[0])) return $sr[0];
		// 	else return "";
		} else {
			if(isset($this->dataArr[$in])) return $this->dataArr[$in];
			elseif(isset($_REQUEST[$in])) return $_REQUEST[$in];
			elseif(isset($_SESSION[$in])) return $_SESSION[$in];
			elseif(isset($_SERVER[$in])) return $_SERVER[$in];
			elseif(isset($_COOKIE[$in])) return $_COOKIE[$in];
			return getConfig($in);
		}
	}
}
?>
