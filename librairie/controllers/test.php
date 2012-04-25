<?php
require_once(SIMPLE_TEST . 'autorun.php');
Class Test extends \Controller{
	public function __construct() {
		parent::__construct($options);
		
	}
	public function index() {
		
		$st = new SystemTests();
		$st->run();
		
	}
}
class SystemTests extends UnitTestCase {
 	function __construct(){
        $this->UnitTestCase('Log class test');
    }
	function testCreatingNewFile() {
		$this->assertTrue(false);
	}
}
?>