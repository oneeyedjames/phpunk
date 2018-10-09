<?php

/**
 * Basic reference implementation
 *
 * @see http://us2.php.net/manual/en/class.jsonserializable.php JsonSerializable
 * @see collectible Collectible Trait
 * @see mutable Mutable Trait
 */
class object implements collection, JsonSerializable {
	use mutable;

	/**
	 * Creates a shallow clone of the passed data.
	 *
	 * @param mixed $data OPTIONAL Any array or traversable object
	 */
	public function __construct($data = []) {
		$this->loadArray($data);
	}

	/**
	 * @ignore implementation for JsonSerializable
	 */
	public function jsonSerialize() {
		return $this->toArray();
	}
}
