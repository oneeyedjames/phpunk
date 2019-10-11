<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Database\schema;
use PHPunk\Database\table;
use PHPunk\Database\query;

class databaseQueryTest extends TestCase {
	public function setUp() {
		$this->db = new schema(null);
		$this->db->add_table('users');
		$this->db->add_table('groups');
		$this->db->add_relation('user_group', 'groups', 'users', 'group_id');
	}

	public function tearDown() {
		unset($this->db);
	}

	public function testFieldArgs() {
		$args = [
			'table' => 'users',
			'args' => [
				'email' => 'user@domain.tld',
				'team'  => ['red', 'blue']
			]
		];

		$query = new query($this->db, $args);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `users` '
			. 'WHERE `email` = ? AND `team` IN (?, ?)', $query->query);
		$this->assertSame(3, count($query->params));
		$this->assertSame('user@domain.tld', $query->params[0]);
		$this->assertSame('red', $query->params[1]);
		$this->assertSame('blue', $query->params[2]);
	}

	public function testRelationArgs() {
		// Primary table query
		$query = new query($this->db, ['table' => 'groups', 'args' => ['user_group' => 17]]);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `groups`.* FROM `groups` '
			. 'INNER JOIN `users` ON `users`.`group_id` = `groups`.`id` '
			. 'WHERE `users`.`id` = ?', $query->query);
		$this->assertSame(1, count($query->params));
		$this->assertSame(17, $query->params[0]);

		// Foreign table query
		$query = new query($this->db, ['table' => 'users', 'args' => ['user_group' => 13]]);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `users` '
			. 'WHERE `users`.`group_id` = ?', $query->query);
		$this->assertSame(1, count($query->params));
		$this->assertSame(13, $query->params[0]);
	}

	public function testBridgeArgs() {
		// $this->db->remove_relation('user_group');
		$this->db->add_table('user_group', null);
		$this->db->add_relation('ug_user', 'users', 'user_group', 'user_id');
		$this->db->add_relation('ug_group', 'groups', 'user_group', 'group_id');

		$query = new query($this->db, ['table' => 'groups', 'bridge' => 'ug_group', 'args' => ['ug_user' => 17]]);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `groups`.*, `user_group`.* FROM `groups` '
			. 'INNER JOIN `user_group` ON `user_group`.`group_id` = `groups`.`id` '
			. 'WHERE `user_group`.`user_id` = ?', $query->query);
		$this->assertSame(1, count($query->params));
		$this->assertSame(17, $query->params[0]);
	}

	public function testOrder() {
		$args = [
			'table' => 'users',
			'sort' => ['email' => 'desc']
		];

		$query = new query($this->db, $args);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `users` ORDER BY `email` DESC', $query->query);
		$this->assertSame(0, count($query->params));
	}

	public function testLimit() {
		$args = [
			'table' => 'users',
			'limit' => 12,
			'offset' => 24
		];

		$query = new query($this->db, $args);
		$query->build();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `users` LIMIT ? OFFSET ?', $query->query);
		$this->assertSame(2, count($query->params));
		$this->assertSame(12, $query->params[0]);
		$this->assertSame(24, $query->params[1]);
	}
}
