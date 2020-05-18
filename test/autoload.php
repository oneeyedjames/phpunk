<?php

require_once 'src/index.php';

class MySqlMock {
	const MYSQL_ERROR = '';

	public $result;
	private $count;

	public function __construct($result, $count = false) {
		$this->result = $result;
		$this->count = $count ? intval($count) : count($result);
	}

	public function __get($key) {
		switch ($key) {
			case 'insert_id':
				return 13;
			case 'error':
				return self::MYSQL_ERROR;
			default:
				return null;
		}
	}

	public function prepare($sql) {
		return new MySqlStmtMock($this->result);
	}

	public function query($sql) {
		if ($sql == 'SELECT FOUND_ROWS()')
			return new MySqlResultMock([[$this->count]]);

		return new MySqlResultMock($this->result);
	}
}

class MySqlStmtMock {
	const MYSQL_ERROR = '';

	private $result;

	public function __construct($result) {
		$this->result = $result;
	}

	public function __get($key) {
		switch ($key) {
			case 'error':
				return self::MYSQL_ERROR;
			default:
				return null;
		}
	}

	public function execute() { return true; }

	public function get_result() {
		return new MySqlResultMock($this->result);
	}

	public function close() {}
}

class MySqlResultMock {
	private $result;
	private $index = 0;

	public function __construct($result) {
		$this->result = $result;
	}

	function fetch_assoc() {
		return @$this->result[$this->index++];
	}

	function fetch_row() {
		return @array_values($this->result[$this->index++]);
	}

	function free() {}
}
