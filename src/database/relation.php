<?php
/**
 * @package phpunk\database
 */

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
