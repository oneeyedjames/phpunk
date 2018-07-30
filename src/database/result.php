<?php

class database_result extends object {
	private $_found;

	public function __construct($records, $found) {
		parent::__construct($records);
		$this->_found = intval($found);
	}

	public function __get($key) {
		switch ($key) {
			case 'found':
				return $this->_found;
			case 'first':
				return isset($this[0]) ? $this[0] : null;
			default:
				return $this->get($key);
		}
	}

	public function map($func) {
		return new self(array_map($func, $this->toArray()), $this->_found);
	}

	public function key_map($func) {
		$keys   = array_map($func, $this->toArray());
		$values = array_values($this->toArray());

		return new self(array_combine($keys, $values), $this->_found);
	}
}
