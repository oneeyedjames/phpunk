<?php

class database_record implements ArrayAccess, JsonSerializable {
	private $_fields;

	public function __construct($fields) {
		if (is_array($fields))
			$this->_fields = $fields;
		elseif (is_object($fields))
			$this->_fields = get_object_vars($fields);
	}

	public function __get($key) {
		return $this->offsetGet($key);
	}

	public function __set($key, $value) {
		$this->offsetSet($key, $value);
	}

	public function __isset($key) {
		return $this->offsetExists($key);
	}

	public function __unset($key) {
		$this->offsetUnset($key);
	}

	public function offsetGet ($key) {
		return @$this->_fields[$key];
	}

	public function offsetSet ($key, $value) {
		$this->_fields[$key] = $value;
	}

	public function offsetExists ($key) {
		return isset($this->_fields[$key]);
	}

	public function offsetUnset ($key) {
		unset($this->_fields[$key]);
	}

	public function toArray() {
		return $this->_fields;
	}

	public function jsonSerialize() {
		return $this->_fields;
	}
}
