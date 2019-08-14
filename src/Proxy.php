<?php
/**
 *		Proxy.php - An object to hold HTTP Responses
 *
 *		This class provides the functionality to set a HTTP or SOCKS based
 *		proxy for webBot
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

require_once 'Exceptions.php';

class Proxy {

	/**
	 * @var int $type - The type of proxy (CURLPROXY_* constants)
	 * @var string $host - The address of the proxy to connect to
	 * @var int $port - The port of the proxy to connect to
	 * @var string $credentials - Any credentials needed to connect to the proxy
	 * @var array $validTypes - A list of all valid CURLPROXY_* types
	 */
	private $type;
	private $host;
	private $port;
	private $credentials;
	static private $validTypes = array(
			'HTTP' => CURLPROXY_HTTP,
			'HTTP1.0' => CURLPROXY_HTTP_1_0, 
			'HTTPS' => CURLPROXY_HTTPS,
			'SOCKS4' => CURLPROXY_SOCKS4,
			'SOCKS4A' => CURLPROXY_SOCKS4A,
			'SOCKS5' => CURLPROXY_SOCKS5, 
			'SOCKS5_HOSTNAME' => CURLPROXY_SOCKS5_HOSTNAME,
			'None' => NULL
		);

	/**
	 *    __construct($host="", $port=0, $type=NULL, $credentials=NULL)
	 *
	 *      Constructs a new Proxy object with the specified settings.
	 *
	 * @param string $host - The address of the proxy
	 * @param int $port - The port to connect to the proxy on
	 * @param int $type - The type of proxy (CURLPROXY_* constants)
	 * @param string $credentials - A string containing username:password format for the proxy
	 *
	 * @return void
	 */
	public function __construct($host="", $port=0, $type=NULL, $credentials=NULL) {

		$this->setHost($host);
		$this->setPort($port);
		$this->setType($type);
		$this->setCredentials($credentials);
	}

	public static function getValidTypes() {
		return self::$validTypes;
	}

	/**
	 *	__toString()
	 *
	 *		Returns a printable string representation of the Proxy object.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("<%s Proxy - %s:%d Credentials: %s>", $this->type, $this->host, $this->port, $this->credentials);
	}

	/**
	 *  setType($type)
	 *
	 *    Checks that $type is a valid cURL proxy type or NULL
	 *    If set to NULL no proxy will be used and all other proxy
	 *    settings are ignored.
	 *
	 * @param int $type - The CURLPROXY type to use.
	 * @return void
	 */
	public function setType($type) {
		$this->type = (in_array($type, self::$validTypes)) ? $type : NULL;
	}

	/**
	 *  setCredentials($credentials)
	 *
	 *    Checks that credentials are sent in the form username:password
	 *    if so they are set for the proxy otherwise credentials are set to NULL
	 *
	 * @param int $credentials - The Credentials to use
	 * @return void
	 */
	public function setCredentials($credentials) {
		$this->credentials = (is_string($credentials) && count(explode(":", $credentials)) > 1) ? $credentials : NULL;
	}

	/**
	 *  setHost($host)
	 *
	 *    Sets the address of the proxy to use
	 *
	 * @param int $host - The host to connect to
	 * @return void
	 */
	public function setHost($host) {
		$this->host = (filter_var($host, FILTER_VALIDATE_URL)) ? $host : NULL;
	}

	/**
	 *  setPort($port)
	 *
	 *    Checks that $port is a valid int greater than or equal to 0
	 *    and if so sets the proxy to use that port. 0 indicates no proxy is set.
	 *
	 * @param int $port - The port of the proxy to use
	 * @return void
	 */
	public function setPort($port) {
		$this->port = (is_int($port) && $port >= 0) ? $port : 0;
	}

	/**
	 *	getProxy()
	 *
	 *		returns an array with the currently set proxy, credentials, and its type.
	 *
	 * @return array
	 */
	public function getProxy()
	{
		return array('host' => $this->host, 'port' => $this->port, 'credentials' => $this->credentials, 'type' => $this->type);
	}

}
