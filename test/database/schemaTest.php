<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Database\schema;
use PHPunk\Database\table;
use PHPunk\Database\relation;

class databaseSchemaTest extends TestCase {
	public $database;

	public function setUp() {
		$this->database = new schema(null);
	}

	public function tearDown() {
		$this->database = null;
	}

	public function testTableExists() {
		$db = $this->database;

		$this->assertFalse($db->table_exists('users'));
		$this->assertFalse($db->table_exists('groups'));

		$this->assertNull($db->get_table('users'));
		$this->assertNull($db->get_table('groups'));

		$db->add_table('users');
		$db->add_table('groups');

		$this->assertTrue($db->table_exists('users'));
		$this->assertTrue($db->table_exists('groups'));

		$this->assertInstanceOf('\PHPunk\Database\table', $db->get_table('users'));
		$this->assertInstanceOf('\PHPunk\Database\table', $db->get_table('groups'));

		$db->remove_table('users');

		$this->assertFalse($db->table_exists('users'));
		$this->assertTrue($db->table_exists('groups'));

		$this->assertNull($db->get_table('users'));
		$this->assertInstanceOf('\PHPunk\Database\table', $db->get_table('groups'));

		$db->clear_tables();

		$this->assertFalse($db->table_exists('users'));
		$this->assertFalse($db->table_exists('groups'));

		$this->assertNull($db->get_table('users'));
		$this->assertNull($db->get_table('groups'));
	}

	public function testTableFields() {
		$tbl = $this->database->add_table('users');

		$this->assertSame('users', $tbl->name);
		$this->assertSame('id',    $tbl->pkey);

		$tbl = $this->database->add_table('groups', 'group_id');

		$this->assertSame('groups',   $tbl->name);
		$this->assertSame('group_id', $tbl->pkey);
	}

	public function testTableSelect() {
		$tbl1 = $this->database->add_table('users');
		$tbl2 = $this->database->add_table('groups');

		$sql1 = $tbl1->select_sql();
		$sql2 = $tbl2->select_sql();

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS * FROM `users` WHERE `id` = ?', $sql1);
		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS * FROM `groups` WHERE `id` = ?', $sql2);

		$this->assertFalse($tbl1->select_sql('user_group'));
		$this->assertFalse($tbl2->select_sql('user_group'));

		$this->database->add_relation('user_group', 'groups', 'users', 'group_id');

		$sql1 = $tbl1->select_sql('user_group');
		$sql2 = $tbl2->select_sql('user_group');

		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `groups`.* FROM `groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id` WHERE `users`.`id` = ?', $sql1);
		$this->assertSame('SELECT SQL_CALC_FOUND_ROWS `users`.* FROM `groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id` WHERE `groups`.`id` = ?', $sql2);
	}

	public function testTableInsert() {
		$tbl = $this->database->add_table('users');

		$vars = array( 'foo' => 'bar', 'baz' => 'bat' );

		$sql = $tbl->insert_sql($vars, $out);

		$this->assertSame('INSERT INTO `users` (`foo`, `baz`) VALUES (?, ?)', $sql);
		$this->assertSame(array('bar', 'bat'), $out);
	}

	public function testTableUpdate() {
		$tbl = $this->database->add_table('users');

		$vars = array( 'id'  => 1, 'foo' => 'bar', 'baz' => 'bat' );

		$sql = $tbl->update_sql($vars, $out);

		$this->assertSame('UPDATE `users` SET `foo` = ?, `baz` = ? WHERE `id` = ?', $sql);
		$this->assertSame(array('bar', 'bat', 1), $out);
	}

	public function testTableDelete() {
		$tbl1 = $this->database->add_table('users');
		$tbl2 = $this->database->add_table('groups');

		$sql1 = $tbl1->delete_sql();
		$sql2 = $tbl2->delete_sql();

		$this->assertSame('DELETE FROM `users` WHERE `id` = ?', $sql1);
		$this->assertSame('DELETE FROM `groups` WHERE `id` = ?', $sql2);

		$this->database->add_relation('user_group', 'groups', 'users', 'group_id');

		$sql1 = $tbl1->delete_sql('user_group');
		$sql2 = $tbl2->delete_sql('user_group');

		$this->assertSame('DELETE `groups`.* FROM `groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id` WHERE `users`.`id` = ?', $sql1);
		$this->assertSame('DELETE `users`.* FROM `groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id` WHERE `groups`.`id` = ?', $sql2);
	}

	public function testRelationExists() {
		$db = $this->database;
		$db->add_table('users');
		$db->add_table('groups');
		$db->add_table('roles');

		$this->assertFalse($db->relation_exists('user_group'));
		$this->assertFalse($db->relation_exists('user_role'));

		$this->assertNull($db->get_relation('user_group'));
		$this->assertNull($db->get_relation('user_role'));

		$db->add_relation('user_group', 'groups', 'users', 'group_id');
		$db->add_relation('user_role',  'roles',  'users', 'role_id');

		$this->assertTrue($db->relation_exists('user_group'));
		$this->assertTrue($db->relation_exists('user_role'));

		$this->assertInstanceOf('\PHPunk\Database\relation', $db->get_relation('user_group'));
		$this->assertInstanceOf('\PHPunk\Database\relation', $db->get_relation('user_role'));

		$db->remove_relation('user_group');

		$this->assertFalse($db->relation_exists('user_group'));
		$this->assertTrue($db->relation_exists('user_role'));

		$this->assertNull($db->get_relation('user_group'));
		$this->assertInstanceOf('\PHPunk\Database\relation', $db->get_relation('user_role'));

		$db->clear_relations();

		$this->assertFalse($db->relation_exists('user_group'));
		$this->assertFalse($db->relation_exists('user_role'));

		$this->assertNull($db->get_relation('user_group'));
		$this->assertNull($db->get_relation('user_role'));
	}

	public function testRelationFields() {
		$db = $this->database;
		$db->add_table('users');
		$db->add_table('groups');

		$rel = $db->add_relation('user_group', 'groups', 'users', 'group_id');

		$this->assertSame('user_group', $rel->name);
		$this->assertSame('groups',     $rel->ptable);
		$this->assertSame('id',         $rel->pkey);
		$this->assertSame('users',      $rel->ftable);
		$this->assertSame('group_id',   $rel->fkey);

		$this->assertSame('`groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id`', $rel->join);
		$this->assertSame('`groups` INNER JOIN `users` ON `groups`.`id` = `users`.`group_id`', $rel->inner);
		$this->assertSame('`groups` LEFT JOIN `users` ON `groups`.`id` = `users`.`group_id`',  $rel->left);
		$this->assertSame('`groups` RIGHT JOIN `users` ON `groups`.`id` = `users`.`group_id`', $rel->right);
	}
}
