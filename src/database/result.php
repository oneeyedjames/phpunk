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

	public function walk($func, $data = null) {
		return array_walk($this->_records, $func, $data);
	}

	public function map($func) {
		return new self(array_map($func, $this->_records), $this->_found);
	}
}
