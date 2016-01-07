<?php
/*
 * For testing logiks core helper : countries
 * 
 * @author	Bismay K Mohapatra <bismay4u@gmail.com>
 * @date 	2015-11-12
 */
class test_helpers_countries extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("countries");
	}
	
	public function test_getCountryList() {
        $arr=getCountryList();
        
        $this->assertEquals($arr['AD'], "Andorra");
    }
    
    public function test_getLocaleList() {
    	$arr=getLocaleList();
        
        $this->assertTrue(($arr['kw'][0]=="Cornish" && $arr['mk'][0]=="Macedonian"));
    }
}
?>
