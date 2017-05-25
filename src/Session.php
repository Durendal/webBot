<?php

class Session {

	/**
	 * @var array $requests - An array of all completed requests by the session
	 * @var object $cookies - The cookies to use for the Session
	 * @var object $proxy - The proxy to use for the Session
	 * @var int $start - Timestamp recording the creation of the session
	 */
	$requests;
	$cookies;
	$proxy;
	$start;

	public function __construct() {
			$this->requests	= array();
			$this->cookies	= NULL;
			$this->proxy	= NULL;
			$this->start	= time();
	}

	public function __toString() {
		return "<HTTP Session - $time>"
	}


}
