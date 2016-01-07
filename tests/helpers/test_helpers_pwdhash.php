<?php
/*
 * For testing logiks core helper : pwdhash
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com>
 * @date 	2015-11-12
 */
class test_helpers_pwdhash extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("pwdhash");
	}
	
	public function test_HashType() {
		return getConfig("PWD_HASH_TYPE");
	}
	
	/**
	 * @depends test_HashType
	 */
	public function test_getPWDHash($encyption_algo) {
		$actual = getPWDHash('test');
		if($encyption_algo != 'pwdhash') {
			$expected = call_user_func($encyption_algo,'test');
		} else {
			$expected = PwdHash::hash('test');
		}
		$this->assertEquals($expected,$actual);
	}
	
	/**
	 * @depends test_HashType
	 */
	public function test_matchPWD($encyption_algo) {
		
		$algo_actual = getPWDHash('test');
		
		$first = matchPWD($algo_actual,'test');
		$this->assertEquals(true,$first);
		
		$first = matchPWD($algo_actual,'testing');
		$this->assertEquals(false,$first);
		
	}
	
}
?>
