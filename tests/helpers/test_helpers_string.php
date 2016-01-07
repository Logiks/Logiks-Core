<?php
	/*
	 * For testing logiks core helper : string
	 * 
	 * @author	Arun Joseph <arunjoseph50@gmail.com>
	 * @date 	2015-11-12
	 */
	class test_helpers_string extends LogiksTestCase{

		public function setUp(){
			parent::setUp();
			loadHelpers("string");
		}
		public function test_startsWith(){
			$result=startsWith('This is a cool sentence','This');
			$this->assertEquals(true,$result);
		}
		public function test_endsWith(){
			$result=endsWith('This is a cool sentence','sentence');
			$this->assertEquals(true,$result);
		}
		public function test_singular(){
			$result=singular('apples');
			$this->assertEquals('apple',$result);
		}
		public function test_plural(){
			$result=plural('apple');
			$this->assertEquals('apples',$result);
		}
		public function test_camelize(){
			$result=camelize('test_first');
			$this->assertEquals('testFirst',$result);
		}
		public function test_underscore(){
			$result=underscore('First things first');
			$this->assertEquals('first_things_first',$result);
		}
		public function test_humanize(){
			$result=humanize('first_things_first');
			$this->assertEquals('First Things First',$result);
		}
		/*
			public function test_byte_format() {
				$actual = byte_format(1021121200652,2);
				$expected = '0.93 TB';
				$this->assertEquals($expected,$actual);
			}
			*/
		/*
			public function test_wordLimiter() {
				$str = "This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test";
				$result = wordLimiter($str);
				$expected = 'This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test…';
				$this->assertEquals($expected,$result);
			}
			*/
		/*
			public function test_characterLimiter() {
				$str = "This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test";
				$result = characterLimiter($str);
				$expected = 'This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This is a test This…';
				$this->assertEquals($expected,$result);
			}
			*/
		public function test_wordCensor(){
			$str='Damn this world is no good place to stay, we will find for a new inhabitant for the good people...';
			$actual=wordCensor($str,array('Damn','no','good','find'),'*');
			$expected='* this world is * * place to stay, we will * for a new inhabitant for the * people...';
			$this->assertEquals($expected,$actual);
		}
	}
?>
