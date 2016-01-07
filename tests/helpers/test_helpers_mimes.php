<?php
/*
 * For testing logiks core helper : mimes
 * 
 * @author	Arun Joseph <arunjoseph50@gmail.com>
 * @date 	2015-11-16
 */
class test_helpers_mimes extends LogiksTestCase {
	
	public function setUp() {
		parent::setUp();
		loadHelpers("mimes");
	}
	
	public function test_getFileMimes() {
		$tz = getFileMimes();
		$expected = array ('mimetypes' => 'application/vnd.ms-excel', 'extensions' => 32);
		$result = array(
			'mimetypes' => $tz['mimetypes'][32],
			'extensions' => $tz['extensions']['xls']
		);
		$this->assertEquals($expected,$result);
	}
	
	public function test_getMimeTypeForFile() {
		$tx = getMimeTypeForFile('test.jpg');
		$expected = 'image/jpeg';
		$this->assertEquals($expected,$tx);
	}
	
	public function test_getMimeGroups() {
		$tx = getMimeGroups();
		$this->assertContains(array(
					"pdf"=>"PDF Documents",
					"mm"=>"Mindmaps",
					"txt"=>"Text Documents",
				),$tx);
		
	}
}
?>
