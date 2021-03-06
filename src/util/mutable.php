<?php
/**
 * @package phpunk\util
 */

namespace PHPunk\Util;

/**
 * Object-oriented style syntax support
 *
 * @see http://github.com/oneeyedjames/phpunk/wiki/Mutable-Objects PHPunk Wiki
 */
trait mutable {
	use collectible;

	/**
	 * @ignore magic method
	 */
	public function __call($func, $args) {
		if (count($args) == 1)
			return $this->get($func, $args[0]);

		$class = get_class($this);
		trigger_error("Call to undefined method $class::$func()", E_USER_WARNING);
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		return $this->get($key);
	}

	/**
	 * @ignore magic method
	 */
	public function __set($key, $value) {
		$this->put($key, $value);
	}

	/**
	 * @ignore magic method
	 */
	public function __isset($key) {
		return $this->has($key);
	}

	/**
	 * @ignore magic method
	 */
	public function __unset($key) {
		$this->remove($key);
	}

	/**
	 * Determine whether or not a given key exists.
	 *
	 * @param mixed $key The key
	 * @return boolean TRUE if the key exists, FALSE otherwise
	 */
	public function has($key) {
		return $this->offsetExists($key);
	}

	/**
	 * Get the stored value for a given key.
	 *
	 * @param mixed $key The key
	 * @param mixed $default OPTIONAL The value to return if the given key does not exist
	 */
	public function get($key, $default = null) {
		return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
	}

	/**
	 * Store a value for a given key.
	 *
	 * @param mixed $key The key
	 * @param mixed $value The value to be stored
	 */
	public function put($key, $value) {
		$this->offsetSet($key, $value);
	}

	/**
	 * Remove a key-value pair from the store.
	 *
	 * @param mixed $key The key
	 */
	public function remove($key) {
		$this->offsetUnset($key);
	}
}

/**
 * Returns whether or not the object is an instance of mutable.
 *
 * @param object $obj Any object
 * @return boolean Returns **TRUE** if **obj** mutable, **FALSE** otherwise.
 */
function is_mutable($obj) {
	return $obj instanceof mutable;
}
