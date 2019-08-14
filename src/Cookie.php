<?php

use WebBot\WebBot as webBot;

class Cookie {
	
	private $name;
	private $value;
	private $domain;
	private $path;
	private $expires;
	private $maxAge;
	private $secure;
	private $httpOnly;

	public function __construct($attributes = array()) {
		$this->setCookie($attributes);
	}

	public function getName() {
		return $this->name;
	}

	public function getValue() {
		return $this->value;
	}

	public function getDomain() {
		return $this->domain;
	}

	public function getPath() {
		return $this->path;
	}

	public function getExpires() {
		return $this->expires;
	}

	public function getMaxAge() {
		return $this->maxAge;
	}

	public function getSecure() {
		return $this->secure;
	}

	public function getHttpOnly() {
		return $this->httpOnly;
	}

	private function setAttribute($name, $value) {
		$this->{$name} = $value;
	}

	public function setName($name) {
		$this->setAttribute('name', $name);
	}

	public function setValue($value) {
		$this->setAttribute('value', $value);
	}

	public function setDomain($domain) {
		$this->setAttribute('domain', $domain);
	}

	public function setPath($path) {
		$this->setAttribute('path', $path);
	}

	public function setExpires($expires) {
		$this->setAttribute('expires', $expires);
	}

	public function setMaxAge($maxAge) {
		$this->setAttribute('maxAge', $maxAge);
	}

	public function setSecure($secure) {
		$this->setAttribute('secure', $secure);
	}

	public function setHttpOnly($httpOnly) {
		$this->setAttribute('httpOnly', $httpOnly);
	}

	public function setCookie($attributes = array()) {
		$name = "";
		$value = "";
		$domain = "";
		$path = "/";
		$expires = "NULL";
		$maxAge = "NULL";
		$secure = "FALSE";
		httpOnly = "FALSE";
		extract($attributes);
		$this->setName($name);
		$this->setValue($value);
		$this->setPath($path);
		$this->setExpires($expires);
		$this->setMaxAge($maxAge);
		$this->setSecure($secure);
		$this->setHttpOnly($httpOnly);
	}

	public function getCookie() {
		return array (
			'name' => $this->getName(),
			'value' => $this->getValue(),
			'domain' => $this->getDomain(),
			'path' => $this->getPath(),
			'expires' => $this->getExpires(),
			'Max-Age' => $this->getMaxAge(),
			'HttpOnly' => $this->getHttpOnly()
		);
	}
};