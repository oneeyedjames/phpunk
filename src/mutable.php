<?php

trait mutable {
	use collectible;

	public function __call($func, $args) {
		if (count($args) == 1)
			return $this->get($func, $args[0]);

		$class = get_class($this);
		trigger_error("Call to undefined method $class::$func()", E_USER_WARNING);
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

	public function has($key) {
		return $this->offsetExists($key);
	}

	public function get($key, $default = null) {
		return $this->offsetExists($key) ? $this->offsetGet($key) : $default;
	}

	public function put($key, $value) {
		$this->offsetSet($key, $value);
	}

	public function remove($key) {
		$this->offsetUnset($key);
	}
}

function is_mutable($obj) {
	return $obj instanceof mutable;
}
