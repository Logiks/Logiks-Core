<?php
/*
 * Attachment related functions.
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Author: Kshyana Prava kshyana23@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("deleteAttachments")) {
	function deleteAttachments() {
		//printArray($_POST);
		$error=array();
		if(strlen($_POST['src'])==0) {
			$_POST['src']="fs#attachments/";
		}
		if(isset($_POST['forTable'])) $forTable=$_POST['forTable']; else $forTable="";
		if(isset($_POST['forIDCol'])) $forIDCol=$_POST['forIDCol']; else $forIDCol="id";
		if(isset($_POST['forIDVal'])) $forIDVal=$_POST['forIDVal']; else $forIDVal="";
		if(isset($_POST['name'])) $targetCol=$_POST['name']; else $targetCol="";
		if(isset($_POST['path'])) $path=$_POST['path']; 
		else {
			$error["Error:FilePath"]="FilePath Not Found.";
			return $error;
		}
		
		if(strpos("#".$forTable,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
			$sysDb=true;
		} else {
			$sysDb=false;
		}
		
		if(strpos($_POST['src'],"fs#")===0) {
			$storePath=substr($_POST['src'],3);
			$storeType="fs";
			if(strlen($storePath)<=0) $storePath="attachments/";
		} elseif(strpos($_POST['src'],"db#")===0) {
			$storePath=substr($_POST['src'],3);
			$storeType="db";
			if(strlen($storePath)<=0) $storePath=_dbtable("files");
		} else {
			$file['src']="fs#attachments/";
			$storePath=substr($_POST['src'],3);
			$storeType="fs";
			if(strlen($storePath)<=0) $storePath="attachments/";
		}
		if($storeType=="fs") {
			$targetPath= APPROOT.APPS_USERDATA_FOLDER."{$path}";
			if(file_exists($targetPath)) {
				$a=unlink($targetPath);
				if(!$a) {
					$error["Error:PhysicalDelete"]="Target File Failed To Delete.";
				}
			}
		} elseif($storeType=="db") {
			if(strpos("#".$storePath,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
				$sysDb1=true;
			} else {
				$sysDb1=false;
			}
			$deleteQuery="DELETE FROM $storePath WHERE id={$_POST['path']}";
			_dbQuery($deleteQuery,$sysDb1);
			$cnt=_db($sysDb1)->affected_rows();
			if($cnt<=0) {
				$sql="SELECT count(*) as cnt FROM $storePath WHERE id='{$_POST['path']}'";
				$res=_dbQuery($sql,$sysDb1);
				if($res) {
					$des=_dbData($res);
					_dbFree($res,$sysDb1);
					if(isset($des[0]['cnt']) && $des[0]['cnt']>0) {
						$error["Error:DataDelete"]="Source DBTable Failed To Update.";
					}
				} else {
					$error["Error:DataDelete"]="Source DBTable Error Link.";
				}
			}
		} else {
			$error["Error:StorageType"]="StorageType Not Supported.";
		}
		if(count($error)>0) {
			if(isset($error["Error:PhysicalDelete"]) || 
					isset($error["Error:DataDelete"])) {
				return $error;
			}
		}
		if(strlen($forTable)>0 && strlen($forIDCol)>0 && strlen($forIDVal)>0 && strlen($targetCol)>0) {
			$sqlUpdate="UPDATE {$forTable} SET {$targetCol}=replace(replace({$targetCol},'{$path}',''),',,',',') WHERE {$forIDCol}='{$forIDVal}'";
			//echo $sqlUpdate;
			_dbQuery($sqlUpdate,$sysDb);
			$cnt=_db($sysDb)->affected_rows();
			if($cnt<=0) {
				$error["Error:UpdateTarget"]="Target DBTable Failed To Update";
			}
		}
		return $error;
	}

	function processAttachments() {
		if(!isset($_POST['MAX_FILE_SIZE'])) {
			$_POST['MAX_FILE_SIZE']=getConfig("MAX_UPLOAD_FILE_SIZE");
		}
		if(!isset($_POST['IF_FILE_EXISTS'])) {
			$_POST['IF_FILE_EXISTS']="replace";
		}
		if(!isset($_POST['FILE_ACTION'])) {
			$_POST['FILE_ACTION']="create";
		}
		if(!isset($_POST['TEXT_EXTRACTION'])) {
			$_POST['TEXT_EXTRACTION']="true";
		}
		
		if(!isset($_POST['forTable']) || !isset($_POST['forIDVal'])) {
			echo "<script>parent.uploadComplete('Error In File Attachments!');</script>";
			return false;
		}
		$forTable=$_POST['forTable'];
		$forIDVal=$_POST['forIDVal'];
		if(isset($_POST['forIDCol'])) $forIDCol=$_POST['forIDCol']; else $forIDCol="id";
		
		$errors=array();
		$values=array();
		foreach($_FILES as $a=>$b) {
			$values[$a]=array();
			$src="";
			if(is_array($_POST[$a]) && count($_POST[$a])>0) $src=$_POST[$a][0];
			else $src=$_POST[$a];
			
			if(is_array($b['name'])) {
				foreach($b['name'] as $c=>$d) {
					$file=array("name"=>$b['name'][$c],"type"=>$b['type'][$c],"tmp_name"=>$b['tmp_name'][$c],"error"=>$b['error'][$c],"size"=>$b['size'][$c],"src"=>$src);
					if(strlen($file['name'])==0 && $file['size']==0 && strlen($file['tmp_name'])) {
						$values[$a]=null;
						continue;
					}
					$lnk=moveFile($file);
					if(is_array($lnk)) {
						if(isset($lnk['Error'])) $errors[$a.".".$c]=$lnk;
					} else {
						array_push($values[$a],$lnk);
					}
				}
			} else {
				$file=$b;
				if(strlen($file['name'])==0 && $file['size']==0 && strlen($file['tmp_name'])) {
					$values[$a]=null;
					continue;
				}
				$file["src"]=$src;
				$lnk=moveFile($file);
				if(is_array($lnk)) {
					if(isset($lnk['Error'])) $errors[$a.".".$b['name']]=$lnk;
				} else {
					array_push($values[$a],$lnk);
				}
			}
		}
		foreach($values as $a=>$b) {
			if($b!=null) {
				$values[$a]=implode(",",$b);
				$values[$a]="{$a}='{$values[$a]}'";
			} else {
				unset($values[$a]);
			}
		}
		$values=implode(", ",$values);
		
		if(strpos("#".$forTable,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
			$sysDb=true;
		} else {
			$sysDb=false;
		}
		$sqlUpdate="UPDATE {$forTable} SET {$values} WHERE {$forIDCol}='{$forIDVal}'";
		//echo $sqlUpdate;
		_dbQuery($sqlUpdate,$sysDb);
		//$cnt=_db($sysDb)->affected_rows();
		$cnt=count($errors);
		//if(count($errors)>0) {printArray($errors);}
		if($cnt>0) {
			$tms="Failed To Attach {$cnt} Documents.";
		} else {
			$tms="";
		}
		echo "<script>parent.uploadComplete('$tms');</script>";
	}
	function moveFile($file) {
		if(strlen($file['name'])==0 && $file['size']==0 && strlen($file['tmp_name'])==0) {
			return array();
		}
		$lnk="";
		$maxFileSize=$_POST['MAX_FILE_SIZE'];
		$ifFileExists=$_POST['IF_FILE_EXISTS'];//replace,noreplace
		$fileAct=$_POST['FILE_ACTION'];//create,replace,delete
		$storeTxtToDB=$_POST['TEXT_EXTRACTION'];//true,false,yes,no
		$storeType="fs";
		$storePath="attachments/";
		if(strlen($file['src'])==0) {
			$file['src']="fs#attachments/";
		}
		if(strpos($file['src'],"fs#")===0) {
			$storePath=substr($file['src'],3);
			$storeType="fs";
			if(strlen($storePath)<=0) $storePath="attachments/";
		} elseif(strpos($file['src'],"db#")===0) {
			$storePath=substr($file['src'],3);
			$storeType="db";
			if(strlen($storePath)<=0) $storePath=_dbtable("files");
		} else {
			$file['src']="fs#attachments/";
			$storePath=substr($file['src'],3);
			$storeType="fs";
			if(strlen($storePath)<=0) $storePath="attachments/";
		}
		$exts=explode(".",$file['name']);
		if(count($exts)>1)
			$ext=$exts[count($exts)-1];
		else
			$ext="";
		$fname=substr($file['name'],0,strlen($file['name'])-strlen($ext));
		if(strpos($fname,".")===strlen($fname)-1) $fname=substr($fname,0,strlen($fname)-1);
		
		if($storeType=="fs") {
			$newName = md5(rand() * time())."-".str_replace(" ","_",$fname);
			$targetPath= APPROOT.APPS_USERDATA_FOLDER."{$storePath}/{$newName}.{$ext}";
			$targetPath=str_replace("//","/",$targetPath);
			
			if(!file_exists(dirname($targetPath))) {
				mkdir(dirname($targetPath),0777,true);
				chmod(dirname($targetPath),0777);
			}
			if(!file_exists(dirname($targetPath))) {
				return array("Error"=>"Failed To Create TargetPath Folder.");
			}
			if(file_exists($targetPath)) {
				if($ifFileExists=="replace") {
					unlink($targetPath);
				} elseif($ifFileExists=="noreplace") {
					return array("Error"=>"File Exists At The Target.");
				}
			}
			if ($fileSize<$maxFileSize) {
				if(@move_uploaded_file($file['tmp_name'],$targetPath)) {
					return str_replace("//","/","{$storePath}/{$newName}.{$ext}");
				} else {
					return array("Error"=>"Failed To Move File To Destination.");
				}
			} else {
				return array("Error"=>"File Size Is More Then Max.");
			}
		} elseif($storeType=="db") {
			$date=date("Y-m-d");
			$usr=getUserInfo();
			
			$defData['date']=$date;
			$defData['time']=date('H:i:s');
			$defData['doc']=$date;
			$defData['doe']=$date;
			$defData['datestamp']=date('Y-m-d H:i:s');
			$defData['username']=$usr['SESS_USER_NAME'];
			$defData['userid']=$usr['SESS_USER_ID'];
			$defData['privilegeid']=$_SESSION['SESS_PRIVILEGE_ID'];
			$defData['scanBy']=$_SESSION['SESS_USER_ID'];
			$defData['site']=SITENAME;
			
			$fileName=$file['name'];
			$fileType=$file['type'];
			$fileSize=$file['size'];
			$fileData="";
			$meta="";
			$txtData="";
			
			if(isset($_POST['tags'])) $tags=$_POST['tags']; else $tags="";
			if(isset($_POST['remarks'])) $remarks=$_POST['remarks']; else $remarks="";
			
			if ($fileSize<$maxFileSize) {
				$fileData=file_get_contents($file['tmp_name']);
				$fileData=mysql_real_escape_string($fileData);
				if($storeTxtToDB || $storeTxtToDB=="true") {
					$txtData=getTextData($fileData,$fileType);
				}
			} else {
				return array("Error"=>"File Size Is More Then Max.");
			}
			if(strpos("#".$storePath,$GLOBALS["DBCONFIG"]["DB_SYSTEM"])==1) {
				$sysDb=true;
			} else {
				$sysDb=false;
			}
			$insertQuery="INSERT INTO $storePath ";
			$insertQuery.="(datestamp,title,txt_data,file_name,file_data,file_type,file_size,remarks,tags,meta,site,userid,doc,doe) VALUES ";
			$insertQuery.="('{$defData['datestamp']}','{$fname}',\"{$txtData}\",'{$fileName}',\"{$fileData}\",'{$fileType}','{$fileSize}','{$remarks}','{$tags}',";
			$insertQuery.="'{$meta}','{$defData['site']}','{$defData['userid']}','{$defData['doc']}','{$defData['doe']}')";
			//echo $insertQuery;
			
			$a=_dbQuery($insertQuery,$sysDb);
			
			if($a) {
				return _db($sysDb)->insert_id();
			}
			return array("Error"=>"Error In MySQL Query.");
		}
		return array("Error"=>"StorageType Not Supported.");
	}
	function getTextData($data,$type) {
		$txt="";
		if(strpos($type,"text")===0) $txt=substr($data,0,15000);
		$txt=str_replace("\"","`",$txt);
		return $txt;
	}
}
?>
