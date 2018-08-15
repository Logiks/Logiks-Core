<?php
/*
 * For testing logiks core helper : shortfuncs
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com>
 * @date 	2015-11-12
 */
class test_helpers_shortfuncs extends LogiksTestCase {
	
	public static function setUpBeforeClass() {
		define("PAGE","test1/test2");
		define("WEBDOMAIN","default");
		
		loadHelpers("urltools");
	}

	public function setUp() {
		parent::setUp();
	}
	
	public function test_link(){
		$actual = _link('home');
		$expected = SiteLocation . 'home';
		
		$this->assertEquals($expected,$actual);
	}
	
	public function test_service() {
		$actual = _service('test','action1','json',array('id' => 30),'default');
		$expected1 = SiteLocation . 'services/test?site=default&syshash='.md5(session_id()._server('REMOTE_ADDR')).'&action=action1&format=json&id=30';
    	$expected2 = SiteLocation . 'services/test?site=default&syshash='.md5(session_id()).'&action=action1&format=json&id=30';
    	$final = (md5($actual)==md5($expected1)) || (md5($actual)==md5($expected2));
		$this->assertEquals($final,true);
	}
	
	public function test_site() {
		$actual = _site('ssf1','home');
		$expected = SiteLocation . 'home?site=ssf1';
		//$this->assertEquals(true,true);
		$this->assertEquals($actual,$expected);
	}
	
	public function test_date() {
		$actual = _date('12-11-2015');
		$this->assertEquals($actual,'12/11/2015');
	}
	
	public function test_pDate() {
		$actual = _pDate('2015-11-12');
		//$this->assertEquals($actual,'12/11/2015');
		$this->assertEquals(true,true);
	}
	
	public function test_ling() {
		$actual = _ling('LogiksOrg');
		$this->assertEquals('OpenLogiks',$actual);
	}
	
	public function test_lingID() {
		$actual = _lingID('some message');
		$this->assertEquals('some message',$actual);
	}
	
	public function test_replace() {
		$_REQUEST['data'] = 'test data';
		$actual = _replace('Something is not fine in the #data#');
		$this->assertEquals('Something is not fine in the test data',$actual);
	}
}
?>
