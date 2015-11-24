<?php
/*
 * For testing logiks core helper : urltools
 * 
 * @author	Rupali Dawkhar <dawkharrupali@gmail.com
 * @date 	2015-11-16
 */
class test_helpers_browser extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("browser");
	}
	
	public function test_checkBrowser() {
		$actual=array (
			'browser' => 'chrome',
			'version' => '46.0',
			'platform' => 'linux',
			'userAgent' => 'mozilla/5.0 (x11; linux i686) applewebkit/537.36 (khtml, like gecko) chrome/46.0.2490.80 safari/537.36',
		) ;
		$result = checkBrowser();
		$this->assertEquals($actual,$result);
	}
	
	
}
?>
