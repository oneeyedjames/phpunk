<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

/**
 * @property string $join Alias for the INNER JOIN clause
 * @property string $inner The INNER JOIN clause for this bridge table
 */
class bridge_table extends table {
	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'join':
			case 'inner':
				return $this->_join('INNER');
			default:
				return parent::__get($key);
		}
	}

	/**
	 * Generates a SELECT query string, based on the given foreign keys
	 * @param string $name OPTIONAL Name of the related database table to query
	 * @param mixed $args Array or iterable object of foreign key values
	 * @return string A parameterized SQL query
	 */
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

	/**
	 * @ignore internal method
	 */
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
