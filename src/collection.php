<?php

/**
 * 'Implements' ArrayAccess, Countable, Iterator
 */
trait collection {
	private $_data = [];

	public function toArray() {
		return $this->_data;
	}

	protected function loadArray($data) {
		if (is_array($data)) {
			$this->_data = $data;
		} elseif ($data instanceof collection) {
			$this->_data = $data->_data;
		} elseif ($data instanceof Iterator) {
			$this->_data = [];
			foreach ($data as $key => $value)
				$this->_data[$key] = $value;
		} elseif (is_object($data)) {
			$this->_data = get_object_vars($data);
		} else {
			$this->_data = [];
		}
	}

	// from ArrayAccess interface

	public function offsetExists($offset) {
		return array_key_exists($offset, $this->_data);
	}

	public function offsetGet($offset) {
		return @$this->_data[$offset];
	}

	public function offsetSet($offset, $value) {
		$this->_data[$offset] = $value;
	}

	public function offsetUnset($offset) {
		unset($this->_data[$offset]);
	}

	// from Countable interface

	public function count() {
		return count($this->_data);
	}

	// from Iterator interface

	public function current() {
		return current($this->_data);
	}

	public function key() {
		return key($this->_data);
	}

	public function next() {
		next($this->_data);
	}

	public function rewind() {
		reset($this->_data);
	}

	public function valid() {
		return key($this->_data) !== null;
	}
}

interface collectible extends ArrayAccess, Countable, Iterator {}
