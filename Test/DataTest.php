<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Data.php';

class DataTest extends TestCase {

	public function testSetData() {
		$testData = array('test1'=>5, 'test2'=>3);
		$reqData = new webBot\RequestData($testData);
		$this->assertEquals($reqData->get(), $testData);
	}

	public function testDataEncoding() {	
		$testData = array('test1'=>5,'test2'=>3);
		$reqData = new webBot\RequestData($testData);
		$this->assertEquals("test1=5&test2=3", $reqData->getEncoded());
	}

	public function testAddData() {
		$testData = array('test1'=>5, 'test2'=>3);
		$reqData = new webBot\RequestData($testData);
		$testData['test3'] = 6;
		$reqData->addData(array('test3'=>6));
		$this->assertEquals($testData, $reqData->get());
	}

}

?>