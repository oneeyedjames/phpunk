<?php

class database_result implements Iterator, Countable, ArrayAccess, JsonSerializable {
	private $_records;
	private $_found;

	public function __construct($records, $found) {
		$this->_records = $records;
		$this->_found = intval($found);
	}

	public function __get($key) {
		switch ($key) {
			case 'found':
				return $this->_found;
			case 'first':
				return count($this) ? $this->_records[0] : null;
			case 'keys':
				return array_keys($this->_records);
		}
	}

	public function current() {
		return current($this->_records);
	}

	public function key() {
		return key($this->_records);
	}

	public function next() {
		next($this->_records);
	}

	public function rewind() {
		reset($this->_records);
	}

	public function valid() {
		return key($this->_records) !== null;
	}

	public function count() {
		return count($this->_records);
	}

	public function offsetGet ($key) {
		return $this->_records[$key];
	}

	public function offsetSet ($key, $value) {}

	public function offsetExists ($key) {
		return isset($this->_records[$key]);
	}

	public function offsetUnset ($key) {}

	public function jsonSerialize() {
		return array_values($this->_records);
	}

	public function sort($reverse = false, $mode = '') {
		if (is_callable($reverse)) {
			switch ($mode) {
				case 'a':
					return uasort($this->_records, $reverse);
				case 'k':
					return uksort($this->_records, $reverse);
				default:
					return usort($this->_records, $reverse);
			}
		}

		switch ($mode) {
			case 'a':
				return $reverse ? arsort($this->_records) : asort($this->_records);
			case 'k':
				return $reverse ? krsort($this->_records) : ksort($this->_records);
			default:
				return $reverse ? rsort($this->_records) : sort($this->_records);
		}
	}

	public function walk($func, $data = null) {
		return array_walk($this->_records, $func, $data);
	}

	public function map($func) {
		return new self(array_map($func, $this->_records), $this->_found);
	}

	public function key_map($func) {
		$keys   = array_map($func, $this->_records);
		$values = array_values($this->_records);

		return new self(array_combine($keys, $values), $this->_found);
	}
}
