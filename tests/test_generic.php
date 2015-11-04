<?php
class test_generic extends LogiksTestCase
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
