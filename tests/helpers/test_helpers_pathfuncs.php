<?php
/*
 * For testing logiks core helper : pathfuncs
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com>
 * @date 	2015-11-16
 */
class test_helpers_pathfuncs extends LogiksTestCase {
	
	public function setUp(){
		parent::setUp();
		if(!defined("APPROOT")) define("APPROOT",__DIR__."/apps/default/");

		if(!defined("APPS_USERDATA_FOLDER")) define("APPS_USERDATA_FOLDER","userdata/");
		if(!defined("APPS_CONFIG_FOLDER")) define("APPS_CONFIG_FOLDER","config/");

		loadHelpers("pathfuncs");
	}
	
	public function test_getWebPath(){
		$result=getWebPath(__FILE__);
		$expected = SiteLocation.str_replace(ROOT, "", dirname(__FILE__)."/".basename(__FILE__));
		$this->assertEquals($expected,$result);
	}
	
	public function test_getRootPath(){
		$result=getRootPath(__FILE__);
		$this->assertEquals($result,__FILE__);
	}
	
	public function test_getStoragePath(){
		$result=getStoragePath();
		
		$expected = APPROOT.'userdata/';
		$this->assertEquals($expected,$result);
	}
	
	public function test_getConfigPath(){
		$result=getConfigPath();
		
		$expected = APPROOT.'config/';
		$this->assertEquals($expected,$result);
	}
	
}

?>
