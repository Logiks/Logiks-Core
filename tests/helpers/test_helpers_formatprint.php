<?php
/*
 * For testing logiks core helper : urltools
 * 
 * @author	Rupali Dawkhar <dawkharrupali@gmail.com
 * @date 	2015-11-16
 */
class test_helpers_formatprint extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("formatprint");
	}
	
	public function test_getMsgEnvelop() {
		$actual=array (
			'start' => '<option>',
			'end' => '</option>',
			) ;
		$_REQUEST['format']='select';
		$result = getMsgEnvelop();
	//	$result['start'] = htmlentities($result['start']);
	//	$result['end'] = htmlentities($result['end']);
	//	var_export($result);
	$this->assertEquals($actual,$result);
	}
	
	
}
?>
