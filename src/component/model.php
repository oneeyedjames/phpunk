<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\Database\query;

class model {
	private $_resource;
	private $_database;
	private $_cache;

	public function __construct($resource, $database, $cache = null) {
		$this->_resource = $resource;
		$this->_database = $database;
		$this->_cache    = $cache;
	}

	public function __get($key) {
		switch ($key) {
			case 'resource':
				return $this->_resource;
			case 'insert_id':
				return $this->_database->insert_id;
		}
	}

	public function get_record($id) {
		if ($record = $this->get_cached_object($id))
			return $record;

		if ($record = $this->_database->get_record($this->_resource, $id))
			$this->put_cached_object($id, $record);

		return $record;
	}

	public function put_record($record) {
		if ($record = $this->_database->put_record($this->_resource, $record))
			$this->put_cached_object($record->id, $record);

		return $record;
	}

	public function remove_record($id) {
		if ($result = $this->_database->remove_record($this->_resource, $id))
			$this->remove_cached_object($id);

		return $result;
	}

	protected function query($sql, $params = array()) {
		$params = is_array($params) ? $params : array_slice(func_get_args(), 1);
		return $this->_database->query($sql, $params);
	}

	protected function execute($sql, $params = array()) {
		$params = is_array($params) ? $params : array_slice(func_get_args(), 1);
		return $this->_database->execute($sql, $params);
	}

	protected function make_query($args) {
		$args['table'] = $this->_resource;
		return new query($this->_database, $args);
	}

	protected function get_cached_object($id) {
		return @$this->_cache->get($this->_resource, $id);
	}

	protected function put_cached_object($id, $object) {
		return @$this->_cache->put($this->_resource, $id, $object);
	}

	protected function remove_cached_object($id) {
		return @$this->_cache->remove($this->_resource, $id);
	}
}
