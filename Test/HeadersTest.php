<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Headers.php';

class HeadersTest extends TestCase {

	public function testAddHeaders() {	
		$headers = new webBot\Headers();
		$initialHeaders = $headers->getHeaders();
		$testHeaders = array(
			"Keep-alive",
			"300"
		);
		foreach($testHeaders as $header) 
			$this->assertTrue(in_array($header, $initialHeaders));
	}

	public function testChangeHeader() {	
		$headers = new webBot\Headers();
		$initialHeaders = $headers->getHeaders();
		$initialUA = $initialHeaders['User-Agent'];
		do {
			$newUA = $headers->randomAgent();
		} while($newUA == $initialUA);
		$headers->changeHeader("User-Agent", $newUA);
		$newHeaders = $headers->getHeaders();
		$this->assertEquals($newUA, $newHeaders['User-Agent']);
	}

	public function testDeleteHeader() {
		$headers = new webBot\Headers();
		$initialHeaders = $headers->getHeaders();
		$this->assertTrue(array_key_exists("User-Agent", $initialHeaders));
		$headers->delHeader("User-Agent");
		$newHeaders = $headers->getHeaders();
		$this->assertFalse(array_key_exists("User-Agent", $newHeaders));
	}
}

?>