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

	/**
	 * Sorts the data collection. If first argument is callable, it will be used
	 * as a comparison function. Otherwise, it will be used as a boolean flag to
	 * reverse-sort the collection. Omitting the sorting mode flag will sort the
	 * collection by its values and reset its keys to sequential indeces.
	 *
	 * The sorting mode flag works as follows:
	 *   'k' will sort the collection by its keys
	 *   'a' will sort the collection by its values, preserving its keys
	 *
	 * @param mixed $reverse Whether to reverse-sort the collection
	 * @param string $mode OPTIONAL Sorting mode flag
	 * @return boolean TRUE on success, FALSE on failure
	 */
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

	/**
	 * Apply a callback functon to each item in the collection.
	 * @param mixed $func A callable to apply to each item
	 * @param mixed $data OPTIONAL Data to pass along to callable
	 * @return boolean TRUE
	 */
	public function walk($func, $data = null) {
		return array_walk($this->_data, $func, $data);
	}

	/**
	 * @return array Key set for collection
	 */
	public function keys() {
		return array_keys($this->_data);
	}

	/**
	 * @return array Value set for collection
	 */
	public function values() {
		return array_values($this->_data);
	}

	/**
	 * Returns collection as array.
	 * @return array collection of data
	 */
	public function toArray() {
		return $this->_data;
	}

	/**
	 * Replaces internal data collection.
	 * @param mixed $data OPTIONAL Any array or traversable object
	 */
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
