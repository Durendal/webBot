<?php
/**
 *		Cookies.php - An object to represent cookies with webBot
 *
 *		This class helps dissecting the responses from HTTP requests
 *		facilitates easy access to: Status code, headers, and content
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */
namespace Durendal\webBot;

use Durendal\webBot as webBot;

require_once 'Exceptions.php';

class Cookies
{
	/**
	 * @var array $cookies - An array containing a list of currently set cookies
	 * @var string $cookieJar - A string containing the location of the cookie file
	 * @var resource $parentHandle - The cURL Handle bound to the Cookies object
	 */
	private $cookies;
	private $cookieJar;
	private $parentHandle;

	/**
	 *   __construct(&$ch, $cookies = array(), $cookieJar = "cookies.txt")
	 *
	 *     Constructs a fresh Cookies object and sets any cookies passed to it.
	 *
	 * @param cURL $ch - cURL handle of the parent request
	 * @param array $cookies - An array of custom cookies to set.
	 * @param string $cookieJar - The location of the file to write/read cookies from
	 *
	 * @return void
	 */
	public function __construct($cookieJar = "cookies.txt") {
		$this->cookies = array();
		$this->setCookieJar($cookieJar);
	}

	/**
	 *	__toString()
	 *
	 *		Returns a printable string representation of the Cookies object.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("<HTTP Cookies - %d cookies currently set>", count($this->cookies));
	}

	/**
	 *	setCookieJar($cookieJar)
	 *
	 *		Sets the location of the file to use for reading/writing cookies.
	 *
	 * @param string $cookieJar - The path to the file to store cookies in
	 *
	 * @return void
	 */
	public function setCookieJar($cookieJar) {
		$this->cookieJar = $cookieJar;
	}

	/**
	 *	getCookieJar()
	 *
	 *		Returns the location of the file to use for reading/writing cookies.
	 *
	 * @return string $this->cookieJar - The path of the file to read/write cookies from
	 */
	public function getCookieJar() {
		return $this->cookieJar;
	}

	public function setCookie($key, $value) {
		$this->cookies[$key] = $value;
	}

	public function getCookies() {
		return $this->cookies;
	}
}
