<?php

class database_schema {
	private $_mysql = null;

	private $_tables = array();

	private $_rels = array();

	public function __construct($mysql) {
		$this->_mysql = $mysql;
	}

	public function __get($key) {
		switch ($key) {
			case 'insert_id':
				return $this->_mysql->insert_id;
			default:
				return $this->get_table($key);
		}
	}

	public function query($sql, $params = array()) {
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
					$records[] = new database_record($record);

				$result->free();

				if ($result = $this->_mysql->query('SELECT FOUND_ROWS()')) {
					if ($record = $result->fetch_row())
						$found = intval($record[0]);

					$result->free();
				}

				return new database_result($records, $found);
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

	public function table_exists($name) {
		return isset($this->_tables[$name]);
	}

	public function get_table($name) {
		return @$this->_tables[$name];
	}

	public function add_table($name, $pkey = 'id') {
		if (!isset($this->_tables[$name])) {
			$this->_tables[$name] = is_null($pkey)
				? new database_bridge_table($name, $pkey)
				: new database_table($name, $pkey);
		}

		return $this->_tables[$name];
	}

	public function remove_table($name) {
		unset($this->_tables[$name]);
	}

	public function clear_tables() {
		$this->_tables = array();
	}

	public function relation_exists($name) {
		return isset($this->_rels[$name]);
	}

	public function get_relation($name) {
		return @$this->_rels[$name];
	}

	public function add_relation($rel_name, $ptable_name, $ftable_name, $fkey) {
		if (!$this->relation_exists($rel_name)) {
			if (($ptable =& $this->_tables[$ptable_name]) &&
				($ftable =& $this->_tables[$ftable_name])) {
				$rel = new database_relation($rel_name, $ptable, $ftable, $fkey);

				$ptable->add_relation($rel_name, $rel);
				$ftable->add_relation($rel_name, $rel);

				$this->_rels[$rel_name] =& $rel;
			}
		}

		return $this->get_relation($rel_name);
	}

	public function remove_relation($name) {
		if ($rel =& $this->_rels[$name]) {
			unset($this->_rels[$name]);

			if ($ptable =& $this->_tables[$rel->ptable])
				$ptable->remove_relation($name);

			if ($ftable =& $this->_tables[$rel->ftable])
				$ftable->remove_relation($name);
		}
	}

	public function clear_relations() {
		$this->_rels = array();

		foreach ($this->_tables as &$table)
			$table->clear_relations();
	}

	public function get_record($table_name, $record_id, $rel_name = false) {
		if ($table = @$this->_tables[$table_name]) {
			$sql = $table->select_sql($rel_name);

			$params = array(intval($record_id));

			if ($result = $this->query($sql, $params))
				return $result->first;
		}

		return null;
	}

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

	public function remove_record($table_name, $record_id) {
		if ($table = @$this->_tables[$table_name]) {
			$sql = $table->delete_sql();

			$params = array(intval($record_id));

			return $this->execute($sql, $params);
		}

		return false;
	}
}
