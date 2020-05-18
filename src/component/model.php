<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\Database\query;
use PHPunk\Database\record;

/**
 * @property string $resource Resource name for this component
 * @property string $table Database table name for this component
 * @property mixed $insert_id Most recent auto-generated Id on the database connection
 */
class model {
	/**
	 * @ignore internal variable
	 */
	private $_resource;

	/**
	 * @ignore internal variable
	 */
	private $_database;

	/**
	 * @ignore internal variable
	 */
	private $_table;

	/**
	 * @ignore internal variable
	 */
	private $_cache;

	/**
	 * @property string $resource Resource name for this component
	 * @property object $database Database instance for this component
	 * @property object $cache OPTIONAL Cache instance for this component
	 */
	public function __construct($resource, $database, $cache = null) {
		$this->_resource = $resource;
		$this->_database = $database;
		$this->_cache    = $cache;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'resource':
				return $this->_resource;
			case 'table':
				return $this->_table ?: $this->_resource;
			case 'insert_id':
				return $this->_database->insert_id;
		}
	}

	/**
	 * Instantiates a new database record with the given data.
	 * @param mixed $data Associative array of data or record ID
	 * @return object New database record
	 */
	public function create_record($data = []) {
		if (is_numeric($data)) $data = ['id' => $data];
		return new record($data, $this->table);
	}

	/**
	 * Fetches a single database record by unique identifier.
	 * Returns cached copy, if available.
	 * @param mixed $id Unique identifier for database record
	 * @return object Record identified by parameter, NULL on failure
	 */
	public function get_record($id) {
		if ($record = $this->get_cached_object($id))
			return $record;

		if ($record = $this->_database->get_record($this->table, $id))
			$this->put_cached_object($id, $record);

		return $record;
	}

	/**
	 * Saves a single database record to the database.
	 * Invalidates any previously-cached copy of record.
	 * @param object $record Record instance to save to database
	 * @return object Record instance updated with auto-generated id, FALSE on failure
	 */
	public function put_record($record) {
		if ($record = $this->_database->put_record($this->table, $record))
			$this->put_cached_object($record->id, $record);

		return $record;
	}

	/**
	 * Deletes a single database record by unique identifier.
	 * Invalidates any previously-cached copy of the record.
	 * @param mixed $id Unique identifier for database record
	 * @return boolean TRUE on success, FALSE on failure
	 */
	public function remove_record($id) {
		if ($result = $this->_database->remove_record($this->table, $id))
			$this->remove_cached_object($id);

		return $result;
	}

	/**
	 * Overrides the database table name.
	 * @param string $table Name of database table
	 */
	protected function set_table_name($table) {
		$this->_table = $table;
	}

	/**
	 * Runs a raw parameterized SQL query against the database.
	 * Retrieves data from database.
	 * @param string $sql SQL query to run
	 * @param array $params OPTIONAL Array of parameter values for query
	 * @return object Result instance returned by query
	 */
	protected function query($sql, $params = []) {
		$params = is_array($params) ? $params : array_slice(func_get_args(), 1);
		return $this->_database->query($sql, $params);
	}

	/**
	 * Runs a raw parameterized SQL query against the database.
	 * Manipulates data in database.
	 * @param string $sql SQL query to run
	 * @param array $params OPTIONAL Array of parameter values for query
	 * @return boolean TRUE on success, FALSE on failure
	 */
	protected function execute($sql, $params = []) {
		$params = is_array($params) ? $params : array_slice(func_get_args(), 1);
		return $this->_database->execute($sql, $params);
	}

	/**
	 * Creates a new database query object for this component.
	 * @see PHPunk\Database\query
	 * @param array $args Name parameters to pass to query object
	 * @return object Database query object
	 */
	protected function make_query($args) {
		$args['table'] = $this->table;
		return new query($this->_database, $args);
	}

	/**
	 * Fetches a cached copy of a data object by unique identifier.
	 * @param mixed $id Unique identifier for database record
	 * @return object Cached data object, NULL on failure
	 */
	protected function get_cached_object($id) {
		return @$this->_cache->get($this->resource, $id);
	}

	/**
	 * Caches a copy of a single data object by unique identifier.
	 * @param mixed $id Unique identifier for data object
	 * @param object $object data object to cache
	 * @return object Cached data object
	 */
	protected function put_cached_object($id, $object) {
		return @$this->_cache->put($this->resource, $id, $object);
	}

	/**
	 * Un-caches a copy of a single data object by unique identifier.
	 * @param mixed $id Unique identifier for database record
	 */
	protected function remove_cached_object($id) {
		return @$this->_cache->remove($this->resource, $id);
	}
}
