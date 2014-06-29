<?php
/*
 * SQL SRC across sessions
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists('getSQLSrc')) {
	function getSQLSrc($src,$sid,$tbl="") {
		if($src=="session") {
			if(isset($_SESSION[$sid])) {
				$arr=array();
				$arr["table"]=$_SESSION[$sid]["table"];
				$arr["cols"]=$_SESSION[$sid]["cols"];
				$arr["where"]=$_SESSION[$sid]["where"];
				return $arr;
			}
		}
		elseif($src=="cookie") {
			$arr=array();
			if(isset($_COOKIE["{$sid}_table"])) {
				$arr=array();
				$arr["table"]=$_COOKIE["{$sid}_table"];
				$arr["cols"]=$_COOKIE["{$sid}_cols"];
				$arr["where"]=$_COOKIE["{$sid}_where"];
				return $arr;
			}
		}
		elseif($src=="request") {
			$arr=array();
			$arr["table"]=$_REQUEST["sqltbl"];
			$arr["cols"]=$_REQUEST["sqlcols"];
			$arr["where"]=$_REQUEST["sqlwhere"];
			$arr["where"]=str_replace(":eq:","=",$arr["where"]);
			return $arr;
		}
		elseif($src=="post") {
			$arr=array();
			$arr["table"]=$_POST["sqltbl"];
			$arr["cols"]=$_POST["sqlcols"];
			$arr["where"]=$_POST["sqlwhere"];
			$arr["where"]=str_replace(":eq:","=",$arr["where"]);
			return $arr;
		}
		elseif($src=="file") {
			$arr=array();
			if(file_exists($sid)) {
				$data=file_get_contents($sid);
				$data=explode("\n",$data);
				foreach($data as $a) {
					if(strlen($a)>0 && substr(trim($a),0,1)!='#') {
						$b=explode("=",$a);
						if(count($b)>1) {
							$x=$b[0];
							unset($b[0]);
							$y=implode("=",$b);
							$y=processSQLQuery($y);
							$arr[$x]=$y;
						}
					}
				}
				return $arr;
			}
		}
		elseif($src=="dbtable") {
			$sql="SELECT datatable_table,datatable_cols,datatable_where FROM $tbl where id=$sid";
			$rs=_dbQuery($sql);
			if($rs) {
				$record=_db()->fetchData($rs);
				$arr=array();
				$arr["table"]=$record["datatable_table"];
				$arr["cols"]=$record["datatable_cols"];
				$arr["where"]=$record["datatable_where"];
				return $arr;
			}
		}
		else return null;
	}
}
if(!function_exists('processSQLQuery')) {	
	function processSQLQuery($q,$arr=array()) {
		$_SESSION["date"]=date("Y-m-d");
		$_SESSION["site"]=SITENAME;
		if(isset($_SESSION["SESS_USER_ID"])) $_SESSION["user"]=$_SESSION["SESS_USER_ID"]; else $_SESSION["user"]="Guest";
		if(isset($_SESSION["SESS_PRIVILEGE_ID"])) $_SESSION["privilege"]=$_SESSION["SESS_PRIVILEGE_ID"];  else $_SESSION["privilege"]="Guest";
		if(isset($_SESSION["SESS_USER_NAME"])) $_SESSION["username"]=$_SESSION["SESS_USER_NAME"];  else $_SESSION["user_name"]="Guest";
		if(isset($_SESSION["SESS_PRIVILEGE_NAME"])) $_SESSION["privilegename"]=$_SESSION["SESS_PRIVILEGE_NAME"];  else $_SESSION["privilege_name"]="Guest";
		
		$q=preg_replace_callback("/#[a-zA-Z0-9-_]+#/","replaceFromEnviroment",$q);
		$q=preg_replace_callback("/:[a-z]+:/","parseRelation",$q);
		
		unset($_SESSION["privilege"]);unset($_SESSION["username"]);unset($_SESSION["privilegename"]);
		
		return $q;
	}
}
if(!function_exists('parseSQLWhere')) {
	function parseSQLWhere($where) {
		if(!is_array($whr)) {
			$wA=explode(",",$where);
		} else {
			$wA=$where;
		}
		foreach($wA as $a1=>$a2) {
			$a3=preg_split("/(:[a-zA-Z0-9]+:)/",$a2,-1,PREG_SPLIT_DELIM_CAPTURE);
			if(!isset($a3[2]) || $a3[2]==null || $a3[2]=="") {
				$wA[$a1]="({$a3[0]} IS NULL OR {$a3[0]}='') ";
				continue;
			} elseif($a3[2][0]=="@") {
				$a3[2]=_replace("#".substr($a3[2], 1)."#");
			}
			$wA[$a1]=getRelation(substr($a3[1], 1,strlen($a3[1])-2),$a3[0],$a3[2]);
		}
		$where=implode(" AND ",$wA);
		return $where;
	}
}
if(!function_exists('parseRelation')) {
	function parseRelation($func) {
		if(is_array($func)) $func=$func[0];
		$func=substr($func,1,strlen($func)-2);
		if($func=="eq") {
			return "=";
		} elseif($func=="ne" || $func=="neq") {
			return "<>";
		} elseif($func=="lt") {
			return "<";
		} elseif($func=="le") {
			return "<=";
		} elseif($func=="gt") {
			return ">";
		} elseif($func=="ge") {
			return ">=";
		}
		return "";
	}
}
if(!function_exists('getRelation')) {
	function getRelation($func,$col,$value) {
		$r=$value;
		if(!is_numeric($value)) {
			$r="'$value'";
		}
		if($func=="eq") {
			return "$col= $r";
		} elseif($func=="ne" || $func=="neq") {
			return "$col<> $r";
		} elseif($func=="bw") {
			return "$col LIKE '$value%'";
		} elseif($func=="bn") {
			return "$col NOT LIKE '$value%'";
		} elseif($func=="ew") {
			return "$col LIKE '%$value'";
		} elseif($func=="en") {
			return "$col NOT LIKE '%$value'";
		} elseif($func=="cn") {
			return "$col LIKE '%$value%'";
		} elseif($func=="nc") {
			return "$col NOT LIKE '%$value%'";
		} elseif($func=="in") {
			return "$col LIKE '%$value%'";
		} elseif($func=="ni") {
			return "$col NOT LIKE '%$value%'";
		} elseif($func=="lt") {
			return "$col<$r";
		} elseif($func=="le") {
			return "$col<=$r";
		} elseif($func=="gt") {
			return "$col>$r";
		} elseif($func=="ge") {
			return "$col>=$r";
		} elseif($func=="nn") {
			return "$col IS NOT NULL";
		} elseif($func=="nu") {
			return "$col IS NULL";
		}
		return "$col=$value";
	}
}
?>
