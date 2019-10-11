<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

use PHPunk\Util\object;

class query {
	private static $_defaults = [
		'table'  => '',
		'bridge' => '',
		'args'   => [],
		'sort'   => [],
		'limit'  => 0,
		'offset' => 0
	];

	private $_database;

	private $_table;
	private $_bridge;
	private $_args = [];
	private $_sort = [];
	private $_limit = 0;
	private $_offset = 0;

	private $_query;
	private $_params;
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
			case 'params':
			case 'result':
				return $this->{"_$key"};
		}
	}

	public function get_result() {
		if (!is_null($this->_result))
			return $this->_result;

		if ($this->execute())
			return $this->_result;

		return null;
	}

	public function execute() {
		if ($this->build()) {
			$this->_result = $this->_database->query($this->query, $this->params, $this->table);

			return !is_null($this->_result);
		}

		return false;
	}

	public function build() {
		if ($table = $this->_database->get_table($this->table)) {
			$query = "SELECT SQL_CALC_FOUND_ROWS `$table->name`.*";

			$joins = [];
			$where = [];
			$order = [];

			$params = [];

			if ($rel = $table->get_relation($this->bridge)) {
				$bridge = $table->name == $rel->ptable ? $rel->ftable : $rel->ptable;
				$bridge = $this->_database->get_table($bridge);

				$query .= ", `$bridge->name`.*";

				$joins[] = "`$bridge->name` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";
			} else {
				$bridge = new bridge_table('');
			}

			$query .= " FROM `$table->name`";

			foreach ($this->args as $field => $value) {
				if ($rel = $table->get_relation($field)) {
					if ($join = $this->join_table($table, $rel, $field))
						$joins[] = $join;
				} elseif ($rel = $bridge->get_relation($field)) {
					if ($join = $this->join_table($bridge, $rel, $field))
						$joins[] = $join;
				}

				if (is_scalar($value)) {
					$where[] = "`$field` = ?";
					$params[] = $value;
				} elseif (is_array($value) && count($value)) {
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
			$this->_params = $params;

			return true;
		}

		return false;
	}

	public function reset() {
		$this->_query = null;
		$this->_params = null;
		$this->_result = null;
	}

	protected function join_table($table, $rel, &$field = null) {
		if ($table->name == $rel->ptable) {
			$ftable = $this->_database->get_table($rel->ftable);
			$field = "$ftable->name`.`$ftable->pkey";

			return "`$rel->ftable` ON $rel->match";
		} else {
			$field = "$rel->ftable`.`$rel->fkey";
		}

		return false;
	}
}
