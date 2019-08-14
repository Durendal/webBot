<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Response.php';

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

		self::$getRequest = new webBot\Request('https://jsonplaceholder.typicode.com/posts/2/');
		self::$getRequest->addHeader('Content-type', 'application/json; charset=UTF-8');
		self::$getResponse = self::$getRequest->run();
		
		self::$postRequest = new webBot\Request(
			'https://reqres.in/api/users',
			array('method' => 'POST')
		);
		self::$postRequest->setData(
			array(
				'name' => 'morpheus',
				'job' => 'leader'
			)
		);
		self::$postRequest->addHeader('Content-type', 'application/json; charset=UTF-8');
		self::$postResponse = self::$postRequest->run();
		
		self::$putRequest = new webBot\Request(
			'https://reqres.in/api/users/2',
			array('method' => 'PUT')
		);
		self::$putRequest->setData(
			array(
				'name' => 'morpheus',
				'job' => 'zion resident'
			)
		);
		self::$putRequest->addHeader('Content-type', 'application/json; charset=UTF-8');
		self::$putResponse = self::$putRequest->run();

		self::$patchRequest = new webBot\Request(
			'https://reqres.in/api/users/2',
			array('method' => 'PATCH')
		);
		self::$patchRequest->setData(			
			array(
				'name' => 'morpheus',
				'job' => 'zion resident'
			)
		);
		self::$patchRequest->addHeader('Content-type', 'application/json; charset=UTF-8');
		self::$patchResponse = self::$patchRequest->run();

		self::$deleteRequest = new webBot\Request(
			'https://reqres.in/api/users/2',
			array('method' => 'DELETE')
		);
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
		$this->assertEquals(201, self::$postResponse->status());
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
		$this->assertEquals(204, self::$deleteResponse->status());
	}

	public function testDeleteContent() {
		$this->assertTrue(strlen(self::$deleteResponse->content()) == 0);
	}

	public function testDeleteHeaders() {
		$this->assertTrue(count(self::$deleteResponse->headers()->getHeaders()) > 0);
	}

	public function testDeleteRaw() {
		$this->assertTrue(strlen(self::$deleteResponse->raw()) > 0);
	}
}

?>