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
		$this->assertEquals(200, $response->status());
	}

	public function testSetProxy() {
		$firstProxy = $this->request->getProxy();
		$proxy = new webBot\Proxy('127.0.0.1', 8080, webBot\Proxy::getValidTypes()['HTTP']);
		$this->request->setProxy($proxy);
		$secondProxy = $this->request->getProxy();
		$this->assertFalse($firstProxy == $secondProxy);
	}

	public function testSetCookies() {
		$firstCookies = $this->request->getCookies();
		$cookies = new webBot\Cookies();
		$key = '#TestCookie';
		$vals = array(
			'flag' => 'TRUE',
			'path' => '/',
			'secure' => 'FALSE',
			'expiration' => '0',
			'name' => 'ARRAffinity',
			'value' => '0285cfbea9f2ee78f69010c84850bd5b73ee05f1ff7f634b0b6b20c1291ca357'
		);
		$cookies->setCookie($key, $vals);
		$this->request->setCookies($cookies);
		$secondCookies = $this->request->getCookies();
		$this->assertFalse($firstCookies == $secondCookies);
	}
}

?>