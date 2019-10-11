<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

class bridge_table extends table {
	public function __get($key) {
		switch ($key) {
			case 'join':
				return $this->_join();
			case 'left':
			case 'right':
			case 'inner':
				return $this->_join($key);
			default:
				return parent::__get($key);
		}
	}

	public function select_sql($name = false, $args = []) {
		if ($rel = $this->get_relation($name)) {
			$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;
			$query = "SELECT SQL_CALC_FOUND_ROWS `$table`.* FROM $rel->join";

			if (!empty($args)) {
				$where = [];

				foreach ($args as $fkey)
					$where[] = "`$this->name`.`$fkey` = ?";

				$query .= ' WHERE ' . implode(' AND ', $where);
			}

			return $query;
		}

		return false;
	}

	private function _join($type = 'inner') {
		$type = strtoupper($type);
		$join = "`$this->name`";

		foreach ($this->relations as $rel) {
			$table = $this->name != $rel->ptable ? $rel->ptable : $rel->ftable;

			$join .= " $type JOIN `$table` ON $rel->match";
		}

		return $join;
	}
}
