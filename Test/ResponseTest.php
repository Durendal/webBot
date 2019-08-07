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
		$now = time();
		$headers = new webBot\Headers();
		$cookies = new webBot\Cookies("cookies-$now.txt");
		$proxy = new webBot\Proxy();
		$this->getRequest = new webBot\Request(
			array(
				'proxy' => $proxy, 
				'cookies' => $cookies, 
				'headers' => $headers,
				'method' => 'GET'
			)
		);
		$this->getRequest->setURL('https://jsonplaceholder.typicode.com/posts/2/');
		$this->getResponse = $this->getRequest->run();
		
		$this->postRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'POST'
			)
		);
		$this->postRequest->setURL('https://jsonplaceholder.typicode.com/posts');
		$this->postRequest->setData(
			array(
				'title' => 'foo',
				'body' => 'bar',
				'userId' => 1
			)
		);
		$this->postRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->postResponse = $this->postRequest->run();
		
		$this->putRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'PUT'
			)
		);
		$this->putRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		$this->putRequest->setData(
			array(
				'id' => 1,
				'title' => 'foo',
				'body' => 'bar',
				'userId' => 1
			)
		);
		$this->putRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->putResponse = $this->putRequest->run();

		$this->patchRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'PATCH'
			)
		);
		$this->patchRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		$this->patchRequest->setData(array('title' => 'foo'));
		$this->patchRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		$this->patchResponse = $this->patchRequest->run();

		$this->deleteRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'DELETE'
			)
		);
		$this->deleteRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		$this->deleteResponse = $this->deleteRequest->run();
	}

	public function testGetStatus() {
		$this->assertEquals(200, $this->getResponse->status());
	}

	public function testGetContent() {
		$this->assertTrue(strlen($this->getResponse->content()) > 0);
	}

	public function testGetHeaders() {
		$this->assertTrue(count($this->getResponse->headers()->getHeaders()) > 0);
	}

	public function testGetRaw() {
		$this->assertTrue(strlen($this->getResponse->raw()) > 0);
	}

	public function testPostStatus() {
		$this->assertEquals(200, $this->postResponse->status());
	}

	public function testPostContent() {
		$this->assertTrue(strlen($this->postResponse->content()) > 0);
	}

	public function testPostHeaders() {
		$this->assertTrue(count($this->postResponse->headers()->getHeaders()) > 0);
	}

	public function testPostRaw() {
		$this->assertTrue(strlen($this->postResponse->raw()) > 0);
	}

	public function testPutStatus() {
		$this->assertEquals(200, $this->putResponse->status());
	}

	public function testPutContent() {
		$this->assertTrue(strlen($this->putResponse->content()) > 0);
	}

	public function testPutHeaders() {
		$this->assertTrue(count($this->putResponse->headers()->getHeaders()) > 0);
	}

	public function testPutRaw() {
		$this->assertTrue(strlen($this->putResponse->raw()) > 0);
	}

	public function testPatchStatus() {
		$this->assertEquals(200, $this->patchResponse->status());
	}

	public function testPatchContent() {
		$this->assertTrue(strlen($this->patchResponse->content()) > 0);
	}

	public function testPatchHeaders() {
		$this->assertTrue(count($this->patchResponse->headers()->getHeaders()) > 0);
	}

	public function testPatchRaw() {
		$this->assertTrue(strlen($this->patchResponse->raw()) > 0);
	}

	public function testDeleteStatus() {
		$this->assertEquals(200, $this->deleteResponse->status());
	}

	public function testDeleteContent() {
		$this->assertTrue(strlen($this->deleteResponse->content()) > 0);
	}

	public function testDeleteHeaders() {
		$this->assertTrue(count($this->deleteResponse->headers()->getHeaders()) > 0);
	}

	public function testDeleteRaw() {
		$this->assertTrue(strlen($this->deleteResponse->raw()) > 0);
	}
}

?>