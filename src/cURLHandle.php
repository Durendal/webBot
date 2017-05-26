<?php
/**
 *		cURLHandle.php - An object to represent a cURL handle on HTTPBot
 *
 *		This class helps constructing cURL Handles
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */
 namespace Durendal\webBot;

use Durendal\webBot as webBot;

require_once 'Cookies.php';
require_once 'Headers.php';
require_once 'Proxy.php';
require_once 'Exceptions.php';

 class cURLHandle {

   /**
    * @var object $handle - The cURL handle
    * @var object $cookies - The Cookies object to use for this handle
    * @var object $headers - The Headers object to use for this handle
    * @var object $proxy - The Proxy object to use for this handle
    */

   private $handle;
   private $cookies;
   private $headers;
   private $proxy;

   /**
    *   __construct($headers=NULL, $proxy=NULL)
    *
    *     Constructs a cURL handle object using any headers and proxy settings
    *     submitted. Cookies must be added after instantiation via $this->setCookies()
    *     since $this->handle must already be instantiated before a Cookie object
    *     can be created.
    *
    * @param object $headers - The headers object to use for the handle
    * @param object $proxy - The proxy object to use for the handle
    *
    * @return void
    */
   public function __construct($proxy=NULL, $cookies = NULL, $headers = NULL) {

      $this->handle = $this->setupCURL();
      $this->handle = $this->setProxy($proxy);
      $this->setHeaders($headers);
	  $this->setCookies($cookies);
   }

	public function __toString() {
		return "<cURL Handle - >";
	}

   /**
    *   setCookies($cookies)
    *
    *     Checks if $cookies is a valid Cookies object, if so it is assigned to
    *     $this->cookies, otherwise a fresh Cookie object is created with the
    *     current handle.
    *
    * @param object $cookies - The Cookies object to use for this handle
    *
    * @return void
    */
   public function setCookies($cookies, $ch = NULL) {
     $this->cookies = (is_a($cookies, "Durendal\webBot\Cookies")) ? $cookies : new webBot\Cookies();
	 $this->cookies->init($this->handle);
   }

  /**
   *   setHeaders($headers)
   *
   *     Checks if $headers is a valid Headers object, if so it is assigned to
   *     $this->headers, otherwise a fresh Headers object is created.
   *
   * @param object $headers - The Headers object to use for this handle
   *
   * @return void
    */
   public function setHeaders($headers, $ch = NULL) {
      $this->headers = (is_a($headers, "Durendal\webBot\Headers")) ? $headers : new webBot\Headers();
	  $this->headers->init($this->handle)
   }

  /**
   *   setProxy($proxy)
   *
   *     Checks if $proxy is a valid Proxy object, if so it is assigned to
   *     $this->proxy, otherwise a fresh Proxy object is created.
   *
   * @param object $proxy - The Proxy object to use for this handle
   * @param cURL $ch - The cURL handle to use, if none is specified $this->handle is used
   *
   * @return void
    */
   public function setProxy($proxy, $ch = NULL) {

      $this->proxy = (is_a($proxy, "Durendal\webBot\Proxy")) ? $proxy : new webBot\Proxy();
	  $this->proxy->init($this->handle);
   }

   /**
    *   getHandle()
    *
    *     Returns the currently set cURL Handle
    *
    * @return cURL $this->handle - The currently set cURL handle
    */
   public function getHandle() {
     return $this->handle;
   }

   /**
 	 *	setSSL($verify, $hostval, $certfile, $ch)
 	 *
 	 *		Allows the user to adjust SSL settings on a cURL handle directly, If verify is set to TRUE
 	 *		then the following $hostval and $certfile parameters are required, otherwise
 	 *		they can be ommitted.
 	 *
 	 * @param bool $verify - Whether or not to verify SSL Certificates (default: FALSE)
 	 * @param int $hostval - Set the level of verification required: (default: 0)
 	 *						- 0: Don’t check the common name (CN) attribute
 	 *						- 1: Check that the common name attribute at least exists
 	 *						- 2: Check that the common name exists and that it matches the host name of the server
 	 * @param string $certfile - The location of the certificate file you wish to use (default: '')
 	 * @param object $ch - The cURL handle to use (default: $ch)
 	 *
 	 * @return object
 	 */
 	public function setSSL($verify = FALSE, $hostval = 0, $certfile = '', $ch = NULL)
 	{
 		if(!$ch)
 			$ch = $this->handle;

 		if($verify)
 		{
 			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
 			if($hostval >= 0 && $hostval < 3 && $certfile != '')
 			{
 				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $hostval);
 				curl_setopt($ch, CURLOPT_CAINFO, $certfile);
 			}
 		}
 		else
 		{
 			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
 		}

 		return $ch;
 	}

 	/**
 	 *	  downloadImage($url, $outfile, $ref)
 	 *
 	 *		  Will download an image from specified URL and save it to $outfile.
 	 *
 	 * @param string $url - The URL of the image
 	 * @param string $outfile - The location to write the image to
 	 * @param string $ref - The value to use as a referer in the request (default: $url)
 	 *
 	 * @return void
 	 */
 	public function binaryDownload($url, $outfile = "image.jpg", $ref = NULL, $ch = NULL)
 	{
    if(!$ch)
      $ch = $this->handle;
 		if(!$ref)
 			$ref = $url;
 		$this->headers->delHeader("Referer");
 		$this->headers->addHeader("Referer: $ref");
 		$fp = fopen($outfile, "wb");
 		curl_setopt($ch, CURLOPT_HEADERFUNCTION, array($this, "cookieSnatcher"));
 		curl_setopt($ch, CURLOPT_URL, $url);
 		curl_setopt($ch, CURLOPT_FILE, $fp);
 		curl_setopt($ch, CURLOPT_HEADER, FALSE);
 		curl_exec($ch);
 		curl_setopt($ch, CURLOPT_HEADER, TRUE);
 		#curl_setopt($ch, CURLOPT_FILE, NULL);
 		fclose($fp);
 		$errno = curl_errno($ch);
 		$err = curl_error($ch);
 		if($errno)
 			die("$errno: $err\n");

 		$this->rebuildHandle();
 		$this->headers->delHeader("Referer");
 	}

 	/**
 	 *	setupCURL()
 	 *
 	 *		Creates and returns a new generic cURL handle
 	 *
 	 * @return object
 	 */
 	private function setupCURL()
 	{

 		$ch = curl_init();
 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
 		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
 		curl_setopt($ch, CURLOPT_HEADER, 1);
 		curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies->getCookieJar());
 		curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies->getCookieJar());

 		return $ch;
 	}

  /**
	 *	rebuildHandle()
	 *
	 *		rebuilds the cURL Handler for the next request
	 *
	 * @return void
	 */
	public function rebuildHandle()
	{
		curl_close($this->handle);
		$this->handle = $this->setupCURL();
		$this->handle = $this->setProxy($this->proxy);
	}

 	/**
 	 *	requestGET($url, $ref)
 	 *
 	 *		makes a GET based HTTP Request to the url specified in $url using the referer specified in $ref
 	 *		if no $ref is specified it will use the $url
 	 *
 	 * @param string $url - The URL to request (default: NULL)
 	 * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
 	 *
 	 * @return string
 	 */
 	public function requestGET($url, $ref='', $ch = NULL)
 	{
    if(!$ch)
      $ch = $this->handle;

 		if($ref == '')
 			$ref = $url;

 		$this->headers->delHeader("Referer");
 		$this->headers->addHeader("Referer: $ref");

 		curl_setopt($ch, CURLOPT_URL, $url);
 		curl_setopt($ch, CURLOPT_POST, 0);
 		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers->getHeaders());

 		$x = curl_exec($ch);

 		$errno = curl_errno($ch);
 		$err = curl_error($ch);
 		if($errno)
 			die("$errno: $err\n");

 		$this->headers->delHeader("Referer");

 		return new Response($ch, $x);
 	}

 	/**
 	 *	requestPOST($url, $pData, $ref)
 	 *
 	 *		makes a POST based HTTP Request to the url specified in $url using the referer specified in $ref
 	 *		and the parameters specified in $pData. If no $ref is specified it will use the $url
 	 *
 	 * @param string $purl - The URL to request (default: NULL)
 	 * @param string $pData - The POST parameters to send, this string should have been returned from $this->generatePOSTData()
 	 * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
 	 *
 	 * @return string
 	 */
 	public function requestPOST($purl, $pData, $ref = '', $ch = NULL)
 	{
    if(!$ch)
      $ch = $this->handle;

 		if($ref == '')
 			$ref = $purl;

 		$this->headers->delHeader("Referer");
 		$this->headers->addHeader("Referer: $ref");

    if(is_array($pData))
      $pData = $this->generatePOSTData($pData);

 		curl_setopt($ch, CURLOPT_URL, $purl);
 		curl_setopt($ch, CURLOPT_POST, 1);
 		curl_setopt($ch, CURLOPT_POSTFIELDS, $pData);
 		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers->getHeaders());
 		curl_setopt($ch, CURLOPT_POSTREDIR, 3);
 		$x = curl_exec($ch);

 		$errno = curl_errno($ch);
 		$err = curl_error($ch);

 		if($errno)
 			die("$errno: $err\n");

 		curl_setopt($ch, CURLOPT_POST, 0);
 		$this->headers->delHeader("Referer");

 		return new Response($x);
 	}

 	/**
 	 *	requestPUT($url, $ref, $pData)
 	 *
 	 *		Makes a PUT based HTTP request to the url and POST data specified.
 	 *
 	 * @param string $url - The URL to send the request to
 	 * @param string $ref - The Referer to use in the request
 	 * @param string $pData - The POST data to send in the request
 	 *
 	 * @return string
 	 */
 	public function requestPUT($url, $ref = '', $pData, $ch = NULL)
 	{

    if(!$ch)
      $ch = $this->handle;

 		if($ref == '')
 			$ref = $url;

 		$fh = tmpfile();
 		fwrite($fh, $pData);
 		fseek($fh, 0);

 		$this->headers->delHeader("Referer");
 		$this->headers->addHeader("Referer: $ref");
    $fh = fopen($file, 'r');
    $fileContents = file_get_contents($file);

    if(is_array($pData))
      $pData = $this->generatePOSTData($pData);

 		curl_setopt($ch, CURLOPT_URL, $url);
 		curl_setopt($ch, CURLOPT_PUT, TRUE);
 		curl_setopt($ch, CURLOPT_INFILE, $fh);
 		curl_setopt($ch, CURLOPT_INFILESIZE, strlen($pData));
 		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
 		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
 		curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers->getHeaders());

 		$x = curl_exec($ch);

 		$errno = curl_errno($ch);
 		$err = curl_error($ch);
 		if($errno)
 			die("$errno: $err\n");

 		$ch = $this->rebuildHandle();
 		$this->headers->delHeader("Referer");

 		return new Response($x);
 	}

 	/**
 	 *	requestHTTP($type, $url, $ref, $pData)
 	 *
 	 *		simple wrapper method for requestGET, requestPUT and requestPOST. Returns NULL on error
 	 *
 	 * @param string $method - The type of request to make(GET or POST) (default: 'GET')
 	 * @param string $url - The URL to request (default: NULL)
 	 * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
 	 * @param string $pData - The POST parameters to send, this string should have been returned from $this->generatePOSTData() (default: NULL)
 	 *
 	 * @return string
 	 */
 	public function requestHTTP($url, $method = "GET", $ref = '', $pData = NULL, $ch = NULL)
 	{
    if(!$ch)
      $ch = $this->handle;

 		switch($method) {

      case "GET":
 				return $this->requestGET($url, $ref, $ch);
 			case "POST":
 				return $this->requestPOST($url, $pData, $ref, $ch);
 			case "PUT":
 				return $this->requestPUT($url, $ref, $pData, $ch);
 			default:
 				return NULL;
 		}
 	}

  /**
   *	generatePOSTData($data)
   *
   *		generates a urlencoded string from an associative array of POST parameters
   *
   * @param array $data - An array of POST parameters in array($key => $val, ...) format
   *
   * @return string
   */
  public function generatePOSTData($data)
  {
    $params = '';
    foreach($data as $key => $val)
      $params .= curl_escape($this->handle, $key) . '=' . curl_escape($this->handle, $val) . '&';

    // trim trailing &
    return substr($params, 0, -1);
  }

  /**
	 *	  cookieSnatcher($ch, $headerLine)
	 *
	 *		  Read through cookies sent and parse them how you see fit
	 *
	 * @param object $ch - The cURL handle to read from
	 * @param string $headerLine - The current line in headers to check
	 *
	 * @return int
	 */
	function cookieSnatcher($ch, $headerLine) {
		//print "============================================================\n";
		//print $headerLine."\n";
		//print "============================================================\n";
		//if(preg_match('/^Location:\s*([^;]*)/mi', $headerLine, $page) == 1){
		//	$this->cookies[] = $page[1];
		//}
		if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1)
			$this->cookies[] = $cookie[1];
			//print $cookie[1]."\n";
		//}
		return strlen($headerLine); // Needed by curl
	}
}
