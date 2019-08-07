<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Cookies.php';

class CookiesTest extends TestCase {
	public function testSetCookie() {
		$cookieData = explode(" ", ".google.ca	TRUE	/	FALSE	1567709481	1P_JAR	2019-08-06-18");
		
		$cookies = new webBot\Cookies();
		$results = array($cookieData[0] => array_slice($cookieData, 1));
		$cookies->setCookie($cookieData[0], array_slice($cookieData, 1));
		$this->assertEquals($results, $cookies->getCookies());
	}

	public function testCookieJar() {	
		$cookies = new webBot\Cookies();
		$initialJar = $cookies->getCookieJar();
		$this->assertEquals($initialJar, "cookies.txt");
		$cookies->setCookieJar("new_cookie_file.txt");
		$newJar = $cookies->getCookieJar();
		$this->assertEquals($newJar, "new_cookie_file.txt");
	}
}

?>