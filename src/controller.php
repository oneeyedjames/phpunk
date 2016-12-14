<?php

class controller_base {
	private $_resource;
	private $_database;
	private $_cache;

    public function __construct($resource, $database, $cache) {
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

    public function do_action($action) {
		$action = str_replace('-', '_', $action);
        $method = $action . '_action';

        if (method_exists($this, $method))
            return call_user_func(array($this, $method), $_GET, $_POST);
        else
            trigger_error("Undefined action $this->resource:$action", E_USER_WARNING);
    }

	protected function query($sql, $params = array(), &$found = null) {
		return $this->_database->query($sql, $params, $found);
	}

	protected function execute($sql, $params = array()) {
		return $this->_database->execute($sql, $params);
	}

	protected function get_record($id, $resource = false) {
		return $this->_database->get_record($resource ?: $this->_resource, $id);
	}

	protected function put_record($record) {
		return $this->_database->put_record($this->_resource, $record);
	}

	protected function get_cached_object($id, $type = false) {
		return $this->_cache->get($type ?: $this->_resource, $id);
	}

	protected function put_cached_object($id, $object, $type = false) {
		return $this->_cache->put($type ?: $this->_resource, $id, $object);
	}

	protected function remove_cached_object($id, $type = false) {
		$this->_cache->remove($type ?: $this->_resource, $id, $object);
	}

	protected function map_result($result, $key = 'id') {
		return new object(array_combine(array_map(function($record) use ($key) {
			return $record[$key];
		}, $result), $result));
	}
}
