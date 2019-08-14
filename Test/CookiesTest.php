<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Cookies.php';

class CookiesTest extends TestCase {

	public function setUp(): void {
		$this->cookies = new webBot\Cookies("cookies.txt");
		$this->testCookie = array(
			'.google.ca' => 
			array(
				'flag' => 'TRUE',
				'path' => '/',
				'secure' => 'FALSE',
				'expiration' => '1567709481',
				'name' => '1P_JAR',
				'value' => '2019-08-06-18'
			)
		);
		$this->key = array_keys($this->testCookie)[0];
		$this->cookies->setCookie($this->key, $this->testCookie[$this->key]);
	}

	public function testSetCookie() {
		$key = array_keys($this->testCookie)[0];
		$results = array($this->key => $this->testCookie[$this->key]);
		$this->assertEquals($results, $this->cookies->getCookies());
	}

	public function testCookieJar() {	
		$initialJar = $this->cookies->getCookieJar();
		$this->assertEquals($initialJar, "cookies.txt");
		$this->cookies->setCookieJar("new_cookie_file.txt");
		$newJar = $this->cookies->getCookieJar();
		$this->assertEquals($newJar, "new_cookie_file.txt");
	}
}

?>