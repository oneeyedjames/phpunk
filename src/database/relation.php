<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

/**
 * @property string $name The name of the database relationship
 * @property object $ptable The primary database table
 * @property object $ftable The foreign database table
 * @property string $pkey The primary key field
 * @property string $fkey The foreign key field
 * @property string $join Alias for the INNER JOIN clause
 * @property string $inner The INNER JOIN clause for this relationship
 * @property string $left The LEFT JOIN clause for this relationship
 * @property string $right The RIGHT JOIN clause for this relationship
 */
class relation {
	/**
	 * @ignore internal variable
	 */
	private $_name;

	/**
	 * @ignore internal variable
	 */
	private $_ptable;

	/**
	 * @ignore internal variable
	 */
	private $_ftable;

	/**
	 * @ignore internal variable
	 */
	private $_fkey;

	/**
	 * @param string $name The name of the database relationship
	 * @param object $ptable The primary database table
	 * @param object $ftable The foreign database table
	 * @param string $fkey The foreign key field
	 */
	public function __construct($name, &$ptable, &$ftable, $fkey) {
		$this->_name = $name;

		$this->_ptable = $ptable;
		$this->_ftable = $ftable;

		$this->_fkey = $fkey;
	}

	/**
	 * @ignore magic method
	 */
	public function __destruct() {
		$this->_ptable->remove_relation($this->_name);
		$this->_ftable->remove_relation($this->_name);
	}

	/**
	 * @ignore magic method
	 */
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
				return $this->_join();
			case 'left':
			case 'right':
			case 'inner':
				return $this->_join($key);
			case 'match':
				return $this->_match();
		}
	}

	/**
	 * @ignore internal method
	 */
	private function _join($type = 'inner') {
		$type = strtoupper($type);

		return "`$this->ftable` $type JOIN `$this->ptable` ON $this->match";
	}

	/**
	 * @ignore internal method
	 */
	private function _match() {
		if (is_scalar($this->pkey) && is_scalar($this->fkey)) {
			return "`$this->ftable`.`$this->fkey` = `$this->ptable`.`$this->pkey`";
		} elseif (is_array($this->pkey) && is_array($this->fkey)) {
			$match = [];

			foreach ($this->pkey as $index => $field) {
				$match[] = "`$this->ftable`.`{$this->fkey[$index]}` = `$this->ptable`.`$field`";
			}

			return implode(" AND ", $match);
		}

		return false;
	}
}
