<?php
/*
 * This file contains the all group related functionalities
 * 
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');


if(!function_exists("getGroup")) {
	function getGroupInfo($groupid) {
		$sql=_db(true)->_selectQ(_dbTable("users_group",true),"*")->_where(array(
				"id"=>$groupid,
			));
		
		if(!isset($_SESSION['SESS_PRIVILEGE_ID']) || $_SESSION['SESS_PRIVILEGE_ID']>ROLE_PRIME) {
			$sql->_where(["guid"=>$data['SESS_GUID']]);
		}
		
		$data=$sql->_GET();
		if($data) return $data[0];
		else return false;
	}
}
?>