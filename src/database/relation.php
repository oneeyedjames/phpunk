<?php
/**
 * @package phpunk\database
 */

namespace PHPunk\Database;

class relation {
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
				return $this->_join();
			case 'left':
			case 'right':
			case 'inner':
				return $this->_join($key);
			case 'match':
				return $this->_match();
		}
	}

	private function _join($type = 'inner') {
		$type = strtoupper($type);

		return "`$this->ftable` $type JOIN `$this->ptable` ON $this->match";
	}

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
