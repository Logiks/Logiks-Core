<?php
if (!defined('ROOT')) exit('No direct script access allowed');
if(!function_exists("fetchData")) {
	function fetchData($dbLink, $table,$colName,$where) {
		$sql="";
		$sql="select $colName from $table";
		if(is_array($where)) {
			$s=" WHERE";
			foreach($where as $a=>$b) {
				$s.=" $a='$b'";
				$s.=" AND";
			}
			$s=substr($s,0,strlen($s)-3);
			$sql.=$s;
		} elseif(strlen($where)>0) {
			$sql.=" WHERE " . $where;
		} else {
			$sql.=" where length($colName)>0";
		}
		$result=$dbLink->executeQuery($sql);
		$ans="";
		if($result){
			if(mysql_num_rows($result)>0){
				while($record=mysql_fetch_assoc($result)){
					$ans=$record[$colName];
				}
			}
		}
		$dbLink->freeResult($result);
		echo $ans;
	}
} 
?>
