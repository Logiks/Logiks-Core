<?php
/*
 * UIComponent :: Data Selector From Database
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

if(!function_exists("createDataSelector")) {

	function createDataSelector($groupID, $orderBy=null) {
		$sqlObj=_db()->_selectQ("do_lists","title,value,class")
			->_where(array("groupid"=>$groupID,"blocked"=>"false"));

		if(isset($_SESSION['SESS_PRIVILEGE_HASH'])) {
			$sqlObj=$sqlObj->_where(array("privilege"=>"*"))
				->_where("(privilege='*' OR FIND_IN_SET('{$_SESSION['SESS_PRIVILEGE_HASH']}',privilege))");
		} else {
			$sqlObj=$sqlObj->_where(array("privilege"=>"*"));
		}

		if($orderBy==null) $sqlObj=$sqlObj->_orderby('title');
		elseif(strlen($orderBy)>0) $sqlObj=$sqlObj->_orderby($orderBy);

		return generateSelect(_dataSQL($sqlObj));
	}

	function createDataSelectorFromUniques($table, $col1, $col2=null, $where="", $orderBy="") {
		if(is_array($col2)) {
			$col2=implode(",", $col2);
		}
		if($col2!=null && strlen($col2)>0) {
			$sqlObj=_db()->_selectQ($table,"$col1,$col2")
				->_where(array("blocked"=>"false"));
		} else {
			$sqlObj=_db()->_selectQ($table,"$col1")
				->_where(array("blocked"=>"false"));
		}
		if($where!=null) {
			$sqlObj=$sqlObj->_where($where);
		}
		$sqlObj=$sqlObj->_where("length($col1)>0");

		// if(isset($_SESSION['SESS_PRIVILEGE_ID'])) {
		// 	$sqlObj=$sqlObj->_where(array("privilege"=>"*"))
		// 		->where("(privilege='*' OR FIND_IN_SET('{$_SESSION['SESS_PRIVILEGE_NAME']}',privilege))");
		// } else {
		// 	$sqlObj=$sqlObj->_where(array("privilege"=>"*"));
		// }

		$colx=$col1;
		if(strpos($colx,"(")>0) $colx=$col2;

		$sqlObj=$sqlObj->_groupby($colx);

		if($orderBy==null) $sqlObj=$sqlObj->_orderby($colx);
		elseif(strlen($orderBy)>0) $sqlObj=$sqlObj->_orderby($orderBy);

		return generateSelect(_dataSQL($sqlObj),null,null,$col1);
	}

	function createDataSelectorFromTable($table, $columns, $where=null, $groupBy=null,$orderBy=null) {
		$sqlObj=_db()->_selectQ($table,$columns)
				->_where(array("blocked"=>"false"));

		if($where!=null) {
			$sqlObj=$sqlObj->_where($where);
		}
		
		// if(isset($_SESSION['SESS_PRIVILEGE_ID'])) {
		// 	$sqlObj=$sqlObj->_where(array("privilege"=>"*"))
		// 		->where("(privilege='*' OR FIND_IN_SET('{$_SESSION['SESS_PRIVILEGE_NAME']}',privilege))");
		// } else {
		// 	$sqlObj=$sqlObj->_where(array("privilege"=>"*"));
		// }

		if(strlen($groupBy)>0) $sqlObj=$sqlObj->_groupby($groupBy);

		if(strlen($orderBy)>0) $sqlObj=$sqlObj->_orderby($orderBy);

		return generateSelect(_dataSQL($sqlObj));
	}
}
?>
