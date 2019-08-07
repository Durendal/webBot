<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/CURLHandle.php';
require_once __DIR__.'/../src/Proxy.php';
require_once __DIR__.'/../src/Cookies.php';
require_once __DIR__.'/../src/Headers.php';

class CURLHandleTest extends TestCase {

	public function setUp(): void {
		$headers = new webBot\Headers();
		$cookies = new webBot\Cookies();
		$proxy = new webBot\Proxy();
		$this->handle = new webBot\CURLHandle(array('proxy'=>$proxy, 'cookies'=>$cookies, 'headers'=>$headers));
	}

	public function tearDown(): void {
		unset($this->handle);
	}

	public function testInitializingProxy() {
		$testProxy = new webBot\Proxy();
		$this->assertEquals($this->handle->getProxy(), $testProxy->getProxy());
	}

	public function testInitializingCookies() {
		$testCookies = new webBot\Cookies();	
		foreach($this->handle->getCookies() as $key => $value)
			$testCookies->setCookie($key, $value);
		$this->assertEquals($this->handle->getCookies(), $testCookies->getCookies());
	}

	public function testInitializingHeaders() {
		$UA = $this->handle->getHeaders()['User-Agent'];
		$testHeaders = new webBot\Headers(array('User-Agent'=>$UA));
		$this->assertEquals($this->handle->getHeaders(), $testHeaders->getHeaders());
	}
}

?>