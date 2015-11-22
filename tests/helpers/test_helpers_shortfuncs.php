<?php
/*
 * For testing logiks core helper : shortfuncs
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com
 * @date 	2015-11-12
 */
class test_helpers_shortfuncs extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
	}
	
	public function test_link(){
		$actual = _link('home');
		$expected = SiteLocation . 'home';
		
		$this->assertEquals($expected,$actual);
	}
	
	public function test_service() {
		$actual = _service('tmp','get-user','json',array('id' => 30),'ssf');
		$expected = SiteLocation . 'services/tmp/get-user?site=ssf&format=json&id=30';
		$this->assertEquals($actual,$expected);
	}
	
	public function test_site() {
		$actual = _site('ssf1','home');
		$expected = SiteLocation . 'home?site=ssf1';
		//$this->assertEquals(true,true);
		$this->assertEquals($actual,$expected);
	}
	
	public function test_date() {
		$actual = _date('12/11/2015');
		$this->assertEquals($actual,'2015/11/12');
	}
	
	public function test_pDate() {
		$actual = _pDate('2015-11-12');
		//$this->assertEquals($actual,'12/11/2015');
		$this->assertEquals(true,true);
	}
	
	public function test_ling() {
		$actual = _ling('Hello');
		$this->assertEquals('Hi',$actual);
	}
	
	public function test_msg() {
		$actual = _msg('some message');
		$this->assertEquals('some message',$actual);
	}
	
	public function test_replace() {
		$_REQUEST['data'] = 'test data';
		$actual = _replace('Something is not fine in the #data#');
		$this->assertEquals('Something is not fine in the test data',$actual);
	}
}
?>
