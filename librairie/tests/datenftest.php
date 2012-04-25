<?php
require_once '../system/datenf.php';

Class DateNFTest extends PHPUnit_Framework_TestCase{
	/**
     * @expectedException \Exception
     */
	public function testInit() {
		new DateNF("blah");
	}
}
?>