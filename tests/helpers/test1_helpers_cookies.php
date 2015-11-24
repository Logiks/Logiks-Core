<?php
/*
 * For testing logiks core helper : cookies
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com
 * @date 	2015-11-12
 */
class test_helpers_cookies extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("cookies");
	}
	
	public function test_createCookie() {
		createCookie('test','test');
		$this->assertEquals($_COOKIE['test'], "test");
	}
	
}
?>
