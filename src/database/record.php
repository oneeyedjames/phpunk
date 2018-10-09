<?php

/**
 * Represents a row in a database table
 * @property string $table The name of the related database table
 */
class database_record extends object {
	/**
	 * Table name
	 * @var string
	 */
	private $_table;

	/**
	 * Creates a shallow clone of the passed data.
	 *
	 * @param mixed $data OPTIONAL Any array or traversable object
	 * @param string $table OPTIONAL The name of the related database table
	 */
	public function __construct($data = [], $table = false) {
		parent::__construct($data);
		$this->_table = $table;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'table':
				return $this->_table;
			default:
				return parent::__get($key);
		}
	}
}
