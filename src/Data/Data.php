<?php
/**
 *      Data.php - A base class for RequestData and Query to inherit from
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

abstract class Data
{
    private $data;
    private $encodedData;

    public function __construct($data = array())
    {
        $this->setData($data);
    }
    public function validateData($data)
    {
        if (is_array($data)) {
            // Verify array is an associative array
            if (array_keys($data) !== range(0, count($data) - 1)) {
                return true;
            }
        }
        return false;
    }

    public function setData($data)
    {
        if ($this->validateData($data)) {
            $this->data = $data;
            $this->encode();
            return true;
        }
        return false;
    }

    public function addData($data)
    {
        if ($this->validateData($data)) {
            $this->setData(array_merge($this->data, $data));
            return true;
        }
        return false;
    }

    public function encode()
    {
        $this->encodedData = '';
        if (count($this->data) > 0) {
            foreach ($this->data as $key => $value) {
                $this->encodedData .= (
                  rawurlencode($key) . "=" . rawurlencode($value) . "&"
                );
            }
            $this->encodedData = substr($this->encodedData, 0, -1);
        }
        return $this->encodedData;
    }

    public function get()
    {
        return $this->data;
    }

    public function getEncoded()
    {
        return $this->encodedData;
    }
}
