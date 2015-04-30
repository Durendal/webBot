<?php
	/*
			File: webBot.php
			Author: Durendal

			webBot.php aims to simplify the use of cURL with php. At the moment it only
			handles GET and POST HTTP requests but I may add more to it as time and
			interest permits. 

	*/

	class webBot
	{
		private $cookies;		// Cookie File
		private $ch;			// cURL Handler
		private $proxy;			// Proxy Address
		private $proxtype;		// Proxy Type
		private $credentials;	// Proxy Credentials
		private $urls;			// Stack of URLs
		private $verbose;		// Verbose output from class
		private $headers;		// HTTP Headers
			

		public function __construct($proxy = null, $type = 'HTTP', $credentials = null, $cookies = 'cookies.txt')
		{
			
			$this->setCookie($cookies);
			$this->ch = $this->setupCURL();
			$this->ch = $this->setProxy($proxy, $type, $credentials);
			$this->urls = array();
			$verbose = true;
			$this->headers = $this->defaultHeaders();
			
		}

		/*
			setVerbose($mode)

				turns on and off class verbosity. It can take a boolean value directly
				or if called without any parameters, it will simply invert its current value.
		*/

		public function setVerbose($mode = null)
		{
			if($mode)
				$this->verbose = $mode;
			else
				$this->verbose = !$this->verbose;
		}

		/*
			defaultHeaders()

				sets some default headers to use for requests, these can be edited and added to.
		*/
		public function defaultHeaders()
		{
			$this->addHeader("Connection: Keep-alive");
			$this->addHeader("Keep-alive: 300");
			$this->addHeader("Expect:");
			$this->addHeader("User-Agent: " . $this->randomAgent());
		}

		/*
			addHeader($header)

				checks if $header already exists in the headers array, if not it adds it.
		*/
		public function addHeader($header)
		{
			if($this->checkHeader($header))
			{
				if($this->verbose)
					print "This header is already set. Try deleting it then resetting it.\n";
				return false;
			}
			$this->headers[] = $header;
		}

		/*
			checkHeader($header)

				checks if $header already exists in the headers array.
		*/
		public function checkHeader($header)
		{
			foreach($this->headers as $i => $head)
				if(substr($head, 0, strlen($header)-1) == $header)
					return true;

			return false;
		}

		/*
			delHeader($header)

				checks for $header in $this->headers and deletes it if it exists.
		*/
		public function delHeader($header)
		{
			if($this->checkHeader($header))
			{
				unset($this->headers[$i]);
				$this->headers = array_values($this->headers);
			}
			
		}
		/*
			changeHeader($header, $val)

				deletes $header if it exists, then adds $val as a header.
		*/
		public function changeHeader($header, $val)
		{
			$this->delHeader($header);
			if(strpos($val, $header))
				$this->addHeader($val);
			else
				$this->addHeader($header.": ".$val);
		}

		/*
			getHeaders()

				returns a list of the currently set headers
		*/
		public function getHeaders()
		{
			return $this->headers;
		}

		

		/*
			setProxy($py, $type, $creds, $ch)

				will set the proxy using the specified credentials and type,
				by default it assumes an HTTP proxy with no credentials. To 
				use a SOCKS proxy simply pass the string 'SOCKS' as the third 
				parameter. If no parameters are sent, it will remove any proxy
				settings and begin routing in the clear. The fourth parameter is
				an optional curl handler to use instead of $this->ch, this decoupling
				allows for the curl_multi_request() method to use it as well.

		*/
		public function setProxy($py = null, $type = 'HTTP', $creds = null, $ch = null)
		{
			$this->proxy = $py;
			$this->credentials = $creds;
			$this->proxtype = $type;
			if(!$ch)
				$ch = $this->ch;
			if($py)
			{
				// Check for SOCKS or HTTP Proxy
				if(strtoupper($this->proxtype) == 'SOCKS')
					curl_setopt($ch, CURLOPT_PROXYTYPE, 7);
				else
					curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
				curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
				if($this->verbose)
					print "Using {$this->proxtype} Proxy: {$this->proxy} ";
				if($this->credentials)
				{
					if($this->verbose)
						print "Credentials: {$this->credentials}";
					curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->credentials);
				}
				if($this->verbose)
					print "\n";
			}
			// Disable Proxy Support if called with no parameters
			else
			{
				if($this->verbose)
					print "Disabling Proxy.\n";
				curl_setopt($ch, CURLOPT_PROXYTYPE, null);
				curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
				curl_setopt($ch, CURLOPT_PROXY, null);
				curl_setopt($ch, CURLOPT_PROXYUSERPWD, null);
				$this->proxy = null;
				$this->proxtype = 'HTTP';
				$this->credentials = null;
			}

			return $ch;
		}

		/*
			getProxy()
			
				returns an array with the currently set proxy, credentials, and its type.
		*/
		public function getProxy()
		{
			return array('proxy' => $this->proxy, 'credentials' => $this->credentials, 'type' => $this->proxtype);
		}

		/*
			setCookie($cookie)
			
				sets the cookie file to $cookie and rebuilds the curl handler.
				note that if you already have an instance of the curlHandler 
				instantiated, you will need to rebuild it via rebuildHandler()
				for this to take effect
		*/
		public function setCookie($cookie)
		{
			$this->cookies = $cookie;
		}

		/*
			getCookie()
			
				returns the current file where cookies are stored
		*/
		public function getCookie()
		{
			return $this->cookies;
		}



		/*
			setRandomAgent()
			
				returns a useragent at random to one from the list below
					
			List of user-agents from: https://techblog.willshouse.com/2012/01/03/most-common-user-agents/
		*/
		public function randomAgent()
		{
			$agents = array("Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/8.0.5 Safari/600.5.17",
					"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.4.10 (KHTML, like Gecko) Version/8.0.4 Safari/600.4.10",
					"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko",
					"Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F70 Safari/600.1.4",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12D508 Safari/600.1.4",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/8.0.3 Safari/600.3.18",
					"Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) like Gecko",
					"Mozilla/5.0 (Windows NT 6.1; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
					"Mozilla/5.0 (iPad; CPU OS 8_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12D508 Safari/600.1.4",
					"Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12F69 Safari/600.1.4",
					"Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36",
					"Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 Chrome/41.0.2272.76 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.4.10 (KHTML, like Gecko) Version/7.1.4 Safari/537.85.13",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.5.17 (KHTML, like Gecko) Version/7.1.5 Safari/537.85.14",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 7_1_2 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Version/7.0 Mobile/11D257 Safari/9537.53",
					"Mozilla/5.0 (Windows NT 6.1; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (Windows NT 5.1; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_3 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12B466 Safari/600.1.4",
					"Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko",
					"Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0)",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20100101 Firefox/31.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_5) AppleWebKit/537.78.2 (KHTML, like Gecko) Version/6.1.6 Safari/537.78.2",
					"Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Windows NT 6.1; WOW64; rv:35.0) Gecko/20100101 Firefox/35.0",
					"Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 8_1_2 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12B440 Safari/600.1.4",
					"Mozilla/5.0 (X11; Linux x86_64; rv:31.0) Gecko/20100101 Firefox/31.0",
					"Mozilla/5.0 (X11; Linux x86_64; rv:37.0) Gecko/20100101 Firefox/37.0",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/8.0.4 Safari/600.4.10",
					"Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (iPhone; CPU iPhone OS 8_1 like Mac OS X) AppleWebKit/600.1.4 (KHTML, like Gecko) Version/8.0 Mobile/12B411 Safari/600.1.4",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10) AppleWebKit/600.1.25 (KHTML, like Gecko) Version/8.0 Safari/600.1.25",
					"Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/534.59.10 (KHTML, like Gecko) Version/5.1.9 Safari/534.59.10",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/600.3.18 (KHTML, like Gecko) Version/7.1.3 Safari/537.85.12",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/39.0.2171.95 Safari/537.36",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.78.2 (KHTML, like Gecko) Version/7.0.6 Safari/537.78.2",
					"Mozilla/5.0 (Windows NT 5.1; rv:36.0) Gecko/20100101 Firefox/36.0",
					"Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36 OPR/28.0.1750.51",
					"Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2272.89 Safari/537.36");
			
			return $agents[rand(0,count($agents)-1)];

		}

		/*
			pushURL($url, $pdata)

				Adds a URL to $this->urls stack. If it is a POST request, 
				also send an array of the POST parameters
		*/
		public function pushURL($url, $pdata = null)
		{
			if($this->verbose)
				print "Pushing $url onto list\n";
			array_push($this->urls, array($url, $pdata));
		}

		/*
			popURL()

				returns the top URL from the $this->urls stack or null
				on error. Removes that item from the array.
		*/
		public function popURL()
		{
			if($this->urlCount() > 0)
			{
				$url = array_pop($this->urls);
				if($this->verbose)
					print "Popping " . $url[0] . " from list\n";
				return $url;
			}
			if($this->verbose)
				print "No URLs to pop.\n";
			return null;
		}

		/*
			peekURL()

				returns the top URL from the $this->urls stack or null
				on error
		*/
		public function peekURL()
		{
			if($this->urlCount() > 0)
				return end($this->urls);
			if($this->verbose)
				print "No URLs to peek.\n";
			return null;

		}

		/*
			urlCount()

				returns the current number of URLs in the $this->urls stack.
		*/
		public function urlCount()
		{
			return count($this->urls);
		}

		/*
			setupCURL()
			
				Creates and returns a new generic cURL handler
		*/
		private function setupCURL()
		{
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookies);
			curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookies);

			return $ch;
		}

		/*
			requestGET($url, $ref)
			
				makes a GET based HTTP Request to the url specified in $url using the referer specified in $ref
				if no $ref is specified it will use the $url
		*/

		public function requestGET($url = null, $ref='')
		{
			if($url == null)
				if($this->urlCount() > 0)
				{

					$url = $this->popURL();
					$url = $url[0];
				}
				else
				{
					if($this->verbose)
						print "No URLs currently in stack\n";
					return 0;
				}
			
			if($ref == '')
				$ref = $url;
			$this->addHeader("Referer: $ref");
						
			curl_setopt($this->ch, CURLOPT_URL, $url);
			curl_setopt($this->ch, CURLOPT_POST, 0);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
			$x = curl_exec($this->ch);

			return $x;
		}


		/*
			requestGET($url, $pdata, $ref)
			
				makes a POST based HTTP Request to the url specified in $url using the referer specified in $ref
				and the parameters specified in $pdata. If no $ref is specified it will use the $url
		*/

		public function requestPOST($purl = null, $pdata, $ref='')
		{
			if($purl == null)
				if($this->urlCount() > 0)
					$purl = $this->popURL();
				else
				{
					if($this->verbose)
						print "No URLs currently in stack\n";
					return 0;
				}
			if($ref == '')
				$ref = $purl;
			$this->addHeader("Referer: $ref");
						
			curl_setopt($this->ch, CURLOPT_URL, $purl);
			curl_setopt($this->ch, CURLOPT_POST, 1);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $pdata);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
			
			$x = curl_exec($this->ch);

			curl_setopt($this->ch, CURLOPT_POST, 0);

			return $x;
		}

		/*
			requestHTTP($type, $url, $ref, $pdata)

				simple wrapper method for requestGET and requestPOST
		*/
		public function requestHTTP($type = "GET", $url = null, $ref = '', $pdata = null)
		{
			if($type == "GET")
				return $this->requestGET($url, $ref);
			else if($type == "POST" && $pdata != null)
				return $this->requestPOST($url, $pdata, $ref);
			
			if($this->verbose)
				print "Invalid Request type submitted.\n";
			return null;
		}

		/*
				curl_multi_request($nodes)

					Accepts an array of URLs to scrape, each element in the array is a sub-array.
					For GET requests the sub-array needs only one element, the URL. For POST requests
					the subarray should have a second element which is yet another array containing
					POST parameters to be sent.
		*/
		function curl_multi_request($nodes = null)
		{ 
			$py = $this->getProxy();
			
			if($nodes != null)
				$this->urls = $nodes;
			else
				$nodes = $this->urls;

	        $mh = curl_multi_init();

	        $curl_array = array(); 

	        //foreach($nodes as $i => $url) 
	        for($i = 0; $i < $this->urlCount(); $i++)
	        { 
	        	$url = $this->popURL();
	        	$curl_array[$i] = $this->setupCURL();
	        	if($this->checkHeader("Referer"))
	        		$this->delHeader("Referer");
	        	$this->addHeader("Referer: " . $url[0]);
	        	
	        	$curl_array[$i] = $this->setProxy($py['proxy'], $py['type'], $py['credentials'], $curl_array[$i]);
	        	curl_setopt($curl_array[$i], CURLOPT_URL, $url[0]);
        		curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER,1);
        		curl_setopt($curl_array[$i], CURLOPT_HTTPHEADER, $this->headers);
        		curl_setopt($curl_array[$i], CURLOPT_POST, 0);
	        	if(array_key_exists(1, $url) && $url[1] != null)
	        	{
	        		curl_setopt($curl_array[$i], CURLOPT_POST, 1);
					curl_setopt($curl_array[$i], CURLOPT_POSTFIELDS, $this->generatePOSTData($url[1]));
	        	} 
	            curl_multi_add_handle($mh, $curl_array[$i]); 
	            $this->delHeader("Referer");
	        } 
	        $active = null; 
	        do 
	        { 
	            $mrc = curl_multi_exec($mh, $active); 
	        } while($mrc == CURLM_CALL_MULTI_PERFORM); 
	        
	        while ($active && $mrc == CURLM_OK) 
	        {
           		do 
           		{
               		$mrc = curl_multi_exec($mh, $active);
           		} while ($mrc == CURLM_CALL_MULTI_PERFORM);

    		}
    		if ($mrc != CURLM_OK)
      			trigger_error("Curl multi read error $mrc\n", E_USER_WARNING);
    		$nodes = $this->urls;
	        $res = array(); 
	      
	        foreach($nodes as $i => $url) 
	        {

	        	$curlError = curl_error($curl_array[$i]);
      			if($curlError == "")
	            	$res[$url[0]] = curl_multi_getcontent($curl_array[$i]); 
	            else
	            	if($this->verbose)
	            		print "Curl error on handle $url: $curlError\n";
	            curl_multi_remove_handle($mh, $curl_array[$i]); 
	        }
	        
	        curl_multi_close($mh);        
	        $this->urls = array();
	        return $res; 
		} 

		/*
			generatePOSTData($data)
			
				generates a urlencoded string from an associative array of POST parameters
		*/
		public function generatePOSTData($data)
		{
			$params = '';

			foreach($data as $key => $val)
				$params .= urlencode($key) . '=' . urlencode($val) . '&';
			
			// trim trailing &
			return substr($params, 0, -1);
		}

		/*
			rebuildHandler()
			
				rebuilds the cURL Handler for the next request
		*/
		public function rebuildHandler()
		{
			$this->setupCURL();
			$this->setProxy($this->proxy, $this->credentials, $this->proxtype);
			$this->setRandomAgent();
		}

		/*
			Parsing subroutines adapted from Mike Schrenks LIB_PARSE.php in Webbots spiders and screenscrapers http://webbotsspidersscreenscrapers.com/
		*/

		public function split_string($string, $delineator, $desired, $type)
		{
			// Case insensitive parse, convert string and delineator to lower case
			$lc_str = strtolower($string);
			$marker = strtolower($delineator);
			// Return text true the delineator
			if($desired == true)
			{
				if($type == true) // Return text ESCL of the delineator
					$split_here = strpos($lc_str, $marker);
				else // Return text false of the delineator
					$split_here = strpos($lc_str, $marker)+strlen($marker);

				$parsed_string = substr($string, 0, $split_here);
			}
			// Return text false the delineator
			else
			{
				if($type==true) // Return text ESCL of the delineator
					$split_here = strpos($lc_str, $marker) + strlen($marker);
				else // Return text false of the delineator
					$split_here = strpos($lc_str, $marker) ;

				$parsed_string = substr($string, $split_here, strlen($string));
			}
			return $parsed_string;
		}

		public function return_between($string, $start, $stop, $type)
		{
			$temp = $this->split_string($string, $start, false, $type);
			return $this->split_string($temp, $stop, true, $type);
		}

		public function parse_array($string, $beg_tag, $close_tag)
		{
			preg_match_all("($beg_tag(.*)$close_tag)siU", $string, $matching_data);
			return $matching_data[0];
		}

		public function get_attribute($tag, $attribute)
		{
			// Use Tidy library to 'clean' input
			$cleaned_html = tidy_html($tag);
			// Remove all line feeds from the string
			$cleaned_html = str_replace(array("\r\n", "\n", "\r"), "", $cleaned_html);
			
			// Use return_between() to find the properly quoted value for the attribute
			return return_between($cleaned_html, strtoupper($attribute)."=\"", "\"", true);
		}

		public function remove($string, $open_tag, $close_tag)
		{
			# Get array of things that should be removed from the input string
			$remove_array = parse_array($string, $open_tag, $close_tag);
			
			# Remove each occurrence of each array element from string;
			for($xx=0; $xx<count($remove_array); $xx++)
				$string = str_replace($remove_array, "", $string);
			
			return $string;
		}
		public function tidy_html($input_string)
		{
			// Detect if Tidy is in configured
			if( function_exists('tidy_get_release') )
			{
				# Tidy for PHP version 4
				if(substr(phpversion(), 0, 1) == 4)
				{
					tidy_setopt('uppercase-attributes', TRUE);
					tidy_setopt('wrap', 800);
					tidy_parse_string($input_string);			
					$cleaned_html = tidy_get_output();  
				}
				# Tidy for PHP version 5
				if(substr(phpversion(), 0, 1) == 5)
				{
					$config = array(
								   'uppercase-attributes' => true,
								   'wrap'				 => 800);
					$tidy = new tidy;
					$tidy->parseString($input_string, $config, 'utf8');
					$tidy->cleanRepair();
					$cleaned_html  = tidy_get_output($tidy);  
				}
			}
			else
			{
				# Tidy not configured for this computer
				$cleaned_html = $input_string;
			}
			return $cleaned_html;
		}

		public function validateURL($url)
		{	
			$pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w]'
			.'[-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]'
			.'|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
			return preg_match($pattern, $url);
		}

	}