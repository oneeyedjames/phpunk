<?php

interface Mutable extends Iterator, Countable, ArrayAccess, JsonSerializable {
	public function has($key);

	public function get($key);
	public function get_all();

	public function put($key, $value);
	public function put_all($values);

	public function remove($key);
	public function remove_all();
}

interface ArrayWrapper {
	public function toArray();
}

class object implements Mutable, ArrayWrapper {
	private $_vars = array();

	public function __construct($vars = null) {
		$this->put_all($vars);
	}

	public function __get($key) {
		return $this->get($key);
	}

	public function __set($key, $value) {
		$this->put($key, $value);
	}

	public function __isset($key) {
		return $this->has($key);
	}

	public function __unset($key) {
		$this->remove($key);
	}

	public function __call($func, $args) {
		if (count($args) == 1)
			return $this->get($func, $args[0]);

		$class = get_class($this);
		trigger_error("Call to undefined method $class::$func()", E_USER_WARNING);
	}

	public function current() {
		return current($this->_vars);
	}

	public function key() {
		return key($this->_vars);
	}

	public function next() {
		next($this->_vars);
	}

	public function rewind() {
		reset($this->_vars);
	}

	public function valid() {
		return key($this->_vars) !== null;
	}

	public function count() {
		return count($this->_vars);
	}

	public function has($key) {
		return array_key_exists($key, $this->_vars);
	}

	public function get($key, $default = null) {
		return $this->has($key) ? $this->_vars[$key] : $default;
	}

	public function get_all() {
		return $this->_vars;
	}

	public function put($key, $value) {
		$this->_vars[$key] = $value;
	}

	public function put_all($vars) {
		if (is_array($vars) || is_a($vars, 'Iterator')) {
			foreach ($vars as $key => $value) {
				$this->put($key, $value);
			}
		} elseif (is_object($vars)) {
			$this->put_all(get_object_vars($vars));
		}
	}

	public function remove($key) {
		unset($this->_vars[$key]);
	}

	public function remove_all() {
		$this->_vars = array();
	}

	public function offsetGet ($key) {
		return $this->get($key);
	}

	public function offsetSet ($key, $value) {
		$this->put($key, $value);
	}

	public function offsetExists ($key) {
		return $this->has($key);
	}

	public function offsetUnset ($key) {
		$this->remove($key);
	}

	public function toArray() {
		return $this->_vars;
	}

	public function jsonSerialize() {
		return $this->_vars;
	}
}
