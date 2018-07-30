<?php

class database_record extends object {
	private $_table;

	public function __construct($data = [], $table = false) {
		parent::__construct($data);
		$this->_table = $table;
	}

	public function __get($key) {
		switch ($key) {
			case 'table':
				return $this->_table;
			default:
				return parent::__get($key);
		}
	}
}
