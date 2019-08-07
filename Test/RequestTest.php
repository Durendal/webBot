<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Request.php';
require_once __DIR__.'/../src/Response.php';
require_once __DIR__.'/../src/CURLHandle.php';
require_once __DIR__.'/../src/Proxy.php';
require_once __DIR__.'/../src/Cookies.php';
require_once __DIR__.'/../src/Headers.php';

class RequestTest extends TestCase {

	public function setUp(): void {
		$headers = new webBot\Headers();
		$cookies = new webBot\Cookies();
		$proxy = new webBot\Proxy();
		$this->request = new webBot\Request(array('proxy'=>$proxy, 'cookies'=>$cookies, 'headers'=>$headers,'method'=>'GET'));
	}

	public function tearDown(): void {
		unset($this->handle);
	}

	public function testSetURL() {
		$url = 'https://jsonplaceholder.typicode.com/posts/2/';
		$this->request->setURL($url);
		$this->assertEquals($url, $this->request->getURL());
	}

	public function testRun() {
		$url = 'https://jsonplaceholder.typicode.com/posts/2/';
		$this->request->setURL($url);
		$response = $this->request->run();
		$this->assertTrue(is_a($response, "WebBot\WebBot\Response"));
	}
}

?>