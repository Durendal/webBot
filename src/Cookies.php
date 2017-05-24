<?php
/*
  Review CURLOPT_COOKIELIST to resolve cookie issues
*/
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

 class Cookies
 {
     private $cookies;
     private $cookieJar;
     private $parentHandle;

     /**
      *   __construct(&$ch, $cookies = array(), $cookieJar = "cookies.txt")
      *
      *     Constructs a fresh Cookies object and sets any cookies passed to it.
      *
      * @param cURL &$ch - cURL handle of the parent request
      * @param array $cookies - An array of custom cookies to set.
      * @param string $cookieJar - The location of the file to write/read cookies from
      *
      * @return void
      */
     public function __construct(&$ch, $cookies = array(), $cookieJar = "cookies.txt") {
       $this->parentHandle = $ch;
       $this->cookieJar = $cookieJar;

       // Set cookie jar
       curl_setopt($this->parentHandle, CURLOPT_COOKIEJAR, $cookieJar);
       curl_setopt($this->parentHandle, CURLOPT_COOKIEFILE, $cookieJar);

       // Clear Cookies in cookie jar
       curl_setopt($this->parentHandle, CURLOPT_COOKIELIST, "ALL");

       // Populate Cookiejar with any custom submitted cookies
       foreach($cookies as $cookie)
          curl_setopt($this->parentHandle, CURLOPT_COOKIELIST, $cookie);

     }

	 public function __toString() {
		 return "<HTTP Cookies - >";
	 }
  /**
 	 *	setCookie($cookie)
 	 *
 	 *		sets the cookie file to $cookie and rebuilds the curl handler.
 	 *		note that if you already have an instance of the curlHandler
 	 *		instantiated, you will need to rebuild it via rebuildHandle()
 	 *		for this to take effect
 	 *
 	 * @param string $key - The name for the cookie being added
   * @param string $value - The value of the cookie being added
 	 *
 	 * @return void
 	 */
 	public function setCookie($key, $value)
 	{
 		$this->cookies[$key] = $value;
 	}

  /**
   *    generateCookies()
   *
   *      Returns a string built from an array of Cookies
   *
   * @return string - A string containing the currently set cookies
   */
 	public function generateCookies()
 	{
 		return implode(";", $this->cookies);
 	}

 	/**
 	 *	getCookie()
 	 *
 	 *		returns the name of the current file where cookies are stored
 	 *
 	 * @return string
 	 */
 	public function getCookie()
 	{
 		return $this->cookies;
 	}
}
