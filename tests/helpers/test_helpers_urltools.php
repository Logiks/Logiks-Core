<?php
/*
 * For testing logiks core helper : urltools
 * 
 * @author	Rupali Dawkhar <dawkharrupali@gmail.com
 * @date 	2015-11-16
 */
class test_helpers_urltools extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("urltools");
	}
	
	public function test_getQueryParams() {
		$actual=array ( 'site' => 'dummy', 'page' => 'testkit/', 'basepage' => 'testkit', 'slug' => array ( 0 => 'testkit', 1 => '', ), 'query' => array ( 'comp' => 'testcase', 'src' => '/srcspace/www/devlogiks/tests/helpers/test_helpers_urltools.php', 'category' => '22ffa003527fc7b4cfddb491cbfb1804', ), );
		$result = getQueryParams();
		
		$this->assertEquals($actual,$result);
	}
	public function test_getPrettyLink() {
		$actual= "http://192.168.10.210:81/devlogiks/testkit/";
		$result = getPrettyLink();
	//	echo $result;
		$this->assertEquals($actual,$result);
	}
	public function test_cryptURL() {
		$actual="aHR0cDovLzE5Mi4xNjguMTAuMjEwOjgxL2RldmxvZ2lrcy90ZXN0a2l0Lw";
		$url = getPrettyLink();
	  	$result=cryptURL($url);
		//	echo $result;
		$this->assertEquals($actual,$result);
	}
	public function test_decryptURL() {
		$actual="http://192.168.10.210:81/devlogiks/testkit/";
		$url = getPrettyLink();
		$url=cryptURL($url);
	  	$result=decryptURL($url);
		//	echo $result;
		$this->assertEquals($actual,$result);
	}
	public function test_getRelativePathToROOT() {
		$actual="../";
	  	$result=getRelativePathToROOT('test_helpers_string.php');
		echo $result;
		$this->assertEquals($actual,$result);
	}
	public function test_getRequestPath() {
		$actual="http://192.168.10.210:81/testkit/";
	  	$result=getRequestPath();
		$this->assertEquals($actual,$result);
	}
	
}
?>
