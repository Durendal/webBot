<?php
/**
 *		Data.php - A base class for RequestData and Query to inherit from
 *
 * @author Durendal
 * @license GPL
 * @link https://github.com/Durendal/webBot
 */

namespace Durendal\webBot;

use Durendal\webBot as webBot;

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
		if(validate_data($data))
			$this->data = $data;
		$this->encode();
	}

	public function addData($data) {
		$this->setData(array_merge($this->data, $data));
	}

	public function encode() {
		$this->encodedData = '';
		if(count($this->data) > 0) {
			$this->encodedData .= '?';
			foreach($this->data as $key => $value) 
				$this->encodedData .= rawurlencode($key) ."=" . rawurlencode($value) . "&";
			$this->encodedData = substr($this->encodedData, 0, -1);
		}
	}

	public function get() {
		return $data;
	}

	public function getEncoded() {
		return $this->encodedData;
	}
}

// Wrappers for Data with more meaningful names

class RequestData extends Data {}

class RequestQuery extends Data {}

?>