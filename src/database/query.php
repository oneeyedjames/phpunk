<?php

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
		if ($table = $this->_database->get_table($this->table)) {
			$query = "SELECT SQL_CALC_FOUND_ROWS `$table->name`.*";

			$joins = array();
			$where = array();
			$order = array();

			$params = array();

			if ($rel = $table->get_relation($this->bridge)) {
				$bridge = $table->name == $rel->ptable ? $rel->ftable : $rel->ptable;
				$bridge = $this->_database->get_table($bridge);

				$query .= ", `$bridge->name`.*";

				$joins[] = "`$bridge->name` ON `$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";
			} else {
				$bridge = new database_bridge_table('');
			}

			$query .= " FROM `$table->name`";

			foreach ($this->args as $field => $value) {
				if ($rel = $table->get_relation($field)) {
					$match = "`$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

					if ($table->name == $rel->ptable) {
						$ftable = $this->_database->get_table($rel->ftable);

						$joins[] = "`$rel->ftable` ON $match";
						$field = "$rel->ftable`.`$ftable->pkey";
					} else {
						if (0 == $value) {
							$field = "$rel->ftable`.`$rel->fkey";
						} else {
							$joins[] = "`$rel->ptable` ON $match";
							$field = "$rel->ptable`.`$rel->pkey";
						}
					}
				} elseif ($rel = $bridge->get_relation($field)) {
					$match = "`$rel->ftable`.`$rel->fkey` = `$rel->ptable`.`$rel->pkey`";

					if ($bridge->name == $rel->ptable) {
						$joins[] = "`$rel->ftable` ON $match";
						$field = "$bridge->name`.`$bridge->pkey";
					} else {
						if (0 == $value) {
							$field = "$rel->ftable`.`$rel->fkey";
						} else {
							$joins[] = "`$rel->ptable` ON $match";
							$field = "$rel->ptable`.`$rel->pkey";
						}
					}
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
			$this->_result = $this->_database->query($query, $params, $this->table);

			return !is_null($this->_result);
		}

		return false;
	}

	public function reset() {
		$this->_result = null;
	}
}
