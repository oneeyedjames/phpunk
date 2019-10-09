<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

use PHPunk\Util\object;

/**
 * Contains a set of data return from a database query
 *
 * @property string $table The name of the related database table
 * @property integer $found The total number of records matching the query
 * @property object $first The first record of the returned data set
 */
class result extends object {
	/**
	 * @ignore internal variable
	 */
	private $_table;

	/**
	 * @ignore internal variable
	 */
	private $_found;

	/**
	 * @param array $records Array of database records
	 * @param integer $found Total count of matching records
	 * @param string $table OPTIONAL database table name
	 */
	public function __construct($records, $found, $table = false) {
		parent::__construct($records);
		$this->_found = intval($found);
		$this->_table = $table;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'table':
				return $this->_table;
			case 'found':
				return $this->_found;
			case 'first':
				return isset($this[0]) ? $this[0] : null;
			default:
				return parent::__get($key);
		}
	}

	/**
	 * Returns a new result after applying the given callback function to each
	 * record in the result.
	 *
	 * @param callable $func The callback function to apply to each record
	 * @return object a new result instance
	 */
	public function map($func) {
		return new self(array_map($func, $this->toArray()), $this->_found);
	}

	/**
	 * Returns a new result keyed by the results of the given callback function.
	 *
	 * @param callable $func The callback function to apply to each record
	 * @return object a new result instance
	 */
	public function key_map($func) {
		$keys   = array_map($func, $this->toArray());
		$values = array_values($this->toArray());

		return new self(array_combine($keys, $values), $this->_found);
	}
}
