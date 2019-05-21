<?php
/**
 * @package phpunk
 */

namespace PHPunk;

use JsonSerializable;

use PHPunk\Util\collectible;
use PHPunk\Util\collection;

/**
 * @property string $code Machine-readable error code
 * @property string $message Human-readable error message
 */
class api_error implements collection, JsonSerializable {
	use collectible;

	/**
	 * @ignore internal variable
	 */
	private $_code;

	/**
	 * @ignore internal variable
	 */
	private $_message;

	/**
	 * Creates a new error object with the given code and message
	 *
	 * @param string $code Machine-readable error code
	 * @param string $message Human-readable error message
	 * @param mixed $data OPTIONAL array or traversable object of additional data
	 */
	public function __construct($code, $message, $data = []) {
		$this->_code = $code;
		$this->_message = $message;
		$this->loadArray($data);
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'code':
			case 'message':
				return $this->{"_$key"};
		}
	}

	/**
	 * @ignore implementation for JsonSerializable
	 */
	public function jsonSerialize() {
		return [
			'code'    => $this->_code,
			'message' => $this->_message,
			'data'    => $this->toArray()
		];
	}
}
