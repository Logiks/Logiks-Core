<?php
	/*
	 * For testing logiks core helper : urltools
	 * 
	 * @author	Rupali Dawkhar <dawkharrupali@gmail.com
	 * @date 	2015-11-16
	 */
	class test_helpers_listarray extends LogiksTestCase{
		public function setUp(){
			parent::setUp();
			loadHelpers("listarray");
			
		}
		
		public function test_printTree() {
			$actual='<li><a rel=site >site</a></li><li><h2>slug</h2><ul><li><a rel=0 >0</a></li><li><a rel=1 >1</a></li></ul></li><li><h2>query</h2><ul><li><a rel=comp >comp</a></li><li><a rel=category >category</a></li></ul></li>';
			$testArray=array ( 'site' => 'dummy', 'slug' => array ( 0 => 'testkit', 1 => '', ), 'query' => array ( 'comp' => 'testcase', 'category' => '22ffa003527fc7b4cfddb491cbfb1804' ) );
			$result = printTree($testArray);
			//echo $result;
			$this->assertEquals($actual,$result);
		}
		// public function test_printTreeForRowArray(){
		// 	//$this->assertTrue(class_exists("ArrayToList"));
			
		// 	$actual='<li><a rel=site >site</a></li><li><h2>slug</h2><ul><li><a rel=0 >0</a></li><li><a rel=1 >1</a></li></ul></li><li><h2>query</h2><ul><li><a rel=comp >comp</a></li><li><a rel=category >category</a></li></ul></li>';
		// 	$testArray=array('site'=>'dummy','slug'=>array(0=>'testkit',1=>'',),'query'=>array('comp'=>'testcase','category'=>'22ffa003527fc7b4cfddb491cbfb1804'));
		// //	print_r($testArray);
		// 	$result=printTreeForRowArray($testArray);
		// 	// print_r($result);
		// 	//$result='';
			
		// 	$this->assertEquals(md5($actual),md5($result));
		// }
		
	
	}
?>