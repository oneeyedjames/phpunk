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

	public function query($sql, $params = array(), &$count = null) {
		$records = false;

		if ($stmt = $this->_mysql->prepare($sql)) {
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

				while ($record = $result->fetch_object())
					$records[] = new object($record);

				$result->free();

				if ($result = $this->_mysql->query('SELECT FOUND_ROWS()')) {
					if ($record = $result->fetch_row())
						$count = intval($record[0]);

					$result->free();
				}
			} else {
				error_log($sql);
				trigger_error($stmt->error, E_USER_WARNING);
			}

			$stmt->close();
		} else {
			trigger_error($this->_mysql->error, E_USER_WARNING);
		}

		return $records;
	}

	public function execute($sql, $params = array()) {
		$result = false;

		if ($stmt = $this->_mysql->prepare($sql)) {
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

			if ($recordset = $this->query($sql, $params))
				return $recordset[0];
		}

		return null;
	}

	public function put_record($table_key, $record) {
		if ($table = @$this->_tables[$table_key]) {
			if (is_a($record, 'object'))
				$record = $record->get_all();
			elseif (is_object($record))
				$record = get_object_vars($record);

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
}

class database_query {
	private static $_defaults = array(
		'table'  => '',
		'bridge' => '',
		'args'   => array(),
		'sort'   => array(),
		'limit'  => 0,
		'offset' => 0
	);

	private $_database;

	private $_table;
	private $_bridge;
	private $_args = array();
	private $_sort = array();
	private $_limit = 0;
	private $_offset = 0;

	private $_query;
	private $_result;

	public function __construct($database, $args) {
		$this->_database = $database;

		$args = new object(array_merge(self::$_defaults, $args));

		$this->_table  = $args->table;
		$this->_bridge = $args->bridge;
		$this->_args   = $args->args;
		$this->_sort   = $args->sort;
		$this->_limit  = $args->limit;
		$this->_offset = $args->offset;
	}

	public function __get($key) {
		switch ($key) {
			case 'table':
			case 'bridge':
			case 'args':
			case 'sort':
			case 'limit':
			case 'offset':
			case 'query':
			case 'result':
				return $this->{"_$key"};
		}
	}

	public function get_result() {
		if (!is_null($this->_result))
			return $this->_result;

		if ($table = $this->_database->get_table($this->table)) {
			$query = "SELECT SQL_CALC_FOUND_ROWS `$table->name`.* FROM `$table->name`";

			$joins = array();
			$where = array();
			$order = array();

			$params = array();

			if ($rel = $table->get_relation($this->bridge)) {
				$bridge = $table->name == $rel->ptable ? $rel->ftable : $rel->ptable;
				$bridge = $this->_database->get_table($bridge);

				$joins[] = "`$bridge->name` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";
			} else {
				$bridge = new database_bridge_table('');
			}

			foreach ($this->args as $field => $value) {
				if ($rel = $table->get_relation($field)) {
					if ($table->name == $rel->ptable) {
						$joins[] = "`$rel->ftable` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

						$ftable = $this->_database->get_table($rel->ftable);
						$field = "$rel->ftable`.`$ftable->pkey";
					} else {
						$joins[] = "`$rel->ptable` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

						$field = "$rel->ptable`.`$rel->pkey";
					}
				} elseif ($rel = $bridge->get_relation($field)) {
					if ($bridge->name == $rel->ptable) {
						$joins[] = "`$rel->ftable` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

						$field = "$bridge->name`.`$bridge->pkey";
					} else {
						$joins[] = "`$rel->ptable` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

						$field = "$rel->ptable`.`$rel->pkey";
					}
				}

				if (is_scalar($value)) {
					$where[] = "`$field` = ?";
					$params[] = $value;
				} elseif (is_array($value)) {
					$places = array_fill(0, count($value), '?');

					$where[] = "`$field` IN (" . implode(", ", $places) . ")";

					foreach ($value as $subvalue)
						$params[] = $subvalue;
				}
			}

			if (count($joins))
				$query .= " INNER JOIN " . implode(" INNER JOIN ", $joins);

			if (count($where))
				$query .= " WHERE " . implode(" AND ", $where);

			if (count($this->sort)) {
				foreach ($this->sort as $field => $value) {
					$value = strtoupper($value) == 'DESC' ? 'DESC' : 'ASC';
					$order[] = "`$field` $value";
				}

				$query .=  " ORDER BY " . implode(", ", $order);
			}

			if ($limit = intval($this->limit)) {
				$query .= " LIMIT ?";
				$params[] = $limit;
			}

			if ($offset = intval($this->offset)) {
				$query .= " OFFSET ?";
				$params[] = $offset;
			}

			$this->_query = $query;

			if (is_array($records = $this->_database->query($query, $params, $found)))
				$this->_result = new database_result($records, $table->pkey, $found);

			return $this->_result;
		}

		return null;
	}
}

class database_result implements Iterator, Countable, ArrayAccess {
	private $_records;
	private $_found;

	public function __construct($records, $pkey, $found) {
		//$this->_records = array_combine(array_map(function($record) use ($pkey) {
		//	return $record[$pkey];
		//}, $records), $records);

		$this->_records = $records;
		$this->_found = intval($found);
	}

	public function __get($key) {
		switch ($key) {
			case 'found':
				return $this->_found;
			case 'first':
				return count($this) ? $this->_records[0] : null;
		}
	}

	public function current() {
		return current($this->_records);
	}

	public function key() {
		return key($this->_records);
	}

	public function next() {
		next($this->_records);
	}

	public function rewind() {
		reset($this->_records);
	}

	public function valid() {
		return key($this->_records) !== null;
	}

	public function count() {
		return count($this->_records);
	}

	public function offsetGet ($key) {
		return $this->_records[$key];
	}

	public function offsetSet ($key, $value) {}

	public function offsetExists ($key) {
		return isset($this->_records[$key]);
	}

	public function offsetUnset ($key) {}

	public function walk($func, $data = null) {
		return array_walk($this->_records, $func, $data);
	}
}

class database_table {
	private $_name = null;
	private $_pkey = null;
	private $_rels = array();

	public function __construct($name, $pkey = 'id') {
		$this->_name = $name;
		$this->_pkey = $pkey;
	}

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

	public function relation_exists($name) {
		return isset($this->_rels[$name]);
	}

	public function get_relation($name) {
		return @$this->_rels[$name];
	}

	public function add_relation($name, $rel) {
		$this->_rels[$name] = $rel;
	}

	public function remove_relation($name) {
		unset($this->_rels[$name]);
	}

	public function clear_relations() {
		$this->_rels = array();
	}

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

class database_bridge_table extends database_table {
	public function __get($key) {
		switch ($key) {
			case 'join':
			case 'inner':
				return $this->_join('INNER');
			default:
				return parent::__get($key);
		}
	}

	public function select_sql($name = false, $args = array()) {
		if ($rel = $this->get_relation($name)) {
			$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
			$query = "SELECT SQL_CALC_FOUND_ROWS `$table`.* FROM $rel->join";

			if (!empty($args)) {
				$where = array();

				foreach ($args as $fkey)
					$where[] = "`$this->name`.`$fkey` = ?";

				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			return $query;
		}

		return false;
	}

	private function _join($type) {
		$type = strtoupper($type);
		$join = $this->name;

		foreach ($this->relations as $rel) {
			$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
			$join .= " $type JOIN `$table` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";
		}

		return $join;
	}
}

class database_relation {
	private $_name;

	private $_ptable;
	private $_ftable;

	private $_fkey;

	public function __construct($name, &$ptable, &$ftable, $fkey) {
		$this->_name = $name;

		$this->_ptable = $ptable;
		$this->_ftable = $ftable;

		$this->_fkey = $fkey;
	}

	public function __destruct() {
		$this->_ptable->remove_relation($this->_name);
		$this->_ftable->remove_relation($this->_name);
	}

	public function __get($key) {
		switch ($key) {
			case 'name':
			case 'fkey':
				return $this->{"_$key"};
			case 'ptable':
			case 'ftable':
				return $this->{"_$key"}->name;
			case 'pkey':
				return $this->_ptable->pkey;
			case 'join':
			case 'inner':
				return $this->_join('INNER');
			case 'left':
			case 'right':
				return $this->_join($key);
		}
	}

	private function _join($type = 'INNER') {
		$ptable = $this->_ptable->name;
		$pkey   = $this->_ptable->pkey;

		$ftable = $this->_ftable->name;
		$fkey   = $this->_fkey;

		$type = strtoupper($type);

		return "`$ptable` $type JOIN `$ftable` ON `$ptable`.`$pkey` = `$ftable`.`$fkey`";
	}
}
