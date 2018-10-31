<?php
/**
 * @package phpunk
 */

class api_error implements collection, JsonSerializable {
	use collectible;

	private $_code;
	private $_message;

	public function __construct($code, $message, $data = []) {
		$this->_code = $code;
		$this->_message = $message;
		$this->loadArray($data);
	}

	public function __get($key) {
		switch ($key) {
			case 'code':
			case 'message':
				return $this->{"_$key"};
		}
	}

	public function jsonSerialize() {
		return [
			'code'    => $this->_code,
			'message' => $this->_message,
			'data'    => $this->toArray()
		];
	}
}
