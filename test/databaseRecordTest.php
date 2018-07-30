<?php

class databaseRecordTest extends PHPUnit_Framework_TestCase {
	private $record;

	public function setUp() {
		$this->record = new database_record([
			'foo' => 'bar',
			'baz' => 'bat'
		], 'test_table');
	}

	public function testTable() {
		$new_record = new database_record;

		$this->assertEquals('test_table', $this->record->table);
		$this->assertFalse($new_record->table);
	}

	public function testCount() {
		$this->assertEquals(2, count($this->record));
	}

	public function testHas() {
		$this->assertTrue(isset($this->record['foo']));
		$this->assertTrue(isset($this->record['baz']));

		$this->assertTrue(isset($this->record->foo));
		$this->assertTrue(isset($this->record->baz));

		$this->assertTrue($this->record->has('foo'));
		$this->assertTrue($this->record->has('baz'));
	}

	public function testGet() {
		$this->assertEquals('bar', $this->record['foo']);
		$this->assertEquals('bat', $this->record['baz']);

		$this->assertEquals('bar', $this->record->foo);
		$this->assertEquals('bat', $this->record->baz);

		$this->assertEquals('bar', $this->record->get('foo'));
		$this->assertEquals('bat', $this->record->get('baz'));
	}

	public function testGetDefault() {
		$this->assertFalse($this->record->has('non'));

		$this->assertNull($this->record->get('non'));
		$this->assertEquals('def', $this->record->get('non', 'def'));

		$this->assertNull($this->record->non);
		$this->assertEquals('def', $this->record->non('def'));
	}

	public function testPut() {
		$this->assertEquals('bar', $this->record['foo']);
		$this->record['foo'] = 'baz';
		$this->assertEquals('baz', $this->record['foo']);

		$this->assertEquals('baz', $this->record->foo);
		$this->record->foo = 'bat';
		$this->assertEquals('bat', $this->record->foo);

		$this->assertEquals('bat', $this->record->get('foo'));
		$this->record->put('foo', 'bar');
		$this->assertEquals('bar', $this->record->get('foo'));
	}

	public function testRemove() {
		$this->record->put('bar', 1);
		$this->record->put('bat', 2);

		$this->assertTrue($this->record->has('foo'));
		$this->assertTrue($this->record->has('baz'));
		$this->assertTrue($this->record->has('bar'));
		$this->assertTrue($this->record->has('bat'));

		unset($this->record['bat']);

		$this->assertTrue($this->record->has('foo'));
		$this->assertTrue($this->record->has('baz'));
		$this->assertTrue($this->record->has('bar'));
		$this->assertFalse($this->record->has('bat'));

		unset($this->record->bar);

		$this->assertTrue($this->record->has('foo'));
		$this->assertTrue($this->record->has('baz'));
		$this->assertFalse($this->record->has('bar'));
		$this->assertFalse($this->record->has('bat'));

		$this->record->remove('baz');

		$this->assertTrue($this->record->has('foo'));
		$this->assertFalse($this->record->has('baz'));
		$this->assertFalse($this->record->has('bar'));
		$this->assertFalse($this->record->has('bat'));
	}
}
