<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Database\table;
use PHPunk\Database\bridge_table;
use PHPunk\Database\relation;

class databaseBridgeTest extends TestCase {
	public function setUp() {
		$users  = new table('users');
		$groups = new table('groups');

		$this->bridge = new bridge_table('user_groups', ['user_id', 'group_id']);
		$this->bridge->add_relation('user', new relation('ug_user', $users, $this->bridge, 'user_id'));
		$this->bridge->add_relation('group', new relation('ug_group', $groups, $this->bridge, 'group_id'));
	}

	public function tearDown() {
		unset($this->bridge);
	}

	public function testJoin() {
		$join = "`user_groups` INNER JOIN `users` ON `user_groups`.`user_id` = `users`.`id` "
			. "INNER JOIN `groups` ON `user_groups`.`group_id` = `groups`.`id`";

		$this->assertSame($join, $this->bridge->join);
	}

	public function testSelectSQL() {
		$sql = 'SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `user_groups` INNER JOIN `users` '
			. 'ON `user_groups`.`user_id` = `users`.`id` WHERE `user_groups`.`group_id` = ?';

		$this->assertSame($sql, $this->bridge->select_sql('user', ['group_id']));
	}
}
