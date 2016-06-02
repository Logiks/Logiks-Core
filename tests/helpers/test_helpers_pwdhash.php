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
		if($encyption_algo != 'pwdhash' && $encyption_algo != 'logiks') {
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
		$salt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		$algo_actual = getPWDHash('test',$salt);
		
		$first = matchPWD($algo_actual,'test',$salt);
		$this->assertEquals(true,$first);
		
		$first = matchPWD($algo_actual,'testing');
		$this->assertEquals(false,$first);
		
	}
	
}
?>
