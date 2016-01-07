<?php
/*
 * For testing logiks core helper : urltools
 * 
 * @author	Rupali Dawkhar <dawkharrupali@gmail.com>
 * @date 	2015-11-16
 */
class test_helpers_urltools extends LogiksTestCase {
	
	public static function setUpBeforeClass() {
		define("PAGE","test1/test2");

		loadHelpers("urltools");
	}
	
	public function test_getQueryParams() {
		//$actual=array ( 'site' => 'dummy', 'page' => 'testkit/', 'basepage' => 'testkit', 'slug' => array ( 0 => 'testkit', 1 => '', ), 'query' => array ( 'comp' => 'testcase', 'src' => '/srcspace/www/devlogiks/tests/helpers/test_helpers_urltools.php', 'category' => '22ffa003527fc7b4cfddb491cbfb1804', ), );
		$result = getQueryParams();
		$actual = [
				'site'=>SITENAME,
				'page'=>PAGE,
				'basepage'=>'test1',
				'slug'=>[
					'test2'
				],
				'query'=>false
			];
		$this->assertEquals($actual,$result);
	}
	public function test_getPrettyLink() {
		$actual= SiteLocation.PAGE;
		$result = getPrettyLink();
		$this->assertEquals($actual,$result);
	}
	public function test_cryptURL() {
		$url = getPrettyLink();
		$enc=new LogiksEncryption();
		$actual=$enc->encode($url);

	  	$result=cryptURL($url);
		
		$this->assertEquals($actual,$result);
	}
	public function test_decryptURL() {
		$url = getPrettyLink();
		$enc=new LogiksEncryption();
		$cx=$enc->encode($url);

	  	$result=decryptURL($cx);
		
		$this->assertEquals($url,$result);
	}
	public function test_getRelativePathToROOT() {
		$actual="../";
	  	$result=getRelativePathToROOT('test_helpers_string.php');
		$this->assertEquals($actual,$result);
	}
	public function test_getRequestPath() {
		$actual="http://".$GLOBALS['LOGIKS']["_SERVER"]['HTTP_HOST'].dirname($GLOBALS['LOGIKS']["_SERVER"]['PHP_SELF'])."/";
	  	$result=getRequestPath();
		$this->assertEquals($actual,$result);
	}
	
}
?>
