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
	
	public function test_getPWDHash() {
		setConfig("PWD_HASH_TYPE","md5");
		
		$this->assertEquals(getPWDHash('test'),md5(md5('test')));

		setConfig("PWD_HASH_TYPE","sha1");
		$this->assertEquals(getPWDHash('test'),sha1(md5('test')));
	}

	public function test_matchPWD() {
		setConfig("PWD_HASH_TYPE","logiks");

		$salt=strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');

		$algo_actual = getPWDHash('test',$salt);
		if(is_array($algo_actual)) $algo_actual=$algo_actual['hash'];
		
		$first = matchPWD($algo_actual,'test',$salt);
		$this->assertEquals(true,$first);
		
		$first = matchPWD($algo_actual,'testing',$salt);
		$this->assertEquals(false,$first);
	}
	
}
?>
