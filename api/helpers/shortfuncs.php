<?php
/*
 * ShortHand Functions For Some Most Used functions
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.5
 */
if(!defined('ROOT')) exit('No direct script access allowed');

/*Shorthand function for special folders for this site*/
if(!function_exists("_dirTemp")) {
	function _dirTemp($dir) {
		$fx=ROOT.CACHE_APPS_FOLDER.SITENAME."/{$dir}/";
		if(!is_dir($fx)) {
			mkdir($fx,0777,true);
		}
		return $fx;
	}
}

/*URL Oriented Functions*/
if(!function_exists("_link")) {
	//default url generator for logiks
	function _link($page="", $query="", $site=SITENAME) {
		$ssr1=stristr($page,"http://");
		$ssr2=stristr($page,"https://");
		$ssn=strlen($ssr1)+strlen($ssr2);
		if($ssn>0) return $page;

		return getPrettyLink($page, $site, $query);
	}
}
if(!function_exists("_service")) {
	//Gets the service cmd link
	function _service($scmd, $action="", $format="json", $params=array(), $site=SITENAME) {
		//$s=SiteLocation."services/{$scmd}?site={$site}";
		$s=SiteLocation."services/{$scmd}?site={$site}&syshash=".getSysHash();
		if(strlen($action)>0) $s.="&action={$action}";
		if(strlen($format)>0) $s.="&format={$format}";
		if(is_array($params)) foreach($params as $a=>$b) $s.="&{$a}={$b}";
		return $s;
	}
}
if(!function_exists("_site")) {
	//Gets the site link
	function _site($site=SITENAME,$page="") {
		return getPrettyLink($page, $site);
	}
}
if(!function_exists("_url")) {
	//Gets current url in proper format
	function _url() {
		$url=_server('HTTP_HOST')."/"._server('REQUEST_URI');
		$url=str_replace("//","/",$url);
		$url=str_replace("//","/",$url);
		if(_server("HTTPS") && strlen(_server("HTTPS"))>0) $url="https://$url";
		elseif(_server("SERVER_PROTOCOL")=="HTTP/1.1")	$url="http://$url";
		return $url;
	}
}
if(!function_exists("_uri")) {
	//Gets current uri for the actual page, eg PAGE - SLUG
	function _uri() {
		if(defined('PAGEURI')) {
			$uri=_link(PAGEURI);
		} else {
			$uri=explode("/",PAGE);
			$fix=false;
			foreach($uri as $a=>$b) {
				if($b=="new" || $b=="edit") {
					$fix=true;
				}
				if($fix) unset($uri[$a]);
			}
			$uri=_link(implode("/",$uri));
		}
		return $uri;
	}
}
/*Date And Time Functions*/
if(!function_exists("_time")) {
	//Used Generally To Convert UserFormatted Times To DB Formatted (H:i:s) times
	function _time($time=null, $inFormat="*", $outFormat="H:i:s") {
		if($inFormat=="*") $inFormat=getConfig("TIME_FORMAT");
		if(strlen($time)<=0 || $time==null) {
			$time=date($inFormat);
		}
		$timeStore=array("h"=>"","H"=>"","i"=>"","u"=>"","s"=>"","g"=>"","G"=>"","a"=>"","A"=>"");

		$inFormat=str_replace("-","/",$inFormat);
		$inFormat=str_replace(getConfig("TIME_SEPARATOR"),":",$inFormat);

		if($inFormat==$outFormat) return $time;

		$timeArr=preg_split("/[\s,:]+/",$time);
		$inFormatArr=preg_split("/[\s,:]+/",$inFormat);
		if(count($inFormatArr)!=count($timeArr)) {
			return false;
		}
		$timeStore=array();
		foreach($inFormatArr as $key => $value) {
			$timeStore[$value]=$timeArr[$key];
			if($value=="H") {
				$timeStore["g"]=intval($timeStore["H"]%12);
				$timeStore["G"]=intval($timeStore["H"]);
				$timeStore["h"]=strlen($timeStore["g"])>1?$timeStore["g"]:"0{$timeStore['g']}";
				//$timeStore["H"]=strlen($timeStore["G"])>1?$timeStore["G"]:"0{$timeStore['G']}";
			} elseif($value=="h") {
				$timeStore["g"]=intval($timeStore["h"]);
				$timeStore["G"]=intval($timeStore["g"]>12?($timeStore["g"]+12):$timeStore["g"]);
				//$timeStore["h"]=strlen($timeStore["g"])>1?$timeStore["g"]:"0{$timeStore['g']}";
				$timeStore["H"]=strlen($timeStore["G"])>1?$timeStore["G"]:"0{$timeStore['G']}";
			} elseif($value=="G") {
				$timeStore["g"]=$timeStore["G"]%12;
				$timeStore["h"]=strlen($timeStore["g"])>1?$timeStore["g"]:"0{$timeStore['g']}";
				$timeStore["H"]=strlen($timeStore["G"])>1?$timeStore["G"]:"0{$timeStore['G']}";
			} elseif($value=="g") {
				$timeStore["G"]=intval($timeStore["g"]>12?($timeStore["g"]+12):$timeStore["g"]);
				$timeStore["h"]=strlen($timeStore["g"])>1?$timeStore["g"]:"0{$timeStore['g']}";
				$timeStore["H"]=strlen($timeStore["G"])>1?$timeStore["G"]:"0{$timeStore['G']}";
			}
		}
		if($timeStore["g"]>12) {
			$timeStore["a"]="pm";
			$timeStore["A"]="PM";
		} else {
			$timeStore["a"]="am";
			$timeStore["A"]="AM";
		}
		$a=preg_split("/[\s,:]+/",$outFormat);
		$out=$outFormat;
		foreach($a as $w) {
			$out=str_replace($w, $timeStore[$w], $out);
		}
		return $out;
	}
}
if(!function_exists("_date")) {
	//Used Generally To Convert UserFormatted Dates To DB Formatted (Y/m/d) dates
	function _date($date=null, $inFormat="*", $outFormat="Y/m/d") {
		if($date=="0000-00-00") return "0000-00-00";
		if($date==null || strlen($date)<=0) return "";
		if($inFormat=="*" || $inFormat=="")  $inFormat=getConfig("DATE_FORMAT");

		if($inFormat==$outFormat) return $date;

		$dArr=array("d"=>"","m"=>"","Y"=>"","y"=>"","n"=>"","M"=>"","F"=>"");
		$months=array(
				1=>"Jan",2=>"Feb",3=>"Mar",4=>"Apr",
				5=>"May",6=>"Jun",7=>"Jul",8=>"Aug",
				9=>"Sept",10=>"Oct",11=>"Nov",12=>"Dec",
			);
		$monthsFull=array(
				1=>"January",2=>"February",3=>"March",4=>"April",
				5=>"May",6=>"June",7=>"July",8=>"August",
				9=>"September",10=>"October",11=>"November",12=>"December",
			);
		$days=array(
				1=>"Mon",2=>"Teu",3=>"Wed",4=>"Thu",
				5=>"Fri",6=>"Sat",7=>"Sun"
			);
		$daysFull=array(
				1=>"Monday",2=>"Teusday",3=>"Wednesday",4=>"Thursday",
				5=>"Friday",6=>"Saturday",7=>"Sunday"
			);

		$outFormat=str_replace("yy","Y",$outFormat);
		$inFormat=str_replace("yy","Y",$inFormat);

		$dateArr=preg_split("/[\s,\-:\/]+/",$date);
		$inFormatArr=preg_split("/[\s,\-:\/]+/",$inFormat);
		$dateStore=array();
		if(count($inFormatArr)!=count($dateArr)) {
			return false;
		}
		foreach($inFormatArr as $key => $value) {
			$dateStore[$value]=$dateArr[$key];
		}
		
		$dateStore["n"]=intval($dateStore["m"]);
		$dateStore["j"]=intval($dateStore["d"]);
		//$dateStore["D"]=$days[floor($dateStore["j"]%7)];
		$dateStore["M"]=$months[$dateStore["n"]];
		$dateStore["F"]=$monthsFull[$dateStore["n"]];
		$dateStore["y"]=substr($dateStore["Y"], 2);

		$dateStore["w"]=date("w",strtotime("{$dateStore["Y"]}/{$dateStore["m"]}/{$dateStore["d"]}"));
		$dateStore["W"]=date("W",strtotime("{$dateStore["Y"]}/{$dateStore["m"]}/{$dateStore["d"]}"));
		
		if(isset($days[$dateStore["w"]])) $dateStore["l"]=$days[$dateStore["w"]];
		else $dateStore["l"]=0;
		
		if(isset($daysFull[$dateStore["w"]])) $dateStore["L"]=$daysFull[$dateStore["w"]];
		else $dateStore["L"]=0;

		$a=preg_split("/[\s,-:\/]+/",$outFormat);
		$out=$outFormat;
		foreach($a as $w) {
			$out=str_replace($w, $dateStore[$w], $out);
		}
		return $out;
	}
}
if(!function_exists("_pDate")) {
	//From Server Side To Client Side :: Print Date
	function _pDate($date=null,$outFormat=null) {
		if($date==null || strlen($date)<=0) $date="";//date("Y/m/d");
		$date=explode(" ", $date);
		if(!isset($date[1])) $date[1]="";
		if($outFormat==null) {
			if(strlen($date[1])>0) {
				return trim(_date($date[0],"Y/m/d",getConfig("DATE_FORMAT"))." "._time($date[1],"H:i:s",getConfig("TIME_FORMAT")));
			} else {
				return trim(_date($date[0],"Y/m/d",getConfig("DATE_FORMAT")));
			}
		} else {
			$outFormat=explode(" ", $outFormat);
			if(!isset($outFormat[1])) $outFormat[1]="";
			if(strlen($date[1])>0) {
				return trim(_date($date[0],"Y/m/d",$outFormat[0])." "._time($date[1],"H:i:s",$outFormat[1]));
			} else {
				return trim(_date($date[0],"Y/m/d",$outFormat[0]));
			}
		}

	}
}
if(!function_exists("_timestamp")) {
	function _timestamp($micro=true) {
		if($micro)
			return date(getConfig("TIMESTAMP_FORMAT")).microtime();
		else
			return date(getConfig("TIMESTAMP_FORMAT"));
	}
}
/*Xtra Functions*/
if(!function_exists("_randomid")) {
	function _randomid($d="",$hash=true) {
		$s=SITENAME."_".date("Y-m-d-G:i:s")."_"._server('REMOTE_ADDR')."_".rand(1000,9999999);
		if(_session('SESS_USER_ID')) $s.="_"._session('SESS_USER_ID');
		if($hash) return $d.md5($s);
		else return $d.$s;
	}
}
if(!function_exists("_ling")) {
	function _ling($data,$forceWord=false) {
		$ling=Lingulizer::getInstance();
		if(is_array($data)) {
			foreach($data as $a=>$b) {
				$data[$a]=$ling->toLing($b);
			}
			return $data;
		} else {
			if($forceWord) {
				return $ling->toLing($data);
			} else {
				if(strpos($data," ")>0) {
					return $ling->toLing($data);//toLingContent
				} else {
					return $ling->toLing($data);
				}
			}
		}
	}
}
if(!function_exists("_lingID")) {
	function _lingID($msgID) {
		if(strpos($msgID,"#")===0) {
			$msg1=substr($msgID,1);
			$msg2=_ling($msg1);
			if($msg1==$msg2) return "";
			else return $msg2;
		} else {
			return _ling($msgID);
		}
	}
}
if(!function_exists("_replace")) {
	function _replace($str,$glue="#") {
		$lr=new LogiksReplace();
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+@[a-zA-Z]+{$glue}/",array($lr,"replaceFromEnviroment"),$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+![0-9]+{$glue}/",array($lr,"replaceFromEnviroment"),$str);
		$str=preg_replace_callback("/{$glue}[a-zA-Z0-9-_]+{$glue}/",array($lr,"replaceFromEnviroment"),$str);
		return $str;
	}
}

if(!function_exists("_slugify")) {
	function _slugify($text) {
		// replace non letter or digits by -
		$text = preg_replace('~[^\pL\d]+~u', '-', $text);

		// transliterate
		$text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

		// remove unwanted characters
		$text = preg_replace('~[^-\w]+~', '', $text);

		// trim
		$text = trim($text, '-');

		// remove duplicate -
		$text = preg_replace('~-+~', '-', $text);

		// lowercase
		$text = strtolower($text);

		if (empty($text)) {
			return 'n-a';
		}

		return $text;
	}
}
?>
