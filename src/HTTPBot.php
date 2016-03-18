<?php
/**
 *        HTTPBot.php - A simple interface to cURL with PHP
 *
 *        HTTPBot.php aims to simplify the use of cURL with php. At the moment it only
 *        handles GET and POST HTTP requests but I may add more to it as time and
 *        interest permits. 
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace Durendal\webBot;
require_once 'response.php';
/**
 *        HTTPBot is a class for interacting with cURL through PHP. It should significantly simplify the process
 *        providing several functions to manipulate the curl_setopt() function in various ways.
 *        
 *         Some of the main features:<br>
 *            Optional stack based URL queue<br>
 *            curl\_multi\_* integration<br>
 *            Proxy support for HTTP and SOCKS proxies<br>
 *            Complete header customization<br>
 *            Enhanced SSL Support<br>
 *            Parsing methods for extracting useful data from scraped pages<br>
 *
 *        All Parsing methods were written by Mike Schrenk in his book Webbots Spiders and Screenscrapers, the original source is available at http://www.schrenk.com/nostarch/webbots/DSP_download.php
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

    private $cookies, $proxy, $proxyType, $credentials, $urls, $verbose, $headers, $ch;
        
    /**
     *    __construct($proxy, $type, $credentials, $cookies)
     *
     *        Will Create an instance of webBot, initializes the cookie file, any proxy settings, as well as generating a default set of headers
     *
     * @param string $proxy - A string containing the proxy address (default: null)
     * @param string $type - The type of Proxy to use(HTTP or SOCKS) (default: 'HTTP')
     * @param string $credentials - The Credentials to use for the proxy (default: null)
     * @param string $cookies - The file to store cookies for the bot (default: 'cookies.txt')
     *
     * @return void
     */            

    public function __construct($proxy = null, $type = 'HTTP', $credentials = null, $cookies = 'cookies.txt')
    {
        $this->setCookie($cookies);
        $this->ch = $this->setupCURL();
        $this->ch = $this->setProxy($proxy, $type, $credentials);
        $this->urls = array();
        $verbose = false;
        $this->defaultHeaders();            


    }

    /**
     *    setVerbose($mode)
     *
     *        turns on and off class verbosity. It can take a boolean value directly
     *        or if called without any parameters, it will simply invert its current value.
     *
     * @param bool $mode - Sets verbosity mode (default: null)
     *
     * @return void
     */

    public function setVerbose($mode = null)
    {
        $this->verbose = ($mode) ? $mode : !$this->verbose;
    }

    /**
     *    defaultHeaders()
     *
     *        sets some default headers to use for requests, these can be edited and added to.
     *
     * @return void
     */
    public function defaultHeaders()
    {
        //$this->addHeader("Connection: Keep-alive");
        //$this->addHeader("Keep-alive: 300");
        //$this->addHeader("Expect:");
        $this->addHeader("User-Agent: " . $this->randomAgent());
    }

    /**
     *    addHeader($header)
     *
     *        checks if $header already exists in the headers array, if not it adds it.
     *
     * @param string $header - Contains the Header to add
     *
     * @return void
     */
    public function addHeader($header)
    {
        if($this->checkHeader($header)){
            if($this->verbose)
                print "This header is already set. Try deleting it then resetting it.\n";
            return;
        }
        $this->headers[] = $header;
    }

    /**
     *    checkHeader($header)
     *
     *        checks if $header already exists in the headers array. 
     *        If it finds the header it returns its index in the array, 
     *        otherwise it returns null.
     *
     * @param string $header - Contains the Header to check
     *
     * @return int
     */
    public function checkHeader($header)
    {
        if(count($this->headers) > 0)
            foreach($this->headers as $i => $head)
                if(stristr($head, $header))
                    return $i;

        return null;
    }

    /**
     *    delHeader($header)
     *
     *        checks for $header in $this->headers and deletes it if it exists.
     *
     * @param string $header - Contains the Header to delete
     *
     * @return void
     */
    public function delHeader($header)
    {
        // Ensure that $i is a valid index(which includes 0, if we only tested $i = ..., it would errenously return false)
        if(($i = $this->checkHeader($header)) >= 0){
            unset($this->headers[$i]);
            $this->headers = array_values($this->headers);
        }            
    }

    /**
     *    changeHeader($header, $val)
     *
     *        deletes $header if it exists, then adds $val as a header. $val can contain the header type and value, or just the value.
     *
     * @param string $header - Contains the Header to change
     * @param string $val - The value to change the header to
     *
     * @return void         
     */
    public function changeHeader($header, $val)
    {
        $this->delHeader($header);
        if(stristr($val, $header))
            $this->addHeader($val);
        else
            $this->addHeader("$header: $val");
    }

    /**
     *    getHeaders()
     *
     *        returns a list of the currently set headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *    setSSL($verify, $hostval, $certfile, $ch)
     *        
     *        Allows the user to adjust SSL settings on a cURL handle directly, If verify is set to true
     *        then the following $hostval and $certfile parameters are required, otherwise
     *        they can be ommitted.
     *
     * @param bool $verify - Whether or not to verify SSL Certificates (default: false)
     * @param int $hostval - Set the level of verification required: (default: 0)
     *                        - 0: Don’t check the common name (CN) attribute
     *                        - 1: Check that the common name attribute at least exists
     *                        - 2: Check that the common name exists and that it matches the host name of the server
     * @param string $certfile - The location of the certificate file you wish to use (default: '')
     * @param object $ch - The cURL handle to use (default: $this->ch)
     *
     * @return object
     */

    public function setSSL($verify = false, $hostval = 0, $certfile = '', $ch = null)
    {
        if(!$ch)
            $ch = $this->ch;
        
        if($verify){
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            if($hostval >= 0 && $hostval < 3 && $certfile != ''){
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $hostval);
                curl_setopt($ch, CURLOPT_CAINFO, $certfile);
            }
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        return $ch;
    }

    /**
     *    setProxy($proxy, $type, $creds, $ch)
     *
     *        will set the proxy using the specified credentials and type, by default it assumes an HTTP proxy with no credentials. To 
     *        use a SOCKS proxy simply pass the string 'SOCKS' as the second parameter. If no parameters are sent, it will remove any proxy
     *        settings and begin routing in the clear. The fourth parameter is an optional curl handler to use instead of $this->ch, this decoupling
     *        allows for the curlMultiRequest() method to use it as well.
     *
     * @param string $proxy - The address of the proxy to set (default: null)
     * @param string $type - The type of the proxy(HTTP or SOCKS) (default: 'HTTP')
     * @param string $creds - The credentials to use for the proxy (default: null)
     * @param object $ch - The cURL handle to use (default: $this->ch)
     *
     * @return object
     */
    public function setProxy($proxy = null, $type = 'HTTP', $creds = null, $ch = null)
    {
        $this->proxy = $proxy;
        $this->credentials = $creds;
        $this->proxyType = $type;
        if(!$ch)
            $ch = $this->ch;
        if($proxy){
            // Check for SOCKS or HTTP Proxy
            if(strtoupper($this->proxyType) == 'SOCKS')
                curl_setopt($ch, CURLOPT_PROXYTYPE, 7);
            else
                curl_setopt($this->ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);

            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
            if($this->verbose)
                print "Using {$this->proxyType} Proxy: {$this->proxy} ";
            if($this->credentials){
                if($this->verbose)
                    print "Credentials: {$this->credentials}";
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->credentials);
            }
            if($this->verbose)
                print "\n";
        // Disable Proxy Support if called with no parameters
        } else {
            if($this->verbose)
                print "Disabling Proxy.\n";
            curl_setopt($ch, CURLOPT_PROXYTYPE, null);
            curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($ch, CURLOPT_PROXY, null);
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, null);
            $this->proxy = null;
            $this->proxyType = 'HTTP';
            $this->credentials = null;
        }

        return $ch;
    }

    /**
     *    getProxy()
     *    
     *        returns an array with the currently set proxy, credentials, and its type.
     *
     * @return array
     */
    public function getProxy()
    {
        return array('proxy' => $this->proxy, 'credentials' => $this->credentials, 'type' => $this->proxyType);
    }


    /**
     *    setCookie($cookie)
     *
     *        sets the cookie file to $cookie and rebuilds the curl handler.
     *        note that if you already have an instance of the curlHandler 
     *        instantiated, you will need to rebuild it via rebuildHandle()
     *        for this to take effect
     *
     * @param string $cookie - The file you want cookies written to
     *
     * @return void
     */

    public function setCookie($cookie)
    {
        $this->cookies = $cookie;
    }


    public function generateCookies()
    {
        return implode(";", $this->cookies);
    }

    /**
     *    getCookie()
     *    
     *        returns the name of the current file where cookies are stored
     *
     * @return string
     */
    public function getCookie()
    {
        return $this->cookies;
    }

    /**
     *      downloadImage($url, $outfile, $ref)
     *
     *          Will download an image from specified URL and save it to $outfile.
     *
     * @param string $url - The URL of the image
     * @param string $outfile - The location to write the image to
     * @param string $ref - The value to use as a referer in the request (default: $url)
     *
     * @return void
     */

    public function downloadImage($url, $outfile = "image.jpg", $ref = NULL)
    {

        if(!$ref)
            $ref = $url;
        $this->delHeader("Referer");
        $this->addHeader("Referer: $ref");
        $fp = fopen($outfile, "wb");
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, array($this, "cookieSnatcher"));
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_FILE, $fp);
        curl_setopt($this->ch, CURLOPT_HEADER, FALSE);
        curl_exec($this->ch);
        curl_setopt($this->ch, CURLOPT_HEADER, TRUE);
        #curl_setopt($this->ch, CURLOPT_FILE, NULL);
        fclose($fp);
        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        if($errno)
            die("$errno: $err\n");

        $this->rebuildHandle();
        $this->delHeader("Referer");
    }

    /**
     *    randomAgent()
     *    
     *        returns a useragent at random to one from the list below
     *            
     *    List of user-agents from: https://techblog.willshouse.com/2012/01/03/most-common-user-agents/
     *
     * @return string
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

    /**
     *    pushURL($url, $pData)
     *
     *        Adds a URL to $this->urls stack. If it is a POST request, 
     *        also send an array of the POST parameters
     *
     * @param string $url - The URL to add to the queue.
     * @param array $pData - Array of the POST data (only required for POST requests) (default: null)
     *
     * @return void
     */
    public function pushURL($url, $pData = null)
    {
        if($this->verbose)
            print "Pushing $url onto list\n";
        array_push($this->urls, array($url, $pData));
    }

    /**
     *    popURL()
     *
     *        returns the top URL from the $this->urls stack or null
     *        on error. Removes that item from the array. Returns null
     *        if the list is empty.
     *
     * @return string
     */
    public function popURL()
    {
        if($this->urlCount() > 0){
            $url = array_pop($this->urls);
            if($this->verbose)
                print "Popping " . $url[0] . " from list\n";
            return $url;
        }
        if($this->verbose)
            print "No URLs to pop.\n";
        
        return null;
    }

    /**
     *    peekURL()
     *
     *        returns the top URL from the $this->urls stack or null
     *        on error
     *
     * @return string
     */
    public function peekURL()
    {
        if($this->urlCount() > 0)
            return end($this->urls);
        if($this->verbose)
            print "No URLs to peek.\n";

        return null;
    }

    /**
     *    urlCount()
     *
     *        returns the current number of URLs in the $this->urls stack.
     *
     * @return int
     */
    public function urlCount()
    {
        return count($this->urls);
    }

    /**
     *    setupCURL()
     *    
     *        Creates and returns a new generic cURL handle
     *
     * @return object
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
        //curl_setopt($ch, CURLOPT_COOKIEFILE, NULL);

        return $ch;
    }

    /**
     *    requestGET($url, $ref)
     *    
     *        makes a GET based HTTP Request to the url specified in $url using the referer specified in $ref
     *        if no $ref is specified it will use the $url
     *
     * @param string $url - The URL to request (default: null)
     * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
     *
     * @return string
     */

    public function requestGET($url = null, $ref='')
    {
        if($url == null)
            if($this->urlCount() > 0){

                $url = $this->popURL();
                $url = $url[0];
            } else {
                if($this->verbose)
                    print "No URLs currently in stack\n";
                return 0;
            }
            
        if($ref == '')
            $ref = $url;

        $this->delHeader("Referer");
        $this->addHeader("Referer: $ref");

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_POST, 0);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        var_dump($this->headers);
        $x = curl_exec($this->ch);

        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        if($errno)
            die("$errno: $err\n");

        $this->delHeader("Referer");
        
        return new response($x);
    }


    /**
     *    requestPOST($url, $pData, $ref)
     *
     *        makes a POST based HTTP Request to the url specified in $url using the referer specified in $ref
     *        and the parameters specified in $pData. If no $ref is specified it will use the $url
     *
     * @param string $purl - The URL to request (default: null)
     * @param string $pData - The POST parameters to send, this string should have been returned from $this->generatePOSTData()
     * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
     *
     * @return string
     */
    public function requestPOST($purl = null, $pData, $ref='')
    {
        print "test\n";
        if($purl == null)
            if($this->urlCount() > 0)
                $purl = $this->popURL();
            else {
                if($this->verbose)
                    print "No URLs currently in stack\n";
                return 0;
            }
        if($ref == '')
            $ref = $purl;
        
        $this->delHeader("Referer");
        $this->addHeader("Referer: $ref");
                        
        curl_setopt($this->ch, CURLOPT_URL, $purl);
        curl_setopt($this->ch, CURLOPT_POST, 1);
        var_dump($pData);
        var_dump($this->headers);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $pData);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($this->ch, CURLOPT_POSTREDIR, 3);
        $x = curl_exec($this->ch);

        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        print "Right before die son\n";
        if($errno)
            die("$errno: $err\n");

        curl_setopt($this->ch, CURLOPT_POST, 0);
        $this->delHeader("Referer");

        return new response($x);
    }

    /**
     *    requestPUT($url, $ref, $pData)
     *
     *        Makes a PUT based HTTP request to the url and POST data specified.
     *
     * @param string $url - The URL to send the request to
     * @param string $ref - The Referer to use in the request
     * @param string $pData - The POST data to send in the request
     *
     * @return string
     */
    public function requestPUT($url=null, $ref = '', $pData)
    {
    	if(!$url)
    		if($this->urlCount() > 0)
    			$url = $this->popURL();
    		else{
    			if($this->verbose)
    				print "No URLs currently in stack\n";

    			return 0;
    		}
    	if($ref == '')
    		$ref = $url;

    	$fh = tmpfile();
    	fwrite($fh, $pData);
    	fseek($fh, 0);

    	$this->delHeader("Referer");
    	$this->addHeader("Referer: $ref");
   		$fh = fopen($file, 'r');
   		$fileContents = file_get_contents($file);
    	curl_setopt($this->ch, CURLOPT_URL, $url);
    	curl_setopt($this->ch, CURLOPT_PUT, true);
        curl_setopt($this->ch, CURLOPT_INFILE, $fh);
        curl_setopt($this->ch, CURLOPT_INFILESIZE, strlen($pData));
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->headers);

        $x = curl_exec($this->ch);

        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        if($errno)
            die("$errno: $err\n");

        $this->ch = $this->rebuildHandle();
        $this->delHeader("Referer");

        return new response($x);
    }

    /**
     *    requestHTTP($type, $url, $ref, $pData)
     *
     *        simple wrapper method for requestGET, requestPUT and requestPOST. Returns null on error
     *
     * @param string $type - The type of request to make(GET or POST) (default: 'GET')
     * @param string $url - The URL to request (default: null)
     * @param string $ref - The Referer to use for the request(default is to set the $url value) (default: '')
     * @param string $pData - The POST parameters to send, this string should have been returned from $this->generatePOSTData() (default: null)
     *
     * @return string
     */
    public function requestHTTP($type = "GET", $url = null, $ref = '', $pData = null)
    {
        switch($type){
            case "GET":
                return $this->requestGET($url, $ref);
            case "POST":
                return $this->requestPOST($url, $pData, $ref);
            case "PUT":
            	return $this->requestPUT($url, $ref, $pData);
            default:
                print "Invalid Request type submitted.\n";
                return null;    
        }
    }

    /**
     *    curlMultiRequest($nodes)
     *
     *        Accepts an array of URLs to scrape, each element in the array is a sub-array.
     *        For GET requests the sub-array needs only one element, the URL. For POST requests
     *        the subarray should have a second element which is yet another array containing
     *        POST parameters to be sent.
     *
     * @param array $nodes - Contains an array of arrays, each subarray contains at least one URL and an optional set of POST parameters to send (default: $this->urls)
     *
     * @return array
     */
    function curlMultiRequest($nodes = null)
    { 
        $proxy = $this->getProxy();
            
        if($nodes != null)
            $this->urls = $nodes;
        else
            $nodes = array_reverse($this->urls);

        $mh = curl_multi_init();

        $curlArray = array(); 
        $counter = $this->urlCount();
        for($i = 0; $i < $counter; $i++){ 
            $url = $this->popURL();
            $curlArray[$i] = $this->setupCURL();
            $this->delHeader("Referer");
            $this->addHeader("Referer: " . $url[0]);
                
            $curlArray[$i] = $this->setProxy($proxy['proxy'], $proxy['type'], $proxy['credentials'], $curlArray[$i]);

            curl_setopt($curlArray[$i], CURLOPT_URL, $url[0]);
            curl_setopt($curlArray[$i], CURLOPT_RETURNTRANSFER,1);
            curl_setopt($curlArray[$i], CURLOPT_HTTPHEADER, $this->headers);
            curl_setopt($curlArray[$i], CURLOPT_POST, 0);
            if(array_key_exists(1, $url) && $url[1] != null){
                curl_setopt($curlArray[$i], CURLOPT_POST, 1);
                curl_setopt($curlArray[$i], CURLOPT_POSTFIELDS, $this->generatePOSTData($url[1]));
            } 
            curl_multi_add_handle($mh, $curlArray[$i]); 
            $this->delHeader("Referer");
        } 
        $active = null; 
        do{ 
            $mrc = curl_multi_exec($mh, $active); 
        } while($mrc == CURLM_CALL_MULTI_PERFORM); 
            
        while($active && $mrc == CURLM_OK){
            do{
                $mrc = curl_multi_exec($mh, $active);
            } while ($mrc == CURLM_CALL_MULTI_PERFORM);
        }
        if ($mrc != CURLM_OK)
              trigger_error("Curl multi read error $mrc\n", E_USER_WARNING);
        
        $res = array(); 
        foreach($nodes as $i => $url){

            $curlError = curl_error($curlArray[$i]);
              if($curlError == "")
                $res[$url[0]] = curl_multi_getcontent($curlArray[$i]); 
            else
                if($this->verbose)
                    print "Curl error on handle $url: $curlError\n";
            curl_multi_remove_handle($mh, $curlArray[$i]); 
        }
            
        curl_multi_close($mh);        
        $this->urls = array();
        
        return $res; 
    } 

    /**
     *    generatePOSTData($data)
     *
     *        generates a urlencoded string from an associative array of POST parameters
     *
     * @param array $data - An array of POST parameters in array($key => $val, ...) format
     *
     * @return string
     */
    public function generatePOSTData($data)
    {
        $params = '';
        foreach($data as $key => $val)
            $params .= urlencode($key) . '=' . urlencode($val) . '&';
            
        // trim trailing &
        return substr($params, 0, -1);
    }

    /**
     *    rebuildHandle()
     *    
     *        rebuilds the cURL Handler for the next request
     *
     * @return void
     */
    public function rebuildHandle()
    {
    	curl_close($this->ch);
        $this->ch = $this->setupCURL();
        $this->ch = $this->setProxy($this->proxy, $this->credentials, $this->proxyType);
    }

    // Parsing subroutines adapted from Mike Schrenks LIB_PARSE.php in Webbots spiders and screenscrapers http://webbotsspidersscreenscrapers.com/
    
    /**
     *    splitString($string, $delineator, $desired, $type)
     *
     *        Returns the portion of a string either before or after a delineator. The returned string may or may not include the delineator.
     *
     * @param string $string - Input string to parse
     * @param string $delineator - Delineation point (place where split occurs)
     * @param bool $desired - true: include portion before delineator
     *                      - false: include portion after delineator
     * @param bool $type - true: include delineator in parsed string
     *                   - false: exclude delineator in parsed string
     *
     * @return string
     */
    public function splitString($string, $delineator, $desired, $type)
    {
        // Case insensitive parse, convert string and delineator to lower case
        $lc_str = strtolower($string);
        $marker = strtolower($delineator);
        // Return text true the delineator
        if($desired == true){
            if($type == true) // Return text ESCL of the delineator
                $split_here = strpos($lc_str, $marker);
            else // Return text false of the delineator
                $split_here = strpos($lc_str, $marker)+strlen($marker);
            $parsed_string = substr($string, 0, $split_here);
        // Return text false the delineator
        } else {
            if($type==true) // Return text ESCL of the delineator
                $split_here = strpos($lc_str, $marker) + strlen($marker);
            else // Return text false of the delineator
                $split_here = strpos($lc_str, $marker) ;

            $parsed_string = substr($string, $split_here, strlen($string));
        }
        
        return $parsed_string;
    }

    /**
     *    returnBetween($string, $start, $stop, $type)
     *
     *        Returns a substring of $string delineated by $start and $stop The parse is not case sensitive, but the case of the parsed string is not effected.     
     *    
     * @param string $string - Input string to parse
     * @param string $start - Defines the beginning of the substring
     * @param string $stop - Defines the end of the substring
     * @param bool $type - true: exclude delineators in parsed string
     *                      - false: include delineators in parsed string
     *
     * @return string
     */
    public function returnBetween($string, $start, $stop, $type)
    {
        $temp = $this->splitString($string, $start, false, $type);
        
        return $this->splitString($temp, $stop, true, $type);
    }

    /**
     *    parseArray($string, $begTag, $closeTag)
     *
     *        Returns an array of strings that exists repeatedly in $string. This function is usful for returning an array that contains links, images, tables or any other data that appears more than once.        
     *
     * @param string $string - String that contains the tags
     * @param string $begTag - Name of the open tag (i.e. "<a>")
     * @param string $closeTag - Name of the closing tag (i.e. "</title>")
     *
     * @return array
     */
    public function parseArray($string, $begTag, $closeTag)
    {
        preg_match_all("($begTag(.*)$closeTag)siU", $string, $matchingData);
        
        return $matchingData[0];
    }

    /**
     *    getAttribute($tag, $attribute)
     *    
     *        Returns the value of an attribute in a given tag.
     *
     * @param string $tag - The tag that contains the attribute
     * @param string $attribute - The attribute, whose value you seek
     *
     * @return string
     */
    public function getAttribute($tag, $attribute)
    {
        // Use Tidy library to 'clean' input
        $cleanedHTML = $this->tidyHTML($tag);
        // Remove all line feeds from the string
        $cleanedHTML = str_replace(array("\r\n", "\n", "\r"), "", $cleanedHTML);
        
        // Use return_between() to find the properly quoted value for the attribute
        return $this->return_between($cleanedHTML, strtoupper($attribute)."=\"", "\"", true);
    }

    /**
     *    remove($string, $openTag, $closeTag)
     *
     *        Removes all text between $openTag and $closeTag
     *
     * @param string $string - The target of your parse
     * @param string $openTag - The starting delimitor
     * @param string $closeTag - The ending delimitor
     *
     * @return string
     */
    public function remove($string, $openTag, $closeTag)
    {
        # Get array of things that should be removed from the input string
        $removeArray = $this->parseArray($string, $openTag, $closeTag);
            
        # Remove each occurrence of each array element from string;
        for($xx=0; $xx<count($removeArray); $xx++)
            $string = str_replace($removeArray, "", $string);
            
        return $string;
    }

    /**
     *    tidyHTML($inputString)
     *    
     *        Returns a "Cleans-up" (parsable) version raw HTML
     *
     * @param string $inputString - raw HTML
     *
     * @return string
     */
    public function tidyHTML($inputString)
    {
        // Detect if Tidy is in configured
        if(function_exists('tidy_get_release')){
            # Tidy for PHP version 4
            if(substr(phpversion(), 0, 1) == 4){
                tidy_setopt('uppercase-attributes', TRUE);
                tidy_setopt('wrap', 800);
                tidy_parse_string($inputString);            
                $cleanedHTML = tidy_get_output();  
            }
            # Tidy for PHP version 5
            if(substr(phpversion(), 0, 1) >= 5){
                $config = array(
                                'uppercase-attributes' => true,
                                'wrap'                 => 800);
                $tidy = new tidy;
                $tidy->parseString($inputString, $config, 'utf8');
                $tidy->cleanRepair();
                $cleanedHTML  = tidy_get_output($tidy);  
            }
        } else {
            # Tidy not configured for this computer
            $cleanedHTML = $inputString;
        }

        return $cleanedHTML;
    }

    /**
     *    validateURL($url)
     *
     *        Uses regular expressions to check for the validity of a URL
     *
     * @param string $url - The URL to validated
     *
     * @return int
     */
    public function validateURL($url)
    {
        $pattern = '/^(([\w]+:)?\/\/)?(([\d\w]|%[a-fA-f\d]{2,2})+(:([\d\w]|%[a-fA-f\d]{2,2})+)?@)?([\d\w]'
        .'[-\d\w]{0,253}[\d\w]\.)+[\w]{2,4}(:[\d]+)?(\/([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)*(\?(&?([-+_~.\d\w]'
        .'|%[a-fA-f\d]{2,2})=?)*)?(#([-+_~.\d\w]|%[a-fA-f\d]{2,2})*)?$/';
        return preg_match($pattern, $url);
    }

    /**
     *      cookieSnatcher($ch, $headerLine)
     *
     *          Read through cookies sent and parse them how you see fit
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
        //    $this->cookies[] = $page[1];
        //}
        //if (preg_match('/^Set-Cookie:\s*([^;]*)/mi', $headerLine, $cookie) == 1){
        //    $this->cookies[] = $cookie[1];
            //print $cookie[1]."\n";
        //}
        return strlen($headerLine); // Needed by curl
    }

}
