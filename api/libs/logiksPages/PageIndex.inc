<?php
/*
 * LogiksTheme is central to the new theme engine that comes now with Logiks v3.6.6+
 * LogiksTheme will encompass the older skining and theming abilities along with enhancing
 * the UX experiance.
 *
 * Author: Bismay Kumar Mohapatra bismay4u@gmail.com on 24/02/2015
 * Version: 1.0
 */
if(!defined('ROOT')) exit('No direct script access allowed');

class PageIndex {

	public static function findPageFromURL($site=null) {
		if($site==null) $site=SITENAME;

		$page=str_replace("?"._server('QUERY_STRING'),"",_server('REQUEST_URI'));
		$page=str_replace(InstallFolder, "", $page);
		if(substr($page, 0,1)=="/") $page=substr($page, 1);
		if($page==null || strlen($page)<=0) {
			$page="home";
		}

		return $page;
	}
}
?>