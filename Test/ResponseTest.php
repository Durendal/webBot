<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Response.php';
require_once __DIR__.'/../src/CURLHandle.php';
require_once __DIR__.'/../src/Proxy.php';
require_once __DIR__.'/../src/Cookies.php';
require_once __DIR__.'/../src/Headers.php';

class ResponseTest extends TestCase {
	static $getRequest;
	static $postRequest;
	static $putRequest;
	static $patchRequest;
	static $deleteRequest;
	static $getResponse;
	static $postResponse;
	static $putResponse;
	static $patchResponse;
	static $deleteResponse;

	public static function setUpBeforeClass(): void {
		$now = time();
		$headers = new webBot\Headers();
		$cookies = new webBot\Cookies("cookies-$now.txt");
		$proxy = new webBot\Proxy();
		self::$getRequest = new webBot\Request(
			array(
				'proxy' => $proxy, 
				'cookies' => $cookies, 
				'headers' => $headers,
				'method' => 'GET'
			)
		);
		self::$getRequest->setURL('https://jsonplaceholder.typicode.com/posts/2/');
		self::$getResponse = self::$getRequest->run();
		
		self::$postRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'POST'
			)
		);
		self::$postRequest->setURL('https://jsonplaceholder.typicode.com/posts');
		self::$postRequest->setData(
			array(
				'title' => 'foo',
				'body' => 'bar',
				'userId' => 1
			)
		);
		self::$postRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		self::$postResponse = self::$postRequest->run();
		
		self::$putRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'PUT'
			)
		);
		self::$putRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		self::$putRequest->setData(
			array(
				'id' => 1,
				'title' => 'foo',
				'body' => 'bar',
				'userId' => 1
			)
		);
		self::$putRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		self::$putResponse = self::$putRequest->run();

		self::$patchRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'PATCH'
			)
		);
		self::$patchRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		self::$patchRequest->setData(array('title' => 'foo'));
		self::$patchRequest->addHeader('Content-Type', 'application/json; charset=UTF-8');
		self::$patchResponse = self::$patchRequest->run();

		self::$deleteRequest = new webBot\Request(
			array(
				'proxy' => $proxy,
				'cookies' => $cookies,
				'headers' => $headers,
				'method' => 'DELETE'
			)
		);
		self::$deleteRequest->setURL('https://jsonplaceholder.typicode.com/posts/1');
		self::$deleteResponse = self::$deleteRequest->run();
	}

	public function testGetStatus() {
		$this->assertEquals(200, self::$getResponse->status());
	}

	public function testGetContent() {
		$this->assertTrue(strlen(self::$getResponse->content()) > 0);
	}

	public function testGetHeaders() {
		$this->assertTrue(count(self::$getResponse->headers()->getHeaders()) > 0);
	}

	public function testGetRaw() {
		$this->assertTrue(strlen(self::$getResponse->raw()) > 0);
	}

	public function testPostStatus() {
		$this->assertEquals(200, self::$postResponse->status());
	}

	public function testPostContent() {
		$this->assertTrue(strlen(self::$postResponse->content()) > 0);
	}

	public function testPostHeaders() {
		$this->assertTrue(count(self::$postResponse->headers()->getHeaders()) > 0);
	}

	public function testPostRaw() {
		$this->assertTrue(strlen(self::$postResponse->raw()) > 0);
	}

	public function testPutStatus() {
		$this->assertEquals(200, self::$putResponse->status());
	}

	public function testPutContent() {
		$this->assertTrue(strlen(self::$putResponse->content()) > 0);
	}

	public function testPutHeaders() {
		$this->assertTrue(count(self::$putResponse->headers()->getHeaders()) > 0);
	}

	public function testPutRaw() {
		$this->assertTrue(strlen(self::$putResponse->raw()) > 0);
	}

	public function testPatchStatus() {
		$this->assertEquals(200, self::$patchResponse->status());
	}

	public function testPatchContent() {
		$this->assertTrue(strlen(self::$patchResponse->content()) > 0);
	}

	public function testPatchHeaders() {
		$this->assertTrue(count(self::$patchResponse->headers()->getHeaders()) > 0);
	}

	public function testPatchRaw() {
		$this->assertTrue(strlen(self::$patchResponse->raw()) > 0);
	}

	public function testDeleteStatus() {
		$this->assertEquals(200, self::$deleteResponse->status());
	}

	public function testDeleteContent() {
		$this->assertTrue(strlen(self::$deleteResponse->content()) > 0);
	}

	public function testDeleteHeaders() {
		$this->assertTrue(count(self::$deleteResponse->headers()->getHeaders()) > 0);
	}

	public function testDeleteRaw() {
		$this->assertTrue(strlen(self::$deleteResponse->raw()) > 0);
	}
}

?>