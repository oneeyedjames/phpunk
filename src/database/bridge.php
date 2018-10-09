<?php
/**
 * @package phpunk\database
 */

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
