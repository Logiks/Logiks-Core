<?php
if(!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(!isset($_REQUEST['action'])) {
	exit("Form Action Not Specified");
}

//echo "Error::";
//printArray($_POST);
//printArray($_GET);
//exit();

$action=$_REQUEST['action'];
unset($_REQUEST['action']);
//exit("Error:: $action");
//multitablesubmit,multiinserts
if($action=='load') {
	loadForm();
} elseif($action=='autoload') {
	autoloadForm();
} elseif($action=='mail') {
	mailForm();
} elseif($action=='dbmail') {
	saveToDB();
	mailForm();
} elseif($action=='submit') {
	saveToDB();
} elseif($action=='delete') {
	deleteForm();
} elseif($action=='updatebyid') {
	updateFormById();
} elseif($action=='unique') {
	checkUnique();
} else {
	echo "Action Not Recognized";
}
exit();

function autoloadForm() {
	$dataPost=$_POST;

	printArray($dataPost);
}
function loadForm() {
	$dataPost=$_POST;

	if(isset($dataPost["frmID"])) {
		$sForm=$dataPost["frmID"];
		unset($dataPost["frmID"]);
	} else $sForm=null;

	$sTable="";$sWhere="";$sCols="";

	$sql="";
	if($sForm!=null && strlen($sForm)>0) {
		$tbl=_dbtable("forms");
		$sql="SELECT submit_table,submit_wherecol,datatable_cols FROM $tbl WHERE id=$sForm AND blocked='false'";

		$res1=_dbQuery($sql);
		if($res1 && _db()->recordCount($res1)>0) {
			$data=_db()->fetchData($res1);
			$sTable=$data["submit_table"];
			$sWhere=$data["submit_wherecol"];
			$sCols=$data["datatable_cols"];
		}
	} elseif(isset($dataPost["submit_table"]) && isset($dataPost["submit_wherecol"])) {
		$sTable=$dataPost["submit_table"];
		$sWhere=$dataPost["submit_wherecol"];
		if(isset($dataPost["frmCols"])) {
			$sCols=$dataPost["frmCols"];
			unset($dataPost["frmCols"]);
		}
		unset($dataPost["submit_table"]);
		unset($dataPost["submit_wherecol"]);
	} elseif(isset($dataPost["table"]) && isset($dataPost["wherecols"])) {
		$sTable=$dataPost["table"];
		$sWhere=$dataPost["wherecols"];
		if(isset($dataPost["cols"])) {
			$sCols=$dataPost["cols"];
			unset($dataPost["cols"]);
		}
		unset($dataPost["table"]);
		unset($dataPost["wherecols"]);
	}

	$sWhere=explode(",",$sWhere);
	$arr=array();
	foreach($sWhere as $a=>$b) {
		if(isset($dataPost[$b])) {
			$arr[$b]=$dataPost[$b];
		}
	}
	$sWhere=$arr;
	$sWhere=generateWhere($sWhere);
	
	$tblCols=_db()->getTableInfo($sTable);
	$arrCols=array();
	foreach($tblCols[0] as $a=>$b) {
		$arrCols[$b]=$tblCols[1][$a];
	}
	$tblCols=$arrCols;

	if($sCols==null && strlen(trim($sCols))<=0) {
		$sCols=array();
		foreach($dataPost as $a=>$b) {
			if(array_key_exists($a,$tblCols)) array_push($sCols,$a);
		}
		$sCols=implode(",",$sCols);
	} else {
		$tempCols=explode(",",$sCols);
		$sCols=array();
		foreach($tempCols as $a) {
			if(array_key_exists($a,$tblCols)) array_push($sCols,$a);
		}
		$sCols=implode(",",$sCols);
	}
	if(strlen($sCols)<=0) exit("");
	$sql="SELECT $sCols FROM $sTable";
	if(strlen($sWhere)>0) {
		$sql.=" WHERE {$sWhere}";
	}

	$res1=_dbQuery($sql);
	if($res1 && _db()->recordCount($res1)>0) {
		$data=_db()->fetchData($res1);
		foreach($data as $a=>$b) {
			if($b==null || $b=="null") {
				$data[$a]="";
			} elseif($tblCols[$a]=="date") {
				$data[$a]=_pDate($b);
			} elseif($tblCols[$a]=="datetime") {
				$b=explode(" ",$b);
				$data[$a]=_pDate($b[0])." {$b[1]}";
			}
		}
		if(!isset($_REQUEST["format"])) $_REQUEST["format"]="json";
		//printFormattedArray($data);
		printServiceMsg($data);
	}
}
function mailForm() {
	include "mail.php";
}

function saveToDB() {
	if(isset($_REQUEST["on_success"])) $onSuccess=$_REQUEST["on_success"]; else $onSuccess="";
	if(isset($_REQUEST["on_error"])) $onFailure=$_REQUEST["on_error"]; else $onFailure="";

	$sysDb=false;
	$fFormat=str_replace("yy","Y",getConfig("DATE_FORMAT"));
	$date=date($fFormat);

	$oriData=array();
	$oriData["date"]=$date;
	$oriData["doc"]=$date;
	$oriData["doe"]=$date;
	$oriData["time"]=date(getConfig("TIME_FORMAT"));
	$oriData["toc"]=date(getConfig("TIME_FORMAT"));
	$oriData["toe"]=date(getConfig("TIME_FORMAT"));
	$oriData["tsoc"]=$oriData["dtoc"]=date($fFormat." ".getConfig("TIME_FORMAT"));
	$oriData["tsoe"]=$oriData["dtoe"]=$oriData["dtoc"];
	$oriData["last_modified"]=$oriData["dtoc"];

	$usr=getUserInfo();
	$oriData["username"]=$usr["SESS_USER_NAME"];
	$oriData["userid"]=$usr["SESS_USER_ID"];
	$oriData["privilegeid"]=$_SESSION["SESS_PRIVILEGE_ID"];
	$oriData["scanBy"]=$_SESSION["SESS_USER_ID"];
	$oriData["submittedby"]=$usr["SESS_USER_ID"];
	$oriData["createdBy"]=$usr["SESS_USER_ID"];
	$oriData["site"]=SITENAME;

	$dataPost=$_POST;

	if(isset($_REQUEST["frmMode"]))
		$sMode=$_REQUEST["frmMode"];
	else
		$sMode="updateinsert";

	if(isset($dataPost["frmID"])) $sForm=urldecode($dataPost["frmID"]); else $sForm="-1";
	$sTable=urldecode($dataPost["submit_table"]);
	$sWhereCol=urldecode($dataPost["submit_wherecol"]);

	if(strpos("#".$sTable,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
		$sysDb=true;
	}

	$tblCols=_db($sysDb)->getTableInfo($sTable);
	if($tblCols==null) exit("Error::Source DataTable Not Found.");

	unset($dataPost["frmID"]);
	unset($dataPost["submit_table"]);
	unset($dataPost["submit_wherecol"]);

	if(isset($dataPost[$sWhereCol]) && $dataPost[$sWhereCol]=="-1") {
		exit("No Data Found");
	}

	$sql="";
	if($sMode=="update") {
		$sWhereCol=explode(",",$sWhereCol);
		$arr=array();
		foreach($sWhereCol as $a=>$b) {
			if(isset($dataPost[$b])) {
				$arr[$b]=$dataPost[$b];
				unset($dataPost[$b]);
			}
		}
		$sWhereCol=$arr;

		$sql=generateUpdate($sTable,$tblCols,$oriData,$dataPost,$sWhereCol);
	} elseif($sMode=="insert") {
		$sql=generateInsert($sTable,$tblCols,$oriData,$dataPost);
	} elseif($sMode=="updateinsert") {
		$dataPost1=$dataPost;
		$sWhereCol=explode(",",$sWhereCol);
		$arr=array();
		foreach($sWhereCol as $a=>$b) {
			if(isset($dataPost[$b])) {
				$arr[$b]=$dataPost[$b];
				unset($dataPost[$b]);
			}
		}
		$sWhereCol=$arr;

		$q="SELECT count(*) as cnt FROM $sTable where ".generateWhere($sWhereCol);
		$res1=_dbQuery($q,$sysDb);
		if($res1 && _db()->recordCount($res1)>0) {
			$data=_db()->fetchData($res1);
			_db($sysDb)->freeResult($res1);
			if($data["cnt"]>0) {
				$sql=generateUpdate($sTable,$tblCols,$oriData,$dataPost,$sWhereCol);
				$sMode="update";
			} else {
				$sql=generateInsert($sTable,$tblCols,$oriData,$dataPost1);
				$sMode="insert";
			}
		} else {
			$sql=generateInsert($sTable,$tblCols,$oriData,$dataPost1);
			$sMode="insert";
		}
	}
	if($sMode=="update" && count($sWhereCol)<=0) {
		echo "Error:: Where Condition Not Satisfied For Update Query.";
		exit();
	}
	//exit("Error:: $sql");
	if(strlen($sql)>0) {
		$a=_dbQuery($sql,$sysDb);
		if($a) {
			if($sMode=="insert") {
				printResult("$sTable",_db($sysDb)->insert_id());
			} else {
				if(_db($sysDb)->affected_rows()>0) {
					if(in_array("last_modified",$tblCols[0]) && in_array("id",$tblCols[0])) {
						$q="SELECT id FROM $sTable ORDER BY last_modified DESC LIMIT "._db($sysDb)->affected_rows();
						$b=_dbQuery($q,$sysDb);
						if($b) {
							$data=_dbData($b);
							_db($sysDb)->freeResult($b);
							$ids=array();
							foreach($data as $a=>$b) {
								array_push($ids,$b['id']);
							}
							$ids=implode(",",$ids);
							printResult("$sTable","$ids");
						}
					} elseif(in_array("tsoe",$tblCols[0]) && in_array("id",$tblCols[0])) {
						$q="SELECT id FROM $sTable ORDER BY tsoe DESC LIMIT "._db($sysDb)->affected_rows();
						$b=_dbQuery($q,$sysDb);
						if($b) {
							$data=_dbData($b);
							_db($sysDb)->freeResult($b);
							$ids=array();
							foreach($data as $a=>$b) {
								array_push($ids,$b['id']);
							}
							$ids=implode(",",$ids);
							printResult("$sTable","$ids");
						}
					} elseif(count($sWhereCol)>0) {
						$where=generateWhere($sWhereCol);
						$q="SELECT id FROM $sTable WHERE $where";
						$b=_dbQuery($q,$sysDb);
						if($b) {
							$data=_dbData($b);
							_db($sysDb)->freeResult($b);
							$ids=array();
							foreach($data as $a=>$b) {
								array_push($ids,$b['id']);
							}
							$ids=implode(",",$ids);
							printResult("$sTable","$ids");
						}
					}
				} else {
					$where=generateWhere($sWhereCol);
					$q="SELECT id FROM $sTable WHERE $where";
					$b=_dbQuery($q,$sysDb);
					if($b) {
						$data=_dbData($b);
						_db($sysDb)->freeResult($b);
						$ids=array();
						foreach($data as $a=>$b) {
							array_push($ids,$b['id']);
						}
						$ids=implode(",",$ids);
						printResult("$sTable","$ids");
					}
				}
			}
			if($sysDb) {
				initUserCredentials();
			}
			if(function_exists($onSuccess)) {
				call_user_func($onSuccess);
			} else {
				echo $onSuccess;
			}
			if(function_exists("log_ActivityEvent")) log_ActivityEvent("FORM Submited Success ::$sForm/$sTable, For ID::"._db()->insert_id(),"User",4,"forms",_dbtable("forms"));
		} else {
			$stmt=explode(" ",trim($sql));
			$stmt=strtoupper($stmt[0]);

			if($GLOBALS['DBCONFIG']["DB_READ_ONLY"]=="true") {
				echo "Error:: DBMS In ReadOnly Mode. No New Data Will Be Added Or Deleted From System.<br/>Please Contact Server Administrator.";
			} elseif(strpos(strtoupper("##".$GLOBALS['DBCONFIG']["BLOCK_STATEMENTS"]),$stmt)>1) {
				echo "Error:: Following Database Operation Is Prohibitted On DBMS By Server Administrator.<br/>Please Contact Server Administrator.";
			} else {
				if(strlen($onFailure)>0) {
					echo $onFailure;
				} else {
					echo "Error:: "._db($sysDb)->getError()."<br/>";
				}
				if(MASTER_DEBUG_MODE=='true') echo _db($sysDb)->getError();
				if(function_exists("log_ActivityEvent")) log_ActivityEvent("FORM Submit Failed ::$sForm/$sTable","User",4,"forms",_dbtable("forms"));
			}
		}
	} else {
		if(strlen($onFailure)>0) {
			echo $onFailure;
		} else {
			echo "Error:: "._db($sysDb)->getErrorNo()."<br/>";
		}
		if(MASTER_DEBUG_MODE=='true') echo _db($sysDb)->getError();
		if(function_exists("log_ActivityEvent")) log_ActivityEvent("FORM SQL Creation Error ::$sForm/$sTable","User",4,"forms",_dbtable("forms"));
	}
}
function updateFormById() {
	$sTable=$_POST["submit_table"];
	unset($_POST["submit_table"]);

	$id=$_POST["update_id"];
	unset($_POST["update_id"]);

	$sql="UPDATE $sTable SET ";
	foreach($_POST as $a=>$b) {
		$sql.="{$a}='{$b}'";
	}
	$sql.=" WHERE id=$id";

	$sysDb=false;
	if(strpos("#".$sTable,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
		$sysDb=true;
	}
	//exit("Error:: $sql");
	if(strlen($sql)>0) {
		$a=_dbQuery($sql,$sysDb);
		if($a) {
			if(function_exists("log_ActivityEvent")) log_ActivityEvent("FORM Update Success ::$sTable, For ID::$id","User",4,"forms",_dbtable("forms"));
		} else {
			if(function_exists("log_ActivityEvent")) log_ActivityEvent("FORM Update Failed ::$sTable, For ID::$id","User",4,"forms",_dbtable("forms"));
		}
	}
}
function deleteForm() {
	if(isset($_REQUEST["on_success"])) $onSuccess=$_REQUEST["on_success"]; else $onSuccess="";
	if(isset($_REQUEST["on_error"])) $onFailure=$_REQUEST["on_error"]; else $onFailure="";

	$sTable=$_POST["submit_table"];

	$sql="";
	if(isset($_POST["delete_id"])) {
		$id=clean($_POST["delete_id"]);
		$sql="DELETE FROM $sTable WHERE id=$id";
	} elseif(isset($_POST["submit_wherecol"])) {
		$whereCol=$_POST["submit_wherecol"];
		$whereCol=explode(",",$whereCol);
		$w=array();
		foreach($whereCol as $a=>$b) {
			if(isset($_POST[$b])) {
				$x=clean($_POST[$b]);
				$sw="$b='$x'";
				array_push($w,$sw);
			}
		}
		$where=implode("AND ",$w);
		$sql="DELETE FROM $sTable WHERE $where";
	}
	//exit("Error:: $sql");
	if(strlen($sql)>strlen("DELETE FROM $sTable WHERE ")) {
		$a=_dbQuery($sql);
		if($a) {
			if(function_exists($onSuccess)) {
				call_user_func($onSuccess);
			} else {
				echo $onSuccess;
			}
		} else {
			if(strlen($onFailure)>0) {
				echo $onFailure;
			} else {
				echo "Error:: "._db($sysDb)->getErrorNo()."<br/>";
			}
			if(MASTER_DEBUG_MODE=='true') echo _db($sysDb)->getError();
		}
	} else {
		if(strlen($onFailure)>0) {
			echo $onFailure;
		} else {
			echo "Error:: "._db($sysDb)->getErrorNo()."<br/>";
		}
		if(MASTER_DEBUG_MODE=='true') echo _db($sysDb)->getError();
	}
	if(function_exists("log_ActivityEvent")) {
		if(isset($_POST["delete_id"])) log_ActivityEvent("Record Form Deleted From Table $sTable, For ID::$id","User",4,"forms",_dbtable("forms"));
		else log_ActivityEvent("Record Form Deleted For Query $sql","User",4,"forms",_dbtable("forms"));
	}
}

function checkUnique() {
	$tbl=$_REQUEST["tbl"];
	$col=$_REQUEST["col"];
	$term=clean($_REQUEST["term"]);
	$sql="SELECT count(*) FROM $tbl where $col='$term'";

	$sysDb=false;
	if(strpos("#".$tbl,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
		$sysDb=true;
	}
	$a=_dbQuery($sql,$sysDb);
	if($a) {
		$b=_dbData($a);
		if($b[0]["count(*)"]<1) echo "unique";
		else echo "not unique";
		_db($sysDb)->freeResult($a);
	} else {
		echo "error";
	}
}

//Other Functions
function generateInsert($table,$columns,$oriData,$dataPost) {
	$cols=array();
	$data=array();
	for($k=0;$k<sizeof($columns[0]);$k++) {
		$col=clean($columns[0][$k]);
		if(isset($dataPost[$col])) {
			$val=$dataPost[$col];
			array_push($data,getData($val,$columns[1][$k]));
			array_push($cols,$col);
		} elseif(isset($oriData[$col])) {//in_array($col,$oriKeys)
			array_push($data,getData($oriData[$col],$columns[1][$k]));
			array_push($cols,$col);
		}
	}
	$sql="INSERT INTO $table (".implode(",",$cols).") VALUES (".implode(",",$data).")";

	return $sql;
}

function generateUpdate($table,$columns,$oriData,$dataPost,$whereCols) {
	unset($oriData["doc"]);
	unset($oriData["toc"]);
	unset($oriData["tsoc"]);
	$sql="UPDATE $table SET ";
	$oriKeys=array_keys($oriData);
	$wKeys=array_keys($whereCols);
	for($k=0;$k<sizeof($columns[0]);$k++) {
		$val="";
		$col=$columns[0][$k];
		if(in_array($col,$wKeys)) {

		} elseif(isset($dataPost[$col])) {
			$val=$dataPost[$col];
			$val=getData($val,$columns[1][$k]);
			$sql.="$col=$val, ";
		} elseif(in_array($col,$oriKeys)) {
			$val=$oriData[$col];
			$val=getData($val,$columns[1][$k]);
			$sql.="$col=$val, ";
		}
	}
	$sql=trim($sql);
	if(strpos($sql,",",strlen($sql)-1)==(strlen($sql)-1)) {
		$sql=substr($sql,0,strlen($sql)-1);
	}
	$sqlWhere=generateWhere($whereCols);
	if(strlen($sqlWhere)>0) {
		$sql="$sql WHERE $sqlWhere";
	}
	return $sql;
}

function generateWhere($whereCols) {
	$arr=array();
	foreach($whereCols as $a=>$b) {
		$b=urldecode($b);
		array_push($arr,"$a='$b'");
	}
	return implode(" AND ",$arr);
}

function getData($s,$type) {
	if(is_array($s)) {
		$s=implode(",",$s);
	}
	$s=urldecode($s);
	//$s=str_replace("'","`",$s);
	$s=mysql_real_escape_string($s);
	if($type=="int" || $type=="float" || $type=="bool") {
		if(strlen($s)<=0) return "0";
		else return "$s";
	} elseif($type=="date") {
		$s=_date($s);
		return "'$s'";
	} elseif($type=="time") {
		$s=_time($s);
		return "'$s'";
	} elseif($type=="datetime") {
		$ss=explode(" ",$s);
		$s0=_date($ss[0]);
		if(isset($ss[1]))
			$s1=_time($ss[1]);
		else
			$s1="";
		return "'$s0 $s1'";
	} else {
		return "'$s'";
	}
}
function printResult($frmTable,$id,$idCol="id",$msg="") {
	$arr=array("formTable"=>$frmTable,"idVal"=>$id,"idCol"=>$idCol,"msg"=>$msg);
	if(isset($_REQUEST['response'])) {
		if($_REQUEST['response']=="json") echo json_encode($arr);
		elseif($_REQUEST['response']=="text") {
			echo "$idCol:$id";
			if(strlen($msg)>0) echo $msg;
		}
	}
}
?>
