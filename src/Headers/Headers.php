<?php
/**
 *      Headers.php - An object to represent HTTP headers with webBot
 *
 *      This class helps dissecting the responses from HTTP requests
 *      facilitates easy access to: Status code, headers, and content
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\WebBot\Headers;

class Headers implements Countable
{

    /**
     * @var array headers - Array of headers submitted or returned from a request
     */
    private $headers;

    public function __construct($headers = array())
    {
        $this->headers = array();
        $this->addHeaders($headers);
        $this->defaultHeaders();
    }

    public function count()
    {
        return count($this->headers);
    }

    /**
     *  __toString()
     *
     *      Returns a printable string representation of the Headers object.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf(
            "<HTTP Headers - %d Headers currently set>",
            count($this->headers)
        );
    }

    /**
     *  defaultHeaders()
     *
     *      sets some default headers to use for requests, these can be edited and added to.
     *
     * @return void
     */
    public function defaultHeaders()
    {
        $this->addHeader("Connection", "Keep-alive");
        $this->addHeader("Keep-alive", "300");
        $this->addHeader("User-Agent", $this->randomAgent());
    }

    public function addHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->addHeader($key, $value);
        }
    }

    /**
     *  addHeader($header)
     *
     *      checks if $header already exists in the headers array, if not it adds it.
     *
     * @param string $header - Contains the Header to add
     *
     * @return boolean - Denotes if header was successfully added
     */
    public function addHeader($key, $value)
    {
        if ($this->checkHeader($key)) {
            return false;
        }

        $this->headers[$key] = $value;
        return true;
    }

    /**
     *  checkHeader($header)
     *
     *      checks if $key is a valid key to the $headers array
     *
     * @param string $key - Contains the key of the header to check
     *
     * @return boolean
     */
    public function checkHeader($key)
    {
        return array_key_exists($key, $this->headers);
    }

    /**
     *  delHeader($header)
     *
     *      checks for $header in $this->headers and deletes it if it exists.
     *
     * @param string $header - Contains the Header to delete
     *
     * @return void
     */
    public function delHeader($key)
    {
        if ($this->checkHeader($key)) {
            unset($this->headers[$key]);
        }
    }

    /**
     *  changeHeader($header, $val)
     *
     *      deletes $header if it exists, then adds $val as a header.
     *      $val can contain the header type and value, or just the value.
     *
     * @param string $header - Contains the Header to change
     * @param string $val - The value to change the header to
     *
     * @return void
     */
    public function changeHeader($key, $val)
    {
        if ($this->checkHeader($key)) {
            $this->delHeader($key);
        }
        $this->addHeader($key, $val);
    }

    /**
     *  getHeaders()
     *
     *      returns a list of the currently set headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     *    getHeader($key)
     *
     *      Checks that the header for $key exists, if so the value is returned
     *
     * @param string $key - The key for the header to return
     *
     * @return string $this->headers[$key] - The value of the header requested
     */
    public function getHeader($key)
    {
        if (array_key_exists($key, $this->headers)) {
            return $this->headers[$key];
        }
        return false;
    }

    public function getAgents()
    {
        return array(
          "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:37.0) "
          . "Gecko/20100101 Firefox/37.0",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/600.5.17"
          . " (KHTML, like Gecko) Version/8.0.5 Safari/600.5.17",
          "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/600.4.10"
          . " (KHTML, like Gecko) Version/8.0.4 Safari/600.4.10",
          "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:36.0) Gecko/20100101 "
          . "Firefox/36.0",
          "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:37.0) Gecko/20100101 "
          . "Firefox/37.0",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:37.0) "
          . "Gecko/20100101 Firefox/37.0",
          "Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) "
          . "like Gecko",
          "Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_3) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:37.0) Gecko/20100101"
          . " Firefox/37.0",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_2) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) "
          . "Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.118 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:36.0) "
          . "Gecko/20100101 Firefox/36.0",
          "Mozilla/5.0 (Windows NT 6.3; WOW64; Trident/7.0; rv:11.0) "
          . "like Gecko",
          "Mozilla/5.0 (Windows NT 6.1; rv:37.0) Gecko/20100101 Firefox/37.0",
          "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko)"
          . " Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.9; rv:37.0) "
          . "Gecko/20100101 Firefox/37.0",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:36.0) "
          . "Gecko/20100101 Firefox/36.0",
          "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/42.0.2311.90 Safari/537.36",
          "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) "
          . "Chrome/41.0.2272.101 Safari/537.36",
          "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.101 Safari/537.36",
          "Mozilla/5.0 (iPad; CPU OS 8_2 like Mac OS X) AppleWebKit/600.1.4 "
          . "(KHTML, like Gecko) Version/8.0 Mobile/12D508 Safari/600.1.4",
          "Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:36.0) "
          . "Gecko/20100101 Firefox/36.0",
          "Mozilla/5.0 (iPad; CPU OS 8_3 like Mac OS X) AppleWebKit/600.1.4 "
          . "(KHTML, like Gecko) Version/8.0 Mobile/12F69 Safari/600.1.4",
          "Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko",
          "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_5) AppleWebKit/537.36 "
          . "(KHTML, like Gecko) Chrome/41.0.2272.104 Safari/537.36");
    }

    /**
     *  randomAgent()
     *
     *      returns a useragent at random to one from the list below
     *
     *  List of user-agents from: https://techblog.willshouse.com/2012/01/03/most-common-user-agents/
     *
     * @return string
     */
    public function randomAgent()
    {
        return $this->getAgents()[rand(0, count($this->getAgents()) - 1)];
    }
}
