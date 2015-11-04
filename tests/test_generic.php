<?php
class ArrayDiffTest1 extends PHPUnit_Framework_TestCase
{
	public function testEmpty()
    {
        $this->assertEquals(1, 1);
    }
    
	/**
     * @depends testEmpty
     */
    public function testEquality() {
        $this->assertTrue(true);
    }
}
?>
