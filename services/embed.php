<?php
//define('SERVICE_FOLDER',"services/");
if(!defined('ROOT')) exit('Direct Access Is Not Allowed');

include_once dirname(__FILE__) . "/config.php";
include_once dirname(__FILE__) . "/api.php";
include_once dirname(__FILE__) . "/security.php";
include_once dirname(__FILE__) . "/controller.php";

//exit( $_SERVER['QUERY_STRING']);
class ServiceEmbed {
	function processQuery($cmd,$type) {
		$request=array(
					"scmd"=>"$cmd",
					"ext"=>"$type",
				);
		$ctrl=new ServiceController();
		$secure=new ServiceSecurity();
		
		$request=$secure->checkSecurity($request);
		$request=$ctrl->cleanRequest($request);
		$ctrl->executeRequest($request);
	}
}
?>
