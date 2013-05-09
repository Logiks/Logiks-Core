<?php
if (!defined('ROOT')) exit('No direct script access allowed');
checkServiceSession();

if(!isset($_REQUEST['action'])) {
	exit("Form Action Not Specified");
}
$action=$_REQUEST['action'];
unset($_REQUEST['action']);

loadHelpers("attachments");

//photoattachements,fileattachments
if($action=='upload') {
	//printArray($_FILES);
	//printArray($_POST);
	processAttachments();
} elseif($action=='delete') {
	$arr=deleteAttachments();
	if(is_array($arr) && count($arr)>0) {
		echo "Error:: <br/>";
		printArray($arr);
	}
}
?>
