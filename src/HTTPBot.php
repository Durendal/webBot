<?php
/**
 *		HTTPBot.php - A simple interface to cURL with PHP
 *
 *		HTTPBot.php aims to simplify the use of cURL with php. At the moment it only
 *		handles GET and POST HTTP requests but I may add more to it as time and
 *		interest permits.
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace Durendal\webBot;

require_once 'Response.php';
require_once 'Proxy.php';

/**
 *		HTTPBot is a class for interacting with cURL through PHP. It should significantly simplify the process
 *		providing several functions to manipulate the curl_setopt() function in various ways.
 *
 *		 Some of the main features:
 *			Optional stack based URL queue
 *			curl\_multi\_* integration
 *			Proxy support for HTTP and SOCKS proxies
 *			Complete header customization
 *			Enhanced SSL Support
 *			Parsing methods for extracting useful data from scraped pages
 *
 *		All Parsing methods were written by Mike Schrenk in his book Webbots Spiders and Screenscrapers, the original source is available at http://www.schrenk.com/nostarch/webbots/DSP_download.php
 */

class HTTPBot
{

	/**
	 * @var string $cookies - Location of cookie file
	 * @var string $proxy - Address of currently set proxy
	 * @var string $proxyType - Type of proxy (HTTP or SOCKS)
	 * @var string $credentials - Credentials to use for proxy
	 * @var array $urls - queue of URLs to process
	 * @var bool $verbose - verbose output from class
	 * @var array $headers - Array of headers to use for requests
	 * @var object $ch - cURL Handle
	 */

	private $proxy, $urls, $requests;

	/**
	 *	__construct($proxy, $type, $credentials, $cookies)
	 *
	 *		Will Create an instance of webBot, initializes the cookie file, any proxy settings, as well as generating a default set of headers
	 *
	 * @param string $proxy - A string containing the proxy address (default: null)
	 * @param string $type - The type of Proxy to use(HTTP or SOCKS) (default: 'HTTP')
	 * @param string $credentials - The Credentials to use for the proxy (default: null)
	 * @param string $cookies - The file to store cookies for the bot (default: 'cookies.txt')
	 *
	 * @return void
	 */

	public function __construct($proxy = NULL)
	{
		$this->urls = new \Ds\Stack();
		$this->requests = new \Ds\Stack();

		$this->proxy = (is_a($proxy, "Proxy")) ? $proxy : new Proxy();
	}

	public function requestGET($url, $headers=null, $params=null) {
	
	}

	/**
	 *	curlMultiRequest($nodes)
	 *
	 *		Accepts an array of URLs to scrape, each element in the array is a sub-array.
	 *		For GET requests the sub-array needs only one element, the URL. For POST requests
	 *		the subarray should have a second element which is yet another array containing
	 *		POST parameters to be sent.
	 *
	 * @param array $nodes - Contains an array of arrays, each subarray contains at least one URL and an optional set of POST parameters to send (default: $this->urls)
	 *
	 * @return array
	 */
	function curlMultiRequest($nodes = NULL)
	{
		$proxy = $this->proxy->getProxy();

		if(is_a($nodes, "SplQueue"))
			$this->urls = $nodes;

		$mh = curl_multi_init();

		$curlArray = array();
		$counter = $this->urls->count();
		for($i = 0; $i < $counter; $i++)
		{
			$url = $this->urls->dequeue();
			$curlArray[$i] = $this->setupCURL();
			$this->delHeader("Referer");
			$this->addHeader("Referer: " . $url[0]);

			$curlArray[$i] = $this->proxy->setProxy($proxy);

			curl_setopt($curlArray[$i], CURLOPT_URL, $url[0]);
			curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER,1);
			curl_setopt($curlArray[$i], CURLOPT_HTTPHEADER, $this->headers);
			curl_setopt($curlArray[$i], CURLOPT_POST, 0);
			if(array_key_exists(1, $url) && $url[1] != null)
			{
				curl_setopt($curlArray[$i], CURLOPT_POST, 1);
				curl_setopt($curlArray[$i], CURLOPT_POSTFIELDS, $this->generatePOSTData($url[1]));
			}
			curl_multi_add_handle($mh, $curlArray[$i]);
			$this->delHeader("Referer");
		}
		$active = null;
		do
		{
			$mrc = curl_multi_exec($mh, $active);
		} while($mrc == CURLM_CALL_MULTI_PERFORM);

		while($active && $mrc == CURLM_OK)
		{
			do
			{
				$mrc = curl_multi_exec($mh, $active);
			} while ($mrc == CURLM_CALL_MULTI_PERFORM);
		}
		if ($mrc != CURLM_OK)
			trigger_error("Curl multi read error $mrc\n", E_USER_WARNING);

		$res = array();
		foreach($nodes as $i => $url)
		{
			$curlError = curl_error($curlArray[$i]);
			if($curlError == "")
				$res[$url[0]] = curl_multi_getcontent($curlArray[$i]);
			curl_multi_remove_handle($mh, $curlArray[$i]);
		}

		curl_multi_close($mh);
		$this->urls = array();

		return $res;
	}

}
