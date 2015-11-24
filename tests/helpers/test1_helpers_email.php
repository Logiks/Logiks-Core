<?php
/*
 * For testing logiks core helper : email
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com
 * @date 	2015-11-12
 */
class test_helpers_email extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("email");
	}
	
	public function test_isValidEmailValid1() {
		$result = isValidEmail('test@example.com');
		$this->assertEquals(true,$result);
	}
	
	public function test_isValidEmailValid2() {
		$result = isValidEmail('test@example');
		$this->assertEquals(false,$result);
	}
	
	public function test_isValidEmailValid3() {
		$result = isValidEmail('test@example@!com');
		$this->assertEquals(false,$result);
	}
	
}
?>
