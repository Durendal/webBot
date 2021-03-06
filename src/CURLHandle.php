<?php
/**
 *      cURLHandle.php - An object to represent a cURL handle on HTTPBot
 *
 *      This class helps constructing cURL Handles
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */
namespace WebBot\WebBot;

use WebBot\WebBot\Cookies\Cookies;
use WebBot\WebBot\Headers\Headers;
use WebBot\WebBot\Proxys\Proxy;
use WebBot\WebBot\Data\RequestQuery;
use WebBot\WebBot\Data\RequestData;
use WebBot\WebBot\Messages\Response;
use WebBot\WebBot\Exceptions\{CookiesException, CURLHandleException};

class CURLHandle
{

    /**
     * @var object $handle - The cURL handle
     * @var object $cookies - The Cookies object to use for this handle
     * @var object $headers - The Headers object to use for this handle
     * @var object $proxy - The Proxy object to use for this handle
     * @var object $query - The RequestQuery object to use for this handle
     * @var object $data - The RequestData object to use for this handle
     */

    private $handle;
    private $cookies;
    private $headers;
    private $proxy;
    private $query;
    private $data;

    /**
     *   __construct($proxy=null, $cookies = null, $headers=null)
     *
     *     Constructs a cURL handle object using any headers, cookies, and proxy settings
     *     submitted.
     *
     * @param object $headers - The headers object to use for the handle
     * @param object $cookies - The cookies object to use for the handle
     * @param object $proxy - The proxy object to use for the handle
     *
     * @return void
     */
    public function __construct(
        Proxy $proxy = null,
        Cookies $cookies = null,
        Headers $headers = null,
        RequestQuery $query = null,
        RequestData $data = null
    ) {
        $this->handle = $this->setupCURL();
        $this->setProxy($proxy);
        $this->setHeaders($headers);
        $this->setCookies($cookies);
        $this->setQuery($query);
        $this->setData($data);
    }

    public function __destruct()
    {
        if ($this->handle) {
            curl_close($this->handle);
            unset($this->handle);
        }
    }

    /**
     *  __toString()
     *
     *      Returns a printable string representation of the cURLHandle object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("<cURL Handle - >");
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
    public function setCookies(Cookies $cookies = null)
    {
        $this->cookies = (is_a($cookies, "Cookies")) ? $cookies : new Cookies();
        $this->initCookies();
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
    public function setHeaders(Headers $headers = null)
    {
        $this->headers = (is_a($headers, "Headers")) ? $headers : new Headers();
        $this->initHeaders();
    }

    public function addHeader(string $key, string $value)
    {
        $this->headers->addHeader($key, $value);
        $this->initHeaders();
    }

    /**
     *   setProxy($proxy)
     *
     *     Checks if $proxy is a valid Proxy object, if so it is assigned to
     *     $this->proxy, otherwise a fresh Proxy object is created.
     *
     * @param object $proxy - The Proxy object to use for this handle
     *
     * @return void
     */
    public function setProxy(Proxy $proxy = null)
    {
        $this->proxy = (is_a($proxy, "Proxy")) ? $proxy : new Proxy();
        $this->initProxy();
    }

    /**
     *   setQuery($query)
     *
     * @param object $query - The RequestQuery object to use for this handle
     *
     * @return void
     */
    public function setQuery(RequestQuery $query = null)
    {
        $this->query = (is_a($query, "RequestQuery")) ? $query : new RequestQuery();
    }

    /**
     *   setData($data)
     *
     * @param object $data - The RequestData object to use for this handle
     *
     * @return void
     */
    public function setData(RequestData $data = null)
    {
        $this->data = (is_a($data, "RequestData")) ? $data : new RequestData();
    }

    /**
     *  initProxy($this->handle)
     *
     *      Initialize the proxy settings on $this->handle
     *
     * @return void
     */
    public function initProxy()
    {
        extract($this->proxy->getProxy());
        curl_setopt($this->handle, CURLOPT_PROXYTYPE, $type);
        curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, null);

