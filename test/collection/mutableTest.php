<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Collection\mutable;

class mutableObject {
	use mutable;

	public function __construct($data = []) {
		$this->load($data);
	}
}

class mutableTest extends TestCase {
	public function testHas() {
		$obj = new mutableObject(['foo' => 'bar']);

		$this->assertTrue($obj->has('foo'));
		$this->assertFalse($obj->has('baz'));

		$this->assertTrue(isset($obj->foo));
		$this->assertFalse(isset($obj->baz));
	}

	public function testGet() {
		$obj = new mutableObject(['foo' => 'bar']);

		$this->assertEquals('bar', $obj->get('foo'));
		$this->assertEquals('bar', $obj->get('foo', 'baz'));
		$this->assertEquals('bar', $obj->foo);
		$this->assertEquals('bar', $obj->foo('baz'));

		$this->assertFalse($obj->has('baz'));
		$this->assertEquals('bat', $obj->get('baz', 'bat'));
		$this->assertEquals('bat', $obj->baz('bat'));
		$this->assertNull($obj->baz);
	}

	public function testPut() {
		$obj = new mutableObject;

		$this->assertFalse($obj->has('foo'));
		$this->assertFalse($obj->has('baz'));

		$obj->put('foo', 'bar');
		$obj->baz = 'bat';

		$this->assertTrue($obj->has('foo'));
		$this->assertTrue($obj->has('baz'));
		$this->assertEquals('bar', $obj->get('foo'));
		$this->assertEquals('bat', $obj->get('baz'));
	}

	public function testRemove() {
		$obj = new mutableObject(['foo' => 'bar', 'baz' => 'bat']);

		$this->assertTrue($obj->has('foo'));
		$this->assertTrue($obj->has('baz'));
		$this->assertEquals('bar', $obj->get('foo'));
		$this->assertEquals('bat', $obj->get('baz'));

		$obj->remove('foo');
		unset($obj->baz);

		$this->assertFalse($obj->has('foo'));
		$this->assertFalse($obj->has('baz'));
	}
}
