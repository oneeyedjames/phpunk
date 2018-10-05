<?php

use PHPUnit\Framework\TestCase;

class databaseResultTest extends TestCase {
	private $result;

	public function setUp() {
		$this->result = new database_result([
			new database_record(['foo' => 'bar']),
			new database_record(['foo' => 'baz']),
			new database_record(['foo' => 'bat'])
		], 10);
	}

	public function testFound() {
		$this->assertEquals(10, $this->result->found);
	}

	public function testFirst() {
		$this->assertEquals('bar', $this->result->first->foo);
	}

	public function testMap() {
		$new_result = $this->result->map(function($record) {
			return $record->foo;
		});

		$this->assertEquals('bar', $new_result[0]);
		$this->assertEquals('baz', $new_result[1]);
		$this->assertEquals('bat', $new_result[2]);
		$this->assertEquals(10, $new_result->found);
	}

	public function testKeyMap() {
		$new_result = $this->result->key_map(function($record) {
			return $record->foo;
		});

		$this->assertFalse($this->result->has('bar'));
		$this->assertFalse($this->result->has('baz'));
		$this->assertFalse($this->result->has('bat'));

		$this->assertTrue($new_result->has('bar'));
		$this->assertTrue($new_result->has('baz'));
		$this->assertTrue($new_result->has('bat'));
		$this->assertEquals(10, $new_result->found);
	}
}
