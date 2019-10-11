<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

class table {
	private $_name = null;
	private $_pkey = null;
	private $_rels = [];

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
			case 'where':
				return $this->_where();
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
		$this->_rels = [];
	}

	public function select_sql($name = false) {
		if ($name) {
			if ($rel = $this->get_relation($name)) {
				$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
				return "SELECT SQL_CALC_FOUND_ROWS `$table`.* FROM $rel->join WHERE $this->where";
			}

			return false;
		}

		return "SELECT SQL_CALC_FOUND_ROWS * FROM `$this->name` WHERE $this->where";
	}

	public function insert_sql($record, &$params) {
		$fields = [];

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
		$fields = [];

		foreach ($record as $field => $value) {
			if (!$this->is_pkey($field)) {
				$fields[] = "`$field` = ?";
				$params[] = $value;
			}
		}

		$fields = implode(", ", $fields);

		if (is_scalar($this->pkey)) {
			$params[] = $record[$this->pkey];
		} elseif (is_array($this->pkey)) {
			foreach ($this->pkey as $field) {
				$params[] = $record[$field];
			}
		}

		return "UPDATE `$this->name` SET $fields WHERE $this->where";
	}

	public function delete_sql($name = false) {
		if ($name) {
			if ($rel = $this->get_relation($name)) {
				$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
				return "DELETE `$table`.* FROM $rel->join WHERE $this->where";
			}

			return false;
		}

		return "DELETE FROM `$this->name` WHERE $this->where";
	}

	private function is_pkey($field) {
		return is_array($this->pkey)
			? in_array($field, $this->pkey)
			: $field == $this->pkey;
	}

	private function _where() {
		if (is_array($this->pkey)) {
			$fields = [];

			foreach ($this->pkey as $field)
				$fields[] = "`$this->name`.`$field` = ?";

			return implode(" AND ", $fields);
		}

		return "`$this->name`.`$this->pkey` = ?";
	}
}
