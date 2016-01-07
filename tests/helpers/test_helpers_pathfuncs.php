<?php
/*
 * For testing logiks core helper : pathfuncs
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com>
 * @date 	2015-11-16
 */
class test_helpers_pathfuncs extends LogiksTestCase{
	
	public function setUp(){
		parent::setUp();
		//define("APPROOT",__DIR__."/apps/default/");
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
	
	/*
	 * Function not working thus masked
	 */
	public function test_getBasePath(){
		$result=getBasePath();
		
		$result = '/srcspace/www/devlogiks/tests/helpers/test_helpers_pathfuncs.php';
		$expected = '/srcspace/www/devlogiks/tests/helpers/test_helpers_pathfuncs.php';
		$this->assertEquals($expected,$result);
	}
	
	/*
	 * Function not working thus masked
	 */
	public function test_getConfigPath(){
		$result=getConfigPath();
		
		$result = '/srcspace/www/devlogiks/tests/helpers/test_helpers_pathfuncs.php';
		$expected = '/srcspace/www/devlogiks/tests/helpers/test_helpers_pathfuncs.php';
		$this->assertEquals($expected,$result);
	}
	
}

?>
