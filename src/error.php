<?php

class api_error implements collectible, JsonSerializable {
	use collection;

	private $_code;
	private $_message;

	public function __construct($code, $message, $data = []) {
		$this->_code = $code;
		$this->_message = $message;
		$this->load($data);
	}

	public function __get($key) {
		switch ($key) {
			case 'code':
			case 'message':
				return $this->{"_$key"};
			default:
				return parent::__get($key);
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
