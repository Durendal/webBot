<?php
/**
 *		Request.php - An object to represent an outgoing request from webBot
 *
 *		This class helps constructing HTTP requests
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */
 namespace Durendal\webBot;

 require_once 'Cookies.php';
 require_once 'Headers.php';
 require_once 'Response.php';
 require_once 'Proxy.php';

 class Request
 {
  /**
 	 * @var string $cookies - Cookie Object to use
 	 * @var array $headers - Array of headers to use for requests
 	 * @var array $targetURL - URL to download
   * @var string $method - HTTP method to use
 	 * @var string $proxy - Currently set Proxy
 	 * @var object $handle - cURL Handle
   * @var array $pData - additional data to be sent on POST and PUT requests
 	 */

   private $cookies;
   private $headers;
   private $targetURL;
   private $method;
   private $proxy;
   private $handle;
   private $pData;

   /**
    *   __construct($url, $proxy, $method="GET", $cookies=NULL, $headers=NULL)
    *
    *     Creates a Request object with any relevant proxy, cookies, and headers.
    *
    * @param string $url - The URL to scrape
    * @param object $proxy - The Proxy settings to use for the request
    * @param string $method - The HTTP Method to use for the request
    * @param object $cookies - The Cookies to use for the request
    * @param object $headers - The Headers to use for the request
    *
    * @return void
    */
   public function __construct($url, $proxy, $method="GET", $cookies=NULL, $headers=NULL) {

     $this->setURL($url);
     $this->method = $method;
     $this->setProxy($proxy);
     $this->pData = (strtoupper($pData) == "POST" || strtoupper($pData) == "PUT") ? array() : NULL;

     if($headers)
       $this->setHeaders($headers);
     else
       $this->headers = new Headers();

     $this->handle = new cURLHandle($headers=$this->headers, $proxy=$this->proxy);

     if($cookies)
        $this->setCookies($cookies);
     else
       $this->cookies = new Cookies($this->handle->getHandle());


      $this->handle->setCookie($this->cookies);
   }

   public function __toString() {
	   return "<HTTP Request - {$this->targetURL}>";
   }

   /**
    *   addPOSTData($data)
    *
    *     Takes an array of key->value pairs to be sent as POST data and adds
    *     to the current POST data array
    *
    * @param array $data - The Data to add to the Array
    *
    * @return void
    */
   public function addPOSTData($data) {
       if(is_array($data))
	   	$this->pData = array_merge($data, $this->pData);
   }

   /**
    *   setProxy($proxy)
    *
    *     Checks that $proxy is a valid proxy object, if so it assigns it to
    *     the current request, otherwise a fresh Proxy object is created.
    *
    * @param object $proxy - The proxy object to use for the request
    *
    * @return void
    */
   public function setProxy($proxy) {
       $this->proxy = (is_a($proxy, "Durendal\webBot\Proxy")) ? $proxy : new Proxy();
   }

   /**
    *   setCookies($cookies)
    *
    *     Checks that $cookies is a valid cookie object, if so it assigns it to
    *     the current request, otherwise a fresh Cookie object is created.
    *
    * @param object $cookies - The cookies object to use for the request
    *
    * @return void
    */
	public function setCookies($cookies) {
		$this->cookies = (is_a($cookies, "Durendal\webBot\Cookies")) ? $cookies : new Cookies($this->handle->getHandle());
	}

   /**
    *   getCookies()
    *
    *     Returns the currently set cookie object
    *
    * @return object $cookies - The currently set cookie object
    */
   public function getCookies() {
     return $this->cookies;
   }

   /**
    *   setHeaders($headers)
    *
    *     Checks that $headers is a valid header object, if so it assigns it to
    *     the current request, otherwise a fresh Header object is created.
    *
    * @param object $headers - The headers object to use for the request
    *
    * @return void
    */
   public function setHeaders($headers) {
     $this->headers = (is_a($headers, "Durendal\webBot\Headers")) ? $headers : new Headers();

   }

   /**
    *   getHeaders()
    *
    *     Returns the currently set Headers object.
    *
    * @return object $this->headers - The currently set headers object
    */
   public function getHeaders() {
     return $this->headers;
   }

   /**
    *   setURL($url)
    *
    *     Sets $url to the target URL
    *
    * @param string $url - The URL to scrape
    *
    * @return void
    */
   public function setURL($url) {
     $this->targetURL = $url;
   }

   /**
    *   getURL()
    *
    *     Returns the currently set URL
    *
    * @return string $this->url - The currently set URL
    */
   public function getURL() {
     return $this->url;
   }

   /**
    *   run()
    *
    *     Executes the request with its set proxy, header, and cookie settings
    *
    * @return object Response - The response to the HTTP Request
    */
   public function run() {
     return $this->handle->requestHTTP($this->getURL(), $this->method, '', $this->pData);
   }
 }
