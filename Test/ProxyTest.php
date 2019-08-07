<?php

use PHPUnit\Framework\TestCase;
use WebBot\WebBot as webBot;

require_once __DIR__.'/../src/Proxy.php';

class ProxyTest extends TestCase {
	public function testSetHost() {	
		$proxy = new webBot\Proxy();
		$initialHost = $proxy->getProxy();
		$this->assertEquals($initialHost['host'], "");
		$proxy->setHost("http://127.0.0.1");
		$newHost = $proxy->getProxy();
		$this->assertEquals($newHost['host'], "http://127.0.0.1");
	}

	public function testSetPort() {	
		$proxy = new webBot\Proxy();
		$initialPort = $proxy->getProxy();
		$this->assertEquals($initialPort['port'], 0);
		$proxy->setPort(8080);
		$newPort = $proxy->getProxy();
		$this->assertEquals($newPort['port'], 8080);
	}

	public function testSetType() {	
		$proxy = new webBot\Proxy();
		$initialType = $proxy->getProxy();
		$this->assertEquals($initialType['type'], NULL);
		$type = $proxy->getValidTypes();
		$proxy->setType($type['HTTP']);
		$newType = $proxy->getProxy();
		$this->assertEquals($newType['type'], $type['HTTP']);
	}

	public function testSetCredentials() {	
		$proxy = new webBot\Proxy();
		$initialCredentials = $proxy->getProxy();
		$this->assertEquals($initialCredentials['credentials'], NULL);
		$proxy->setCredentials("username:password");
		$newCredentials = $proxy->getProxy();
		$this->assertEquals($newCredentials['credentials'], "username:password");
	}
}

?>