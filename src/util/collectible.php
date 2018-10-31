<?php
/**
 * @package phpunk\util
 */

namespace PHPunk\Util;

use ArrayAccess, Countable, Iterator;

/**
 * Array-style key-value storage
 * 'Implements' ArrayAccess, Countable, Iterator
 *
 * @see http://github.com/oneeyedjames/phpunk/wiki/Mutable-Objects PHPunk Wiki
 */
trait collectible {
	/**
	 * Internal key-value storage
	 * @var array
	 */
	private $_data = [];

	public function sort($reverse = false, $mode = '') {
		if (is_callable($reverse)) {
			switch ($mode) {
				case 'a':
					return uasort($this->_data, $reverse);
				case 'k':
					return uksort($this->_data, $reverse);
				default:
					return usort($this->_data, $reverse);
			}
		}

		switch ($mode) {
			case 'a':
				return $reverse ? arsort($this->_data) : asort($this->_data);
			case 'k':
				return $reverse ? krsort($this->_data) : ksort($this->_data);
			default:
				return $reverse ? rsort($this->_data) : sort($this->_data);
		}
	}

	public function walk($func, $data = null) {
		return array_walk($this->_data, $func, $data);
	}

	public function keys() {
		return array_keys($this->_data);
	}

	public function values() {
		return array_values($this->_data);
	}

	public function toArray() {
		return $this->_data;
	}

	protected function loadArray($data) {
		if (is_array($data)) {
			$this->_data = $data;
		} elseif (is_collectible($data)) {
			$this->_data = $data->_data;
		} elseif (is_iterable($data)) {
			$this->_data = iterator_to_array($data);
		} elseif (is_object($data)) {
			$this->_data = get_object_vars($data);
		} else {
			$this->_data = [];
		}
	}

	/**
	 * @ignore implemented from ArrayAccess interface
	 */
	public function offsetExists($offset) {
		return array_key_exists($offset, $this->_data);
	}

	/**
	 * @ignore implemented from ArrayAccess interface
	 */
	public function offsetGet($offset) {
		return @$this->_data[$offset];
	}

	/**
	 * @ignore implemented from ArrayAccess interface
	 */
	public function offsetSet($offset, $value) {
		$this->_data[$offset] = $value;
	}

	/**
	 * @ignore implemented from ArrayAccess interface
	 */
	public function offsetUnset($offset) {
		unset($this->_data[$offset]);
	}

	/**
	 * @ignore implemented from Countable interface
	 */
	public function count() {
		return count($this->_data);
	}

	/**
	 * @ignore implemented from Iterator interface
	 */
	public function current() {
		return current($this->_data);
	}

	/**
	 * @ignore implemented from Iterator interface
	 */
	public function key() {
		return key($this->_data);
	}

	/**
	 * @ignore implemented from Iterator interface
	 */
	public function next() {
		next($this->_data);
	}

	/**
	 * @ignore implemented from Iterator interface
	 */
	public function rewind() {
		reset($this->_data);
	}

	/**
	 * @ignore implemented from Iterator interface
	 */
	public function valid() {
		return key($this->_data) !== null;
	}
}

/**
 * This inteface consolidates the interfaces ArrayAccess, Countable, and Iterator.
 * Classes using the collectible trait must implement this interface.
 */
interface collection extends ArrayAccess, Countable, Iterator {}

/**
 * Returns whether or not the object is an instance of collectible.
 *
 * @param object $obj Any object
 * @return boolean Returns **TRUE** if **obj** collectible, **FALSE** otherwise.
 */
function is_collectible($obj) {
	return $obj instanceof collectible;
}
