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
		setConfig("PWD_HASH_TYPE","md5");
		loadHelpers("pwdhash");
	}
	
	/**
	 * @depends test_HashType
	 */
	public function test_matchPWD($encyption_algo) {
		$salt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		$algo_actual = getPWDHash('test',$salt);
		
		$first = matchPWD($algo_actual,'test',$salt);
		$this->assertEquals(true,$first);
		
		$first = matchPWD($algo_actual,'testing',$salt);
		$this->assertEquals(false,$first);
		
	}
	
}
?>
