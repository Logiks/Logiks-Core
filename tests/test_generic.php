<?php
class test_generic extends LogiksTestCase
{
	public function testROOT()
    {
        $this->assertTrue(defined("ROOT"));
    }

    public function testFunc() {
    	$this->assertTrue(function_exists("loadHelpers"));
    }
}
?>
