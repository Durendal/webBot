<?php
/**
 *        FTPBot.php - A simple interface using cURL for FTP requests with PHP
 *
 *        FTPBot.php aims to simplify the use of cURL with FTP using php.
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\webBot;

use WebBot\WebBot as webBot;

/**
 *    FTPBot()
 *
 *        FTPBot is a class for interacting with FTP using cURL and PHP. It should significantly simplify the process
 *        providing several functions to manipulate the curl_setopt() function in various ways.
 *
 */

class FTPBot
{

    /**
     * @var string $username - The username of the account on the FTP Server
     * @var string $password - The password of the account on the FTP Server
     * @var string $host - The address of the FTP Server
     * @var int $port - The port to connect to
     * @var array $files - The list of files to upload or download
     * @var object $ch - The cURL handle to use
     * @var string $protocol - The protocol to use (default: ftp)
     * @var int $timeout - The timeout to use for cURL requests
     */

    private $username;
    private $password;
    private $host;
    private $port;
    private $files;
    private $ch;
    private $protocol;
    private $timeout;

    /**
     *    __construct($username, $password, $host, $port)
     *
     *        Initializes a generic curl handle and sets the host and port for the
     *        FTP Server
     *
     * @param string $username - The username to login with
     * @param string $password - The password to login with
     * @param string $host - The Hostname of the FTP server
     * @param int $port - The port of the FTP server (default: 21)
     *
     * @return void
     */
    public function __construct($username, $password, $host, $port = 21)
    {
        $this->username = $username;
        $this->password = $password;
        $this->host = $host;
        $this->port = $port;
        $this->ch = $this->setupCURL();
        $this->files = array();
        $this->protocol = 'ftp://';
        $this->setTimeout(300);
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
        curl_setopt($ch, CURLOPT_URL, $this->protocol . $this->host . "/");
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_ALL);
        curl_setopt($ch, CURLOPT_FTPSSLAUTH, CURLFTPAUTH_DEFAULT);
        curl_setopt($ch, CURLOPT_PORT, $this->port);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        return $ch;
    }

    /**
     *    upload($filePath, $uploadTo)
     *
     *        Uploads a file to a remote server
     *
     * @param string $uploadTo - The path on the remote server that you want to write the file to
     * @param string $filePath - The path to the file you wish to upload
     *
     * @return void
     */
    public function upload($uploadTo, $filePath = null)
    {
        if (!$filePath) {
            if (count($this->files) > 0) {
                $filePath = $this->popFile();
            } else {
                return null;
            }
        }
        $file = fopen($filePath, 'r');
        if (!$file) {
            die("Unable to open $filePath\n");
        }

        curl_setopt($this->ch, CURLOPT_URL, $this->protocol . $this->host . '/' . $uploadTo);
        curl_setopt($this->ch, CURLOPT_UPLOAD, true);
        curl_setopt($this->ch, CURLOPT_INFILE, $file);
        curl_setopt($this->ch, CURLOPT_PORT, $this->port);
        curl_setopt($this->ch, CURLOPT_INFILESIZE, filesize($filePath));
        curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        if ($errno) {
            die("$errno: $err\n");
        }
        fclose($file);
        $this->rebuildHandle();
    }

    /**
     *    download($filePath, $downloadTo)
     *
     *        Downloads a file from a remote server
     *
     * @param string $downloadTo - The path on the local server that you want to write the file to
     * @param string $filePath - The path to the file you wish to upload
     *
     * @return void
     */
    public function download($downloadTo, $filePath = null)
    {
        if (!$filePath) {
            if (count($this->files) > 0) {
                $filePath = $this->popFile();
            } else {
                return null;
            }
        }
        $file = fopen($downloadTo, 'w+');

        $filePath = str_replace("+", "%20", str_replace(array("%2F", "%2f"), "/", urlencode($filePath)));

        curl_setopt($this->ch, CURLOPT_URL, $this->protocol . $this->host . "/" . $filePath);
        curl_setopt($this->ch, CURLOPT_FILE, $file);
        curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);
        if ($errno) {
            die("$errno: $err\n");
        }
        fclose($file);
        $this->rebuildHandle();
    }

    /**
     *    ls($dir)
     *
     *        query server for a directory listing
     *
     * @param string $dir - The directory to request a listing from (default: '')
     *
     * @return string
     */
    public function ls($dir = '')
    {
        // Ensure we have appropriate trailing slashes
        if (substr($this->host, -1) != '/') {
            $this->host .= '/';
        }
        $url = $this->protocol . $this->host . (($dir == '') ? '' : ((substr($dir, -1) == '/') ? "$dir" : "$dir/"));

        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_FTPLISTONLY, 1);

        $result = curl_exec($this->ch);
        $errno = curl_errno($this->ch);
        $err = curl_error($this->ch);

        if ($errno) {
            die("$errno: $err\n");
        }
        $this->rebuildHandle();

        return $result;
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
        if (!$ch) {
            $ch = $this->ch;
        }

        if ($verify) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            if ($hostval >= 0 && $hostval < 3 && $certfile != '') {
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
     *    setCredentials($username, $password)
     *
     *        Set the credentials to use for the FTP server
     *
     * @param string $username - The username to login with
     * @param string $password - The password to login with
     *
     * @return void
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        curl_setopt($this->ch, CURLOPT_USERPWD, $this->username . ":" . $this->password);
    }

    /**
     *    getCredentials()
     *
     *        Returns an array with the currently set credentials
     *
     * @return array
     */
    public function getCredentials()
    {
        return array($this->username, $this->password);
    }

    /**
     *    setHost($host, $port)
     *
     *        Sets the host and port of the remote server
     *
     * @param string $host - The hostname of the FTP server
     * @param int $port - The port of the FTP server (default: 21)
     *
     * @return void
     */
    public function setHost($host, $port = 21)
    {
        $this->host = $host;
        $this->port = $port;
    }

    /**
     *    getHost()
     *
     *        Return an array of the Host and Port
     *
     * @return array
     */
    public function getHost()
    {
        return array($this->host, $this->port);
    }

    /**
     *    pushFile($filePath)
     *
     *        Add a file to the list of files to upload/download
     *
     * @param string $filePath - The path to the file to add
     *
     * @return void
     */
    public function pushFile($filePath)
    {
        array_push($this->files, $filePath);
    }

    /**
     *    popFile()
     *
     *        Pop a file from the list and return its path
     *        Returns null if the list is empty.
     *
     * @return string
     */
    public function popFile()
    {
        if (count($this->files) > 0) {
            return array_pop($this->files);
        }

        return null;
    }

    /**
     *    rebuildHandle()
     *
     *        Rebuilds a generic cURL handle
     *
     * @return void
     */
    public function rebuildHandle()
    {
        curl_close($this->ch);
        $this->ch = $this->setupCURL();
    }

    /**
     *    setProtocol($protocol)
     *
     *        Set the protocol to use e.g.: ftp, sftp, ftps, tftp
     *        Returns 1 on success or 0 on failure.
     *
     * @param string $protocol - The protocol to use in calls
     *
     * @return int
     */

    public function setProtocol($protocol = 'ftp://')
    {
        if (!stristr($protocol, "://")) {
            $protocol .= "://";
        }
        switch ($protocol) {
            case "ftp://":
            case "sftp://":
            case "ftps://":
            case "tftp://":
                $this->protocol = $protocol;
                return 1;
            default:
                return 0;
        }
    }

    /**
     *    getProtocol()
     *
     *        Returns the currently set protocol
     *
     * @return string
     */

    public function getProtocol()
    {
        return $this->protocol;
    }

    /**
     *    setTimeout($timeout)
     *
     *        Sets the timeout to $timeout as long as its a positive integer
     *
     * @return void
     */

    public function setTimeout($timeout)
    {
        if ($timeout > 0) {
            $this->timeout = $timeout;
            curl_setopt($this->ch, CURLOPT_TIMEOUT, $this->timeout);
        }
    }

    /**
     *    getTimeout()
     *
     *        Returns the currently set timeout
     *
     * @return int
     */

    public function getTimeout()
    {
        return $this->timeout;
    }
}