        // Check for valid proxy type
        if ($type === null) {
            curl_setopt($this->handle, CURLOPT_HTTPPROXYTUNNEL, 0);
            curl_setopt($this->handle, CURLOPT_PROXY, null);
            curl_setopt($this->handle, CURLOPT_PROXYPORT, null);
        } else {
            curl_setopt($this->handle, CURLOPT_HTTPPROXYTUNNEL, 1);
            curl_setopt($this->handle, CURLOPT_PROXY, $host);
            curl_setopt($this->handle, CURLOPT_PROXYPORT, $port);

            if ($credentials) {
                curl_setopt($this->handle, CURLOPT_PROXYUSERPWD, $credentials);
            }
        }
    }

    /**
     *  initCookies()
     *
     *      Initialize the cookies settings on $this->handle, additionally an array of
     *      cookies in key => value format can be submitted and added to the
     *      handle.
     *
     *
     * @return void
     */
    public function initCookies()
    {
        curl_setopt($this->handle, CURLINFO_HEADER_OUT, true);

        // Set cookie jar
        curl_setopt(
            $this->handle,
            CURLOPT_COOKIEJAR,
            $this->cookies->getCookieJar()
        );
        curl_setopt(
            $this->handle,
            CURLOPT_COOKIEFILE,
            $this->cookies->getCookieJar()
        );

        // Clear Cookies in cookie jar
        curl_setopt($this->handle, CURLOPT_COOKIELIST, "SESS");

        // Populate Cookiejar with any custom submitted cookies
        foreach ($this->cookies->getCookies() as $key => $value) {
            $this->setCookie($key, implode(" ", $value));
        }

        // Write cookies to cookie jar
        curl_setopt($this->handle, CURLOPT_COOKIELIST, "FLUSH");

        $this->getCookies();
    }

        /**
     *  initHeaders()
     *
     *      Initialize the headers settings on the curl handle
     *
     * @return void
     */
    public function initHeaders()
    {
        curl_setopt(
            $this->handle,
            CURLOPT_HTTPHEADER,
            $this->headers->getHeaders()
        );
        curl_setopt($this->handle, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->handle, CURLOPT_HEADER, 1);
    }

    /**
     *  setCookie($cookie)
     *
     *      Adds a new cookie
     *
     * @param string $key - The name for the cookie being added
     * @param string $value - The value of the cookie being added
     *
     * @return void
     */
    public function setCookie(string $key, string $value)
    {
        if ($this->handle == null) {
            throw new CookieException("Must Initialize Cookie Object before setting cookies.");
        }

        curl_setopt(
            $this->handle,
            CURLOPT_COOKIELIST,
            sprintf("%s=%s", $key, $value)
        );
        curl_setopt($this->handle, CURLOPT_COOKIELIST, "FLUSH");
        $this->cookies->setCookie($key, $value);
        $this->getCookies();
    }

    /**
     *    generateCookies()
     *
     *      Returns a string built from an array of Cookies
     *
     * @return string - A string containing the currently set cookies
     */
    public function generateCookies(string $host)
    {
        if ($this->handle == null) {
            throw new CookieException("Must Initialize Cookie Object before setting cookies.");
        }
        $this->getCookies(); // Update cookie object
        $cookieStr = "";
        $cookies = $this->cookies->getCookies();
        foreach ($cookies[$host] as $val) {
            $cookieStr .= sprintf("%s=%s; ", $val['name'], $val['value']);
        }

        return substr($cookieStr, 0, strlen($cookieStr) - 1);
    }

    /**
     *  getCookies()
     *
     *      updates $this->cookies object and returns the currently set cookies
     *
     * @return array
     */
    public function getCookies()
    {
        if ($this->handle == null) {
            throw new CookieException("Must Initialize Cookie Object before setting cookies.");
        }

        $cookies = curl_getinfo($this->handle, CURLINFO_COOKIELIST);

        foreach ($cookies as $key => $val) {
            $val = explode("\t", $val);
            if (count($val) == 7) {
                $this->cookies->setCookie(
                    $val[0],
                    array(
                        'flag' => $val[1],
                        'path' => $val[2],
                        'secure' => $val[3],
                        'expiration' => $val[4],
                        'name' => $val[5],
                        'value' => $val[6]
                    )
                );
            }
            unset($cookies[$key]);
        }

        return $this->cookies->getCookies();
    }

    /**
     *   getHandle()
     *
     *     Returns the currently set cURL Handle
     *
     * @return cURL $this->handle - The currently set cURL handle
     */
    public function getHandle()
    {
        return $this->handle;
    }

    public function getHeaders()
    {
        return $this->headers->getHeaders();
    }

    public function getProxy()
    {
        return $this->proxy->getProxy();
    }
    /**
     *  setSSL($verify, $hostval, $certfile)
     *
     *      Allows the user to adjust SSL settings on a cURL handle directly, If verify is set to true
     *      then the following $hostval and $certfile parameters are required, otherwise
     *      they can be ommitted.
     *
     * @param bool $verify - Whether or not to verify SSL Certificates (default: false)
     * @param int $hostval - Set the level of verification required: (default: 0)
     *                      - 0: Don’t check the common name (CN) attribute
     *                      - 1: Check that the common name attribute at least exists
     *                      - 2: Check that the common name exists and that it matches the host name of the server
     * @param string $certfile - The location of the certificate file you wish to use (default: '')
     *
     * @return object
     */
    public function setSSL(
        bool $verify = false,
        int $hostval = 0,
        string $certfile = ''
    ) {
        if ($verify) {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, true);
            if ($hostval >= 0 && $hostval < 3 && $certfile != '') {
                curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, $hostval);
                curl_setopt($this->handle, CURLOPT_CAINFO, $certfile);
            }
        } else {
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($this->handle, CURLOPT_SSL_VERIFYHOST, false);
        }

        return $this->handle;
    }

    /**
     *    downloadImage($url, $outfile, $ref)
     *
     *        Will download an image from specified URL and save it to $outfile.
     *
     * @param string $url - The URL of the image
     * @param string $outfile - The location to write the image to
     * @param string $ref - The value to use as a referer in the request (default: $url)
     *
     * @return void
     */
    public function binaryDownload(string $url, string $outfile = "image.jpg")
    {
        $fp = fopen($outfile, "wb");

        curl_setopt(
            $this->handle,
            CURLOPT_HEADERFUNCTION,
            array($this, "cookieSnatcher")
        );
        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_FILE, $fp);
        curl_setopt($this->handle, CURLOPT_HEADER, false);
        curl_exec($this->handle);
        curl_setopt($this->handle, CURLOPT_HEADER, true);
        #curl_setopt($this->handle, CURLOPT_FILE, null);
        fclose($fp);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);

        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        $this->rebuildHandle();
        $this->headers->delHeader("Referer");
    }

    /**
     *  setupCURL()
     *
     *      Creates and returns a new generic cURL handle
     *
     * @return object
     */
    private function setupCURL()
    {

        $this->handle = curl_init();
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_FOLLOWLOCATION, 1);

        return $this->handle;
    }

    /**
     *  rebuildHandle()
     *
     *      rebuilds the cURL Handler for the next request
     *
     * @return void
     */
    public function rebuildHandle(
        Cookies $cookies = null,
        Headers $headers = null,
        RequestQuery $query = null,
        RequestData $data = null
    ) {
        curl_close($this->handle);
        $this->handle = $this->setupCURL();
        $this->setProxy($this->proxy);
        $this->setCookies($cookies);
        $this->setQuery($query);
        $this->setData($data);
        $this->setHeaders($headers);
    }

    /**
     *  requestGET($url)
     *
     *      makes a GET based HTTP Request to the url specified in $url using the referer specified in $ref
     *      if no $ref is specified it will use the $url
     *
     * @param string $url - The URL to request (default: null)
     *
     * @return string
     */
    public function get(string $url)
    {
        if (strlen($this->query->getEncoded()) > 0) {
            $url .= '?' . $this->query->getEncoded();
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_POST, 0);

        $raw = curl_exec($this->handle);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);
        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        return new Response($this->handle, $raw);
    }

    /**
     *  requestPOST($url)
     *
     *      makes a POST based HTTP Request to the url specified in $url using the referer specified in $ref
     *      and the parameters specified in $pData. If no $ref is specified it will use the $url
     *
     * @param string $purl - The URL to request (default: null)
     *
     * @return string
     */
    public function post(string $url)
    {

        if (strlen($this->query->getEncoded()) > 0) {
            $url .= '?' . $this->query->getEncoded();
        }

        if (strlen($this->data->getEncoded()) > 0) {
            curl_setopt(
                $this->handle,
                CURLOPT_POSTFIELDS,
                $this->data->getEncoded()
            );
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_POST, 1);
        curl_setopt(
            $this->handle,
            CURLOPT_HTTPHEADER,
            $this->headers->getHeaders()
        );
        curl_setopt($this->handle, CURLOPT_POSTREDIR, 3);
        $raw = curl_exec($this->handle);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);

        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        curl_setopt($this->handle, CURLOPT_POST, 0);

        return new Response($this->handle, $raw);
    }

    /**
     *  put($url)
     *
     *      Makes a PUT based HTTP request to the url and POST data specified.
     *
     * @param string $url - The URL to send the request to
     *
     * @return string
     */
    public function put(string $url)
    {

        if (strlen($this->query->getEncoded()) > 0) {
            $url .= '?' . $this->query->getEncoded();
        }

        if (strlen($this->data->getEncoded()) > 0) {
            curl_setopt(
                $this->handle,
                CURLOPT_POSTFIELDS,
                $this->data->getEncoded()
            );
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_PUT, true);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(
            $this->handle,
            CURLOPT_HTTPHEADER,
            $this->headers->getHeaders()
        );

        $raw = curl_exec($this->handle);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);
        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        return new Response($this->handle, $raw);
    }

    /**
     *  patch($url)
     *
     *      Makes a PATCH based HTTP request to the url and POST data specified.
     *
     * @param string $url - The URL to send the request to
     *
     * @return string
     */
    public function patch(string $url)
    {

        if (strlen($this->query->getEncoded()) > 0) {
            $url .= '?' . $this->query->getEncoded();
        }

        if (strlen($this->data->getEncoded()) > 0) {
            curl_setopt(
                $this->handle,
                CURLOPT_POSTFIELDS,
                $this->data->getEncoded()
            );
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(
            $this->handle,
            CURLOPT_HTTPHEADER,
            $this->headers->getHeaders()
        );

        $raw = curl_exec($this->handle);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);
        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        return new Response($this->handle, $raw);
    }

    /**
     *  delete($url)
     *
     *      Makes a DELETE based HTTP request to the url and POST data specified.
     *
     * @param string $url - The URL to send the request to
     *
     * @return string
     */
    public function delete(string $url)
    {

        if (strlen($this->query->getEncoded()) > 0) {
            $url .= '?' . $this->query->getEncoded();
        }

        if (strlen($this->data->getEncoded()) > 0) {
            curl_setopt(
                $this->handle,
                CURLOPT_POSTFIELDS,
                $this->data->getEncoded()
            );
        }

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->handle, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt(
            $this->handle,
            CURLOPT_HTTPHEADER,
            $this->headers->getHeaders()
        );

        $raw = curl_exec($this->handle);

        $errno = curl_errno($this->handle);
        $err = curl_error($this->handle);
        if ($errno) {
            throw new CURLHandleException("$errorno: $err\n");
        }

        return new Response($this->handle, $raw);
    }

    /**
     *  requestHTTP($type, $url, $ref, $pData)
     *
     *      simple wrapper method for get, post, put, patch, and delete. Returns null on error
     *
     * @param string $method - The type of request to make(GET or POST) (default: 'GET')
     * @param string $url - The URL to request (default: null)
     *
     * @return string
     */
    public function request(string $url, string $method = "GET")
    {
        switch ($method) {
            case "GET":
                return $this->get($url);
            case "POST":
                return $this->post($url);
            case "PUT":
                return $this->put($url);
            case "PATCH":
                return $this->patch($url);
            case "DELETE":
                return $this->delete($url);
            default:
                return null;
        }
    }

    /**
     *    cookieSnatcher($this->handle, $headerLine)
     *
     *        Read through cookies sent and parse them how you see fit
     *
     * @param object $ch - The cURL handle to read from
     * @param string $headerLine - The current line in headers to check
     *
     * @return int
     */
    public function cookieSnatcher(object $ch, string $headerLine)
    {
        //print "============================================================\n";
        //print $headerLine."\n";
        //print "============================================================\n";
        //if(preg_match('/^Location:\s*([^;]*)/mi', $headerLine, $page) == 1){
        //  $this->cookies[] = $page[1];
        //}
        if (preg_match(
            '/^Set-Cookie:\s*([^;]*)/mi',
            $headerLine,
            $cookie
        ) == 1) {
            $this->newCookies[] = $cookie[1];
        }
        //print $cookie[1]."\n";
        //}
        return strlen($headerLine); // Needed by curl
    }
}
