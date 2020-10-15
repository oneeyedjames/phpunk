<?php
/**
 * @package phpunk\collection
 */

namespace PHPunk\Collection;

use JsonSerializable;

/**
 * Basic reference implementation
 *
 * @see http://us2.php.net/manual/en/class.jsonserializable.php JsonSerializable
 * @see collectible Collectible Trait
 * @see mutable Mutable Trait
 * @see http://github.com/oneeyedjames/phpunk/wiki/Mutable-Objects PHPunk Wiki
 *
 * @property object $meta Mutable object of metadata
 */
class collection implements arraylike, JsonSerializable {
	use mutable;

	/**
	 * @ignore internal variable
	 */
	private $_meta;

	/**
	 * Creates a shallow clone of the passed data.
	 *
	 * @param mixed $data OPTIONAL Any array or traversable object
	 */
	public function __construct($data = []) {
		$this->load($data);
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'meta':
				if (is_null($this->_meta))
					$this->_meta = new self();

				return $this->_meta;
			default:
				return $this->get($key);
		}
	}

	/**
	 * @ignore implementation for JsonSerializable
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
}
