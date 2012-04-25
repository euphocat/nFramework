<?php
require_once '../system/checkform.php';

Class CheckFormTest extends PHPUnit_Framework_TestCase{
	
	public function testIsInt() {
		$checkForm = new CheckForm();
		
		$this->assertTrue($checkForm->isInt('21','cle'),"'21'");
		$this->assertTrue($checkForm->isInt(21,'cle'));
		$this->assertFalse($checkForm->isInt('vingt','cle'));
		$this->assertFalse($checkForm->isInt('21.5','cle'));
		$this->assertFalse($checkForm->isInt(21.5,'cle'));
	}
	
	public function testIsDate() {
		$checkForm = new CheckForm();
		
		//date dans le passé
		$this->assertFalse($checkForm->isDate('17/05/86',null), "Date FR année courte");
		$this->assertTrue($checkForm->isDate('17/05/1986',null), "Date FR année longue");
		$this->assertFalse($checkForm->isDate('17/05/86 20:00',null), "Date FR année courte + heure");
		$this->assertTrue($checkForm->isDate('17/05/1986 20:00',null), "Date FR année longue + heure");
	}
}
?>