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
}
