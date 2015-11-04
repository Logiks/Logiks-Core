<?php
class test_generic extends PHPUnit_Framework_TestCase
{
	public function testEmpty()
    {
        $this->assertEquals(1, 0);
    }
    
	/**
     * @depends testEmpty
     */
    public function testEquality() {
        $this->assertTrue(true);
    }
}
?>
