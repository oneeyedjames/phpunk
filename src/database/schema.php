<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

/**
 * @property mixed $insert_id Most recent auto-generated unique ID from MySQL
 */
class schema {
	/**
	 * @ignore internal variable
	 */
	private $_mysql = null;

	/**
	 * @ignore internal variable
	 */
	private $_tables = array();

	/**
	 * @ignore internal variable
	 */
	private $_rels = array();

	/**
	 * @param object $mysql A previously-established MySQLi instance
	 */
	public function __construct($mysql) {
		$this->_mysql = $mysql;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'insert_id':
				return $this->_mysql->insert_id;
			default:
				return $this->get_table($key);
		}
	}

	/**
	 * Executes a parameterized SELECT query.
	 * @param string $sql A standard SQL query string
	 * @param array $params OPTIONAL Index-based query parameters
	 * @param string $table_name OPTIONAL Table name to be passed on to result
	 * @return object Database result object, FALSE on failure
	 */
	public function query($sql, $params = array(), $table_name = false) {
		if ($stmt = $this->_mysql->prepare($sql)) {
			if (is_scalar($params))
				$params = array_slice(func_get_args(), 1);

			if (count($params)) {
				$_params = array('');

				for ($i = 0, $n = count($params); $i < $n; $i++) {
					$_params[$i + 1] =& $params[$i];

					if (is_int($params[$i]))
						$_params[0] .= 'i';
					elseif (is_float($params[$i]))
						$_params[0] .= 'd';
					else
						$_params[0] .= 's';
				}

				if (!call_user_func_array(array($stmt, 'bind_param'), $_params))
					trigger_error($stmt->error, E_USER_WARNING);
			}

			if ($stmt->execute() && $result = $stmt->get_result()) {
				$records = array();
				$found = 0;

				while ($record = $result->fetch_assoc())
					$records[] = new record($record, $table_name);

				$result->free();

				if ($result = $this->_mysql->query('SELECT FOUND_ROWS()')) {
					if ($record = $result->fetch_row())
						$found = intval($record[0]);

					$result->free();
				}

				return new result($records, $found, $table_name);
			} else {
				error_log($sql);
				trigger_error($stmt->error, E_USER_WARNING);
			}

			$stmt->close();
		} else {
			trigger_error($this->_mysql->error, E_USER_WARNING);
		}

		return false;
	}

	/**
	 * Executes a parameterized INSERT, UPDATE, or DELETE query.
	 * @param string $sql A standard SQL query string
	 * @param array $params OPTIONAL Index-based query parameters
	 * @return boolean TRUE on sucess, FALSE on failure
	 */
	public function execute($sql, $params = array()) {
		$result = false;

		if ($stmt = $this->_mysql->prepare($sql)) {
			if (is_scalar($params))
				$params = array_slice(func_get_args(), 1);

			if (count($params)) {
				$_params = array('');

				for ($i = 0, $n = count($params); $i < $n; $i++) {
					$_params[$i + 1] =& $params[$i];

					if (is_int($params[$i]))
						$_params[0] .= 'i';
					elseif (is_float($params[$i]))
						$_params[0] .= 'd';
					else
						$_params[0] .= 's';
				}

				if (!call_user_func_array(array($stmt, 'bind_param'), $_params))
					trigger_error($stmt->error, E_USER_WARNING);
			}

			$this->_found_rows = false;

			if (!($result = $stmt->execute()))
				trigger_error($stmt->error, E_USER_WARNING);

			$stmt->close();

			return $result;
		} else {
			trigger_error($this->_mysql->error, E_USER_WARNING);
		}

		return $result;
	}

	/**
	 * Returns whether a table with the given name has been defined in the database schema.
	 * @param string $name The name of the database table
	 * @return boolean TRUE if the table name is defined, or FALSE otherwise
	 */
	public function table_exists($name) {
		return isset($this->_tables[$name]);
	}

	/**
	 * Returns the database table object with the given name.
	 * @param string $name The name of the database table
	 * @return object Table object if the table name is defined, or FALSE otherwise
	 */
	public function get_table($name) {
		return @$this->_tables[$name];
	}

	/**
	 * Defines a database table name in schema. If the table name already exists
	 * in the schema, this method will merely return the defined table object.
	 * @param string $name Table name
	 * @param string $pkey OPTIONAL Primary key field, defaults to 'id'
	 * @return object Newly-defined table object
	 */
	public function add_table($name, $pkey = 'id') {
		if (!isset($this->_tables[$name])) {
			$this->_tables[$name] = is_null($pkey)
				? new bridge_table($name, $pkey)
				: new table($name, $pkey);
		}

		return $this->_tables[$name];
	}

	/**
	 * Removes a database table definition from the schema.
	 * @param string $name Table name
	 */
	public function remove_table($name) {
		unset($this->_tables[$name]);
	}

	/**
	 * Removes all database table definitions from schema.
	 */
	public function clear_tables() {
		$this->_tables = array();
	}

	/**
	 * Returns whether a foreign-key relationship with the given name has been
	 * defined in the database schema.
	 * @param string $name The name of the relationship
	 * @return boolean TRUE if the relationship is defined, FALSE otherwise
	 */
	public function relation_exists($name) {
		return isset($this->_rels[$name]);
	}

	/**
	 * Returns the foreign-key relationship object with the given name.
	 * @param string $name The name of the relationship
	 * @return object Relation object if the relationship is defined, FALSE otherwise
	 */
	public function get_relation($name) {
		return @$this->_rels[$name];
	}

	/**
	 * Defines a relationship in the schema. If the relationship already exists
	 * in the schema, this method will merely return the defined relation object.
	 * @param string $rel_name Relationship name
	 * @param string $ptable_name Name of table containing primary key
	 * @param string $ftable_name Name of table containing foreign key
	 * @param string $fkey Name of foreign key field
	 * @return object Newly-defined relation object
	 */
	public function add_relation($rel_name, $ptable_name, $ftable_name, $fkey) {
		if (!$this->relation_exists($rel_name)) {
			if (($ptable =& $this->_tables[$ptable_name]) &&
				($ftable =& $this->_tables[$ftable_name])) {
				$rel = new relation($rel_name, $ptable, $ftable, $fkey);

				$ptable->add_relation($rel_name, $rel);
				$ftable->add_relation($rel_name, $rel);

				$this->_rels[$rel_name] =& $rel;
			}
		}

		return $this->get_relation($rel_name);
	}

	/**
	 * Removes a relationship definition from the schema.
	 * @param string $name Relationship name
	 */
	public function remove_relation($name) {
		if ($rel =& $this->_rels[$name]) {
			unset($this->_rels[$name]);

			if ($ptable =& $this->_tables[$rel->ptable])
				$ptable->remove_relation($name);

			if ($ftable =& $this->_tables[$rel->ftable])
				$ftable->remove_relation($name);
		}
	}

	/**
	 * Removes all database relationship definitions from schema.
	 */
	public function clear_relations() {
		$this->_rels = array();

		foreach ($this->_tables as &$table)
			$table->clear_relations();
	}

	/**
	 * Return database record by unique identifier. If the relationship argument
	 * is used, the identifier will be interpreted as the foreign record's
	 * primary key. Otherwise, the identifier will be interpreted as the record's
	 * own primary key.
	 * @param string $table_name Table name
	 * @param mixed $record_id Unique identifier for database record
	 * @param string $rel_name OPTIONAL Relationship name
	 * @return object Record identified by parameter, NULL on failure
	 */
	public function get_record($table_name, $record_id, $rel_name = false) {
		if ($table = @$this->_tables[$table_name]) {
			$sql = $table->select_sql($rel_name);

			$params = array(intval($record_id));

			if ($result = $this->query($sql, $params, $table_name))
				return $result->first;
		}

		return null;
	}

	/**
	 * Inserts or updates a database record and returns the record's primary key.
	 * @param string $table_name Table name
	 * @param mixed $record Key-value data for database record
	 * @return mixed Record's primary key
	 */
	public function put_record($table_name, $record) {
		if ($table = @$this->_tables[$table_name]) {
			if (is_object($record))
				$record = method_exists($record, 'toArray')
					? $record->toArray()
					: get_object_vars($record);

			$params = array();
			$insert = @$record[$table->pkey] == 0;

			if ($insert)
				$sql = $table->insert_sql($record, $params);
			else
				$sql = $table->update_sql($record, $params);

			$this->execute($sql, $params);

			return @$record[$table->pkey] ?: $this->insert_id;
		}

		return false;
	}

	/**
	 * Deletes a database record by primary key.
	 * @param string $table_name Table name
	 * @param mixed $record_id Record's primary key
	 * @return boolean TRUE on success, False on failure
	 */
	public function remove_record($table_name, $record_id) {
		if ($table = @$this->_tables[$table_name]) {
			$sql = $table->delete_sql();

			$params = array(intval($record_id));

			return $this->execute($sql, $params);
		}

		return false;
	}
}
