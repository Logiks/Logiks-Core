<?php
/*
 * For testing logiks core helper : mobility
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com
 * @date 	2015-11-16
 */
class test_helpers_mobility extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("cookies");
		loadHelpers("mobility");
	}
	
	public function test_getUserDevice() {
		$tz = getUserDevice();
		$expected = 'PC';
		$this->assertEquals($expected,$tz);
	}
	
	/*
	public function test_getUserDeviceType() {
		$tz = getUserDeviceType();
		$expected = 'PC';
		$this->assertEquals($expected,$tz);
	}
	*/
	
}
?>
