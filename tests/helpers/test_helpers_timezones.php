<?php
/*
 * For testing logiks core helper : timezones
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com
 * @date 	2015-11-12
 */
class test_helpers_timezones extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("timezones");
	}
	
	public function test_getTimeZones() {
		$tz = getTimeZones();
		$this->assertEquals('Asia/Kolkata',$tz[272]);
	}
	
	public function test_getUTCZones() {
		$tz = getUTCZones();
		$this->assertEquals(5.5,$tz['UP55']);
	}
	
	public function test_get_TimeZones_UTCZones_map() {
		$tz = get_TimeZones_UTCZones_map();
		$this->assertEquals('+5.5',$tz['Asia/Kolkata']);
	}
}
?>
