<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Response.php';
require_once __DIR__.'/../src/CURLHandle.php';
require_once __DIR__.'/../src/Proxy.php';
require_once __DIR__.'/../src/Cookies.php';
require_once __DIR__.'/../src/Headers.php';

class ResponseTest extends TestCase {
	
	public function setUp(): void {
		$headers = new webBot\Headers();
		$cookies = new webBot\Cookies();
		$proxy = new webBot\Proxy();
		$this->request = new webBot\Request(array('proxy'=>$proxy, 'cookies'=>$cookies, 'headers'=>$headers,'method'=>'GET'));
		$this->request->setURL('https://jsonplaceholder.typicode.com/posts/2/');
		$this->response = $this->request->run();
	}

	public function tearDown(): void {
		unset($this->handle);
	}

	public function testStatus() {
		$this->assertEquals(200, $this->response->status());
	}

	public function testContent() {
		$this->assertTrue(strlen($this->response->content()) > 0);
	}

	public function testHeaders() {
		$this->assertTrue(count($this->response->headers()->getHeaders()) > 0);
	}

	public function testRaw() {
		$this->assertTrue(strlen($this->response->raw()) > 0);
	}
}

?>