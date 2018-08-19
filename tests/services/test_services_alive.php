<?php
/*
 * For testing logiks core helper : alive
 * 
 * @author	Bismay <bismay4u@gmail.com>
 * @date 	2018-08-16
 */
class test_helpers_alive extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		//loadHelpers("browser");
	}
	
	public function test_alive() {
	    $ans = $this->http_get("http://localhost/services/alive?site=cms");
		$result = $ans[0];
	    
	    $time=date("r");
	    
	    $this->assertEquals("data: SERVER {$time}",trim($result));
	}
}
?>

