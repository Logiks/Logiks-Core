<?php
class test_generic extends LogiksTestCase {
	public function testROOT() {
        $this->assertTrue(defined("ROOT"));
    }

    public function testFuncLoading() {
    	$this->assertTrue(function_exists("loadHelpers"));
    }
    public function testPrime() {
    	$this->assertTrue(function_exists("_server"));
    }
}
?>
