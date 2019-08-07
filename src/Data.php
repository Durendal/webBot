<?php
/**
 *		Data.php - A base class for RequestData and Query to inherit from
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace WebBot\WebBot;

use WebBot\WebBot as webBot;

class Data {
	private $data;
	private $encodedData;

	public function __construct($data=array()) {
		$this->setData($data);
	}
	public function validate_data($data) {
		if(is_array($data)) 
			// Verify array is an associative array
			if(array_keys($data) !== range(0, count($data) - 1))
				return true;
		return false;
	}

	public function setData($data) {
		if($this->validate_data($data))
			$this->data = $data;
		$this->encode();
	}

	public function addData($data) {
		$this->setData(array_merge($this->data, $data));
	}

	public function encode() {
		$this->encodedData = '';
		if(count($this->data) > 0) {
			foreach($this->data as $key => $value) 
				$this->encodedData .= rawurlencode($key) . "=" . rawurlencode($value) . "&";
			$this->encodedData = substr($this->encodedData, 0, -1);
		}
	}

	public function get() {
		return $this->data;
	}

	public function getEncoded() {
		return $this->encodedData;
	}
}

// Wrappers for Data with more meaningful names

class RequestData extends Data {
	/**
	 *	__toString()
	 *
	 *		Returns a printable string representation of the RequestData object.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("<RequestData - {$this->encodedData} >");
	}
}

class RequestQuery extends Data {
	/**
	 *	__toString()
	 *
	 *		Returns a printable string representation of the RequestQuery object.
	 *
	 * @return string
	 */
	public function __toString() {
		return sprintf("<RequestQuery - {$this->encodedData} >");
	}
}

?>