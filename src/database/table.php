<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

/**
 * @property string $name Name of database table
 * @property string $pkey Name of primary key
 * @property array $relations Relationships to other database tables
 */
class table {
	/**
	 * @ignore internal variable
	 */
	private $_name = null;

	/**
	 * @ignore internal variable
	 */
	private $_pkey = null;

	/**
	 * @ignore internal variable
	 */
	private $_rels = array();

	/**
	 * @property string $name Name of database table
	 * @property string $pkey Name of primary key
	 */
	public function __construct($name, $pkey = 'id') {
		$this->_name = $name;
		$this->_pkey = $pkey;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'name':
				return $this->_name;
			case 'pkey':
				return $this->_pkey;
			case 'relations':
				return $this->_rels;
		}
	}

	/**
	 * Returns whether or not a relationship exists with the given name
	 * @param string $name Name of the table relationship
	 * @return boolean TRUE if relationship exists, FALSE otherwise
	 */
	public function relation_exists($name) {
		return isset($this->_rels[$name]);
	}

	/**
	 * Returns a relation object for the given name
	 * @param string $name Name of the table relationship
	 * @return object The named relation object
	 */
	public function get_relation($name) {
		return @$this->_rels[$name];
	}

	/**
	 * Adds a relation for the given name
	 * @param string $name Name of the table relationship
	 * @param object $rel The relation object
	 */
	public function add_relation($name, $rel) {
		$this->_rels[$name] = $rel;
	}

	/**
	 * Removes the relation with the given name
	 * @param string $name Name of the table relationship
	 */
	public function remove_relation($name) {
		unset($this->_rels[$name]);
	}

	/**
	 * Removes all relationships from this table.
	 */
	public function clear_relations() {
		$this->_rels = array();
	}

	/**
	 * Generates a SELECT query string, based on this table's primary key
	 * @param string $name OPTIONAL relationship name to query
	 * @return string A parameterized SQL query
	 */
	public function select_sql($name = false) {
		if ($name) {
			if ($rel = $this->get_relation($name)) {
				$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
				return "SELECT SQL_CALC_FOUND_ROWS `$table`.* FROM $rel->join WHERE `$this->name`.`$this->pkey` = ?";
			}

			return false;
		}

		return "SELECT SQL_CALC_FOUND_ROWS * FROM `$this->name` WHERE `$this->pkey` = ?";
	}

	/**
	 * Generates an INSERT query string, based on the given data
	 * @param mixed $record An array or iterable object of key-value pairs
	 * @param array $params An array to be populated with query parameters
	 * @return string A parameterized SQL query
	 */
	public function insert_sql($record, &$params) {
		$fields = array();

		foreach ($record as $field => $value) {
			if ($field != $this->pkey) {
				$fields[] = "`$field`";
				$params[] = $value;
			}
		}

		$places = implode(", ", array_fill(0, count($fields), '?'));
		$fields = implode(", ", $fields);

		return "INSERT INTO `$this->name` ($fields) VALUES ($places)";
	}

	/**
	 * Generates an UPDATE query string, based on the given data
	 * @param mixed $record An array or iterable object of key-value pairs
	 * @param array $params An array to be populated with query parameters
	 * @return string A parameterized SQL query
	 */
	public function update_sql($record, &$params) {
		$fields = array();

		foreach ($record as $field => $value) {
			if ($field != $this->pkey) {
				$fields[] = "`$field` = ?";
				$params[] = $value;
			}
		}

		$fields = implode(", ", $fields);

		$params[] = $record[$this->pkey];

		return "UPDATE `$this->name` SET $fields WHERE `$this->pkey` = ?";
	}

	/**
	 * Generates a DELETE query string, based on this table's primary key
	 * @param string $name OPTIONAL relationship name to query
	 * @return string A parameterized SQL query
	 */
	public function delete_sql($name = false) {
		if ($name) {
			if ($rel = $this->get_relation($name)) {
				$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
				return "DELETE `$table`.* FROM $rel->join WHERE `$this->name`.`$this->pkey` = ?";
			}

			return false;
		}

		return "DELETE FROM `$this->name` WHERE `$this->pkey` = ?";
	}
}
