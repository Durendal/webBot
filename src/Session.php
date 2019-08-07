<?php
/**
 *		Session.php - An object to encapsulate HTTP Sessions
 *
 *		This class encapsulates a HTTP Session. As such it will track cookies
 *		and headers through multiple Requests
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

require_once 'Proxy.php';
require_once 'Cookies.php';
require_once 'cURLHandle.php';
require_once 'Request.php';

class Session {

	/**
	 * @var array $requests - An array of all completed requests by the session
	 * @var object $cookies - The cookies to use for the Session
	 * @var object $proxy - The proxy to use for the Session
	 * @var int $start - Timestamp recording the creation of the session
	 */
	private $requests;
	private $cookies;
	private $proxy;
	private $start;

	public function __construct($persist=false, $proxy=NULL, $cookies=NULL, $ch=NULL) {
		$this->requests	= array();
		$this->cookies	= $this->setCookies($cookies);
		$this->proxy	= $this->setProxy($proxy);
		$this->start	= time();
		$this->curlHandle = $this->setCurlHandle($ch);
		if(is_a($persist, "boolean")) {
			$this->persist = $persist;
		}
	}

	public function setCookies($cookies) {
		$this->cookies = (is_a($cookies, "Durendal\webBot\Cookies")) ? $cookies : new webBot\Cookies();
	}

	public function getCookies() {
		return $this->cookies;
	}

	public function setProxy($proxy) {
		$this->proxy = (is_a($proxy, "Durendal\webBot\Proxy")) ? $proxy : new webBot\Proxy();
		$this->proxy->init($this->handle);
	}

	public function getProxy() {
		return $this->proxy;
	}

	public function getTimeInUse() {
		return time() - $this->start;
	}

	public function addRequest($request) {
		if(is_a($request, "Durendal\webBot\Request")){
			$this->requests[] = $request;
		}
	}

	public function getRequests() {
		return $this->requests;
	}

	public function setCurlHandle($ch) {
		if(is_a($ch, "Durendal\webBot\cURLHandle")) {
			$this->curlHandle = $ch;
		} else {
			$this->curlHandle = new webBot\cURLHandle($this->getProxy, $this->getCookies);
		}
	}

	public function getCurlHandle() {
		return $this->curlHandle;
	}

	public function get($url, $settings=array('query'=>NULL, 'headers'=>NULL, 'cookies'=>NULL, 'proxy'=>NULL)) {
		extract($settings);
		$reqSettings = array('method'=>'GET');
		if($query)
			$reqSettings['query'] = is_a($query, 'Durendal\webBot\RequestQuery') ? $query : new webBot\RequestQuery();
		if($headers)
			$reqSettings['headers'] = is_a($headers, 'Durendal\webBot\Headers') ? $headers : new webBot\Headers();
		if($cookies)
			$reqSettings['cookies'] = is_a($cookies, 'Durendal\webBot\Cookies') ? $cookies : new webBot\Cookies();
		if($proxy)
			$reqSettings['proxy'] = is_a($proxy, 'Durendal\webBot\Proxy') ? $proxy : new webBot\Proxy();

		$request = new webBot\Request($url, $reqSettings);

		return $request.run();
	}

	public function __toString() {
		$count = count($this->requests);
		$time = $this->getTimeInUse();
		return "<HTTP Session - $time - Requests: $count>";
	}


}
