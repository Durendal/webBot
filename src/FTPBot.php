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

namespace Durendal\webBot;

/**
 *        FTPBot is a class for interacting with FTP using cURL and PHP. It should significantly simplify the process
 *        providing several functions to manipulate the curl_setopt() function in various ways.
 *        
 */

class FTPBot
{
    /** @var string $username - The username of the account on the FTP Server */
    private $username;
    /** @var string $password - The password of the account on the FTP Server */
    private $password;
    /** @var string $host - The address of the FTP Server */
    private $host;
    /** @var int $port - The port to connect to */
    private $port;
    /** @var array $files - The list of files to upload or download */
    private $files;
    /** @var object $ch - The cURL handle to use */
    private $ch;

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
        curl_setopt($ch, CURLOPT_URL, "ftp://".$this->host."/");
        curl_setopt($ch, CURLOPT_USERPWD, $this->username.":".$this->password);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FTP_SSL, CURLFTPSSL_ALL);
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
     * @return void
     */
    public function upload($uploadTo, $filePath = null)
    {
    	if(!$filePath)
    		if(count($this->files) > 0)
    			$filePath = $this->popFile();
    		else
    			return null;
    	$file = fopen($filePath, 'r');
    	curl_setopt($this->ch, CURLOPT_URL, "ftp://".$this->host."/".$uploadTo);
        curl_setopt($this->ch, CURLOPT_UPLOAD, true);
        curl_setopt($this->ch, CURLOPT_INFILE, $file);
        curl_setopt($this->ch, CURLOPT_INFILESIZE, filesize($filePath));
        curl_exec($this->ch);
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
     * @return void
     */
    public function download($downloadTo, $filePath = null)
    {
    	if(!$filePath)
    		if(count($this->files) > 0)
    			$filePath = $this->popFile();
    		else
    			return null;
        $file = fopen($downloadTo);
        curl_setopt($this->ch, CURLOPT_URL, "ftp://".$this->host."/".$filePath);
        curl_setopt($this->ch, CURLOPT_FILE, $file);
        curl_exec($this->ch);
        fclose($file);
        $this->rebuildHandle();
    }

    /**
     *    ls($dir)
     *
     *        query server for a directory listing
     *
     * @param string $dir - The directory to request a listing from (default: '')
     * @return string
     */
    public function ls($dir = '')
    {
        curl_setopt($this->ch, CURLOPT_URL, "ftp://".$this->host."/".$dir);
        curl_setopt($this->ch, CURLOPT_FTPLISTONLY, 1);
        $result = curl_exec($this->ch);
        curl_setopt($this->ch, CURLOPT_FTPLISTONLY, 0);

        return $result;
    }

    /**
     *    setCredentials($username, $password)
     *
     *        Set the credentials to use for the FTP server
     *
     * @param string $username - The username to login with
     * @param string $password - The password to login with
     * @return void
     */
    public function setCredentials($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        curl_setopt($this->ch, CURLOPT_USERPWD, $this->username.":".$this->password);
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
     *        Sets the host and port of the remost server
     *
     * @param string $host - The hostname of the FTP server
     * @param int $port - The port of the FTP server (default: 21)
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
     *    addFile($filePath)
     *
     *        Add a file to the list of files to upload/download
     *
     * @param string $filePath - The path to the file to add
     * @return void
     */
    public function addFile($filePath)
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
    	if(count($this->files) > 0)
            return array_pop($this->files);

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
    	$this->ch = $this->setupCURL();
    }

}