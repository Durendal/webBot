<?php
/**
 *      Request.php - An object to represent an outgoing request from webBot
 *
 *      This class helps constructing HTTP requests
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */
namespace WebBot\WebBot;

use WebBot\WebBot\Cookies\Cookies;
use WebBot\WebBot\Headers\Headers;
use WebBot\WebBot\Proxys\Proxy;
use WebBot\WebBot\Data\{RequestData, RequestQuery};

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
    private $handle;
    private $method;
    private $proxy;
    private $pData;
    private $query;
    private $response;

    /**
     *   __construct($url, $proxy, $method="GET", $cookies=null, $headers=null)
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
    public function __construct(
        $url,
        $settings = array(
        'proxy' => null,
        'method' => "GET",
        'pData' => null,
        'cookies' => null,
        'headers' => null,
        'ch' => null,
        'query' => null
      )
    ) {
        $proxy = null;
        $method = "GET";
        $pData = null;
        $cookies = null;
        $headers = null;
        $ch = null;
        $query = null;
        extract($settings);
        $this->method = $method;
        $this->setURL($url);
        $this->setHandle($ch, $proxy, $headers, $cookies);
        $this->setProxy($proxy);
        $this->setHeaders($headers);
        $this->setCookies($cookies);
    }

    public function __destruct()
    {
        unset($this->handle);
        unset($this->method);
        unset($this->proxy);
        unset($this->targetURL);
    }

    /**
     *  __toString()
     *
     *      Returns a printable string representation of the Request object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf("<HTTP Request - %s>", $this->targetURL);
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
    public function setProxy($proxy = null)
    {
            $this->handle->setProxy($proxy);
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
    public function setCookies($cookies = null)
    {
        $this->handle->setCookies($cookies);
    }

    public function setData($data, $method = 'POST')
    {
        if (is_a($data, "WebBot\WebBot\RequestData")) {
            $this->handle->setData((strtoupper($method) == "GET") ? null : $data);
        }
    }

    public function setQuery($query, $method)
    {
        if (is_a($query, "WebBot\WebBot\RequestQuery")) {
            $this->handle->setQuery($query);
        }
    }

    /**
     *   getCookies()
     *
     *     Returns the currently set cookie object
     *
     * @return object $cookies - The currently set cookie object
     */
    public function getCookies()
    {
        return $this->handle->getCookies();
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
    public function setHeaders($headers)
    {
        $this->handle->setHeaders($headers);
    }

    public function addHeader($key, $value)
    {
        $this->handle->addHeader($key, $value);
    }

    public function getProxy()
    {
        return $this->handle->getProxy();
    }

    /**
     *   getHeaders()
     *
     *     Returns the currently set Headers object.
     *
     * @return object $this->headers - The currently set headers object
     */
    public function getHeaders()
    {
        return $this->handle->getHeaders();
    }

    public static function getValidTypes()
    {
        return self::$validTypes;
    }

    public function setHandle(
        $ch,
        $proxy = null,
        $headers = null,
        $cookies = null,
        $data = null,
        $query = null
    ) {
        if (!$proxy) {
            $proxy = new Proxy();
        }
        if (!$headers) {
            $headers = new Headers();
        }
        if (!$cookies) {
            $cookies = new Cookies();
        }
        if (!$data) {
            $data = new RequestData();
        }
        if (!$query) {
            $query = new RequestQuery();
        }

        $settings = array(
            'proxy' => $proxy,
            'cookies' => $cookies,
            'headers' => $headers,
            'query' => $query,
            'data' => $data
        );
        $this->handle = (is_a($ch, "webBot\CURLHandle")) ? $ch : new CURLHandle($settings);
        $this->setProxy($proxy);
        $this->setCookies($cookies);
        $this->setHeaders($headers);
    }

    public function getHandle()
    {
        return $this->handle;
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
    public function setURL($url)
    {
        $this->targetURL = $url;
    }

    /**
     *   getURL()
     *
     *     Returns the currently set URL
     *
     * @return string $this->url - The currently set URL
     */
    public function getURL()
    {
        return $this->targetURL;
    }

    /**
     *   run()
     *
     *     Executes the request with its set proxy, header, and cookie settings
     *
     * @return object Response - The response to the HTTP Request
     */
    public function run()
    {
        return $this->handle->request($this->getURL(), $this->method);
    }
}
