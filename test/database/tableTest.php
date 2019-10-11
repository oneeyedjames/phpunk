<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Database\table;
use PHPunk\Database\bridge_table;
use PHPunk\Database\relation;

class databaseTableTest extends TestCase {
	public function setUp() {
		$this->users  = new table('users');
		$this->groups = new table('groups', 'group_id');
		$this->bridge = new table('user_group', ['user_id', 'group_id']);
	}

	public function tearDown() {
		unset($this->users);
		unset($this->groups);
		unset($this->bridge);
	}

	public function testFields() {
		$this->assertSame('users', $this->users->name);
		$this->assertSame('id',    $this->users->pkey);
		$this->assertSame('`users`.`id` = ?', $this->users->where);

		$this->assertSame('groups',   $this->groups->name);
		$this->assertSame('group_id', $this->groups->pkey);
		$this->assertSame('`groups`.`group_id` = ?', $this->groups->where);

		$this->assertSame('user_group', $this->bridge->name);
		$this->assertSame(['user_id', 'group_id'], $this->bridge->pkey);
		$this->assertSame('`user_group`.`user_id` = ? AND `user_group`.`group_id` = ?', $this->bridge->where);
	}

	public function testSelectSQL() {
		$sql1 = 'SELECT SQL_CALC_FOUND_ROWS * FROM `users` WHERE `users`.`id` = ?';
		$sql2 = 'SELECT SQL_CALC_FOUND_ROWS * FROM `groups` WHERE `groups`.`group_id` = ?';
		$sql3 = 'SELECT SQL_CALC_FOUND_ROWS * FROM `user_group` WHERE `user_group`.`user_id` = ? AND `user_group`.`group_id` = ?';

		$this->assertSame($sql1, $this->users->select_sql());
		$this->assertSame($sql2, $this->groups->select_sql());
		$this->assertSame($sql3, $this->bridge->select_sql());
	}

	public function testInsertSQL() {
		$sql1 = 'INSERT INTO `users` (`field`) VALUES (?)';
		$sql2 = 'INSERT INTO `groups` (`field`) VALUES (?)';
		$sql3 = 'INSERT INTO `user_group` (`field`) VALUES (?)';

		$record = ['field' => 'value'];

		$this->assertSame($sql1, $this->users->insert_sql($record, $params));
		$this->assertSame($sql2, $this->groups->insert_sql($record, $params));
		$this->assertSame($sql3, $this->bridge->insert_sql($record, $params));
	}

	public function testUpdateSQL() {
		$sql1 = 'UPDATE `users` SET `field` = ? WHERE `users`.`id` = ?';
		$sql2 = 'UPDATE `groups` SET `field` = ? WHERE `groups`.`group_id` = ?';
		$sql3 = 'UPDATE `user_group` SET `field` = ? WHERE `user_group`.`user_id` = ? AND `user_group`.`group_id` = ?';

		$this->assertSame($sql1, $this->users->update_sql(['id' => 17, 'field' => 'value'], $params));
		$this->assertSame($sql2, $this->groups->update_sql(['group_id' => 13, 'field' => 'value'], $params));
		$this->assertSame($sql3, $this->bridge->update_sql(['user_id' => 17, 'group_id' => 13, 'field' => 'value'], $params));
	}

	public function testDeleteSQL() {
		$sql1 = 'DELETE FROM `users` WHERE `users`.`id` = ?';
		$sql2 = 'DELETE FROM `groups` WHERE `groups`.`group_id` = ?';
		$sql3 = 'DELETE FROM `user_group` WHERE `user_group`.`user_id` = ? AND `user_group`.`group_id` = ?';

		$this->assertSame($sql1, $this->users->delete_sql());
		$this->assertSame($sql2, $this->groups->delete_sql());
		$this->assertSame($sql3, $this->bridge->delete_sql());
	}
}
