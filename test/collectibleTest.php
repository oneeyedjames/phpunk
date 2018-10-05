<?php

use PHPUnit\Framework\TestCase;

class collectibleObject implements collection {
	use collectible;

	public function __construct($data = []) {
		$this->loadArray($data);
	}
}

class collectibleTest extends TestCase {
	public function testArrayAccess() {
		$arr = new collectibleObject;

		$this->assertFalse(isset($arr['foo']));
		$this->assertEmpty($arr['foo']);

		$arr['foo'] = 'bar';

		$this->assertTrue(isset($arr['foo']));
		$this->assertEquals('bar', $arr['foo']);

		unset($arr['foo']);

		$this->assertFalse(isset($arr['foo']));
		$this->assertEmpty($arr['foo']);
	}

	public function testCountable() {
		$arr = new collectibleObject;

		$this->assertEquals(0, count($arr));

		$arr['foo'] = 'bar';
		$arr[0] = 1;
		$arr[] = true;

		$this->assertEquals(3, count($arr));
	}

	public function testIterator() {
		$arr = new collectibleObject;

		for ($i = 0; $i < 10; $i++)
			$arr[$i] = $i + 1;

		foreach ($arr as $key => $value) {
			$this->assertEquals($key + 1, $value);
		}
	}

	public function testSort() {
		$arr = new collectibleObject([7, 3, 5]);

		$this->assertEquals(7, $arr[0]);
		$this->assertEquals(3, $arr[1]);
		$this->assertEquals(5, $arr[2]);

		$arr->sort();

		$this->assertEquals(3, $arr[0]);
		$this->assertEquals(5, $arr[1]);
		$this->assertEquals(7, $arr[2]);

		$arr->sort(true);

		$this->assertEquals(7, $arr[0]);
		$this->assertEquals(5, $arr[1]);
		$this->assertEquals(3, $arr[2]);
	}

	public function testSortAssoc() {
		$arr = new collectibleObject([7, 3, 5]);
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[0]);
		$this->assertEquals(3, $arr[1]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(0, $keys[0]);
		$this->assertEquals(1, $keys[1]);
		$this->assertEquals(2, $keys[2]);

		$arr->sort(false, 'a');
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[0]);
		$this->assertEquals(3, $arr[1]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(1, $keys[0]);
		$this->assertEquals(2, $keys[1]);
		$this->assertEquals(0, $keys[2]);

		$arr->sort(true, 'a');
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[0]);
		$this->assertEquals(3, $arr[1]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(0, $keys[0]);
		$this->assertEquals(2, $keys[1]);
		$this->assertEquals(1, $keys[2]);
	}

	public function testSortKeyed() {
		$arr = new collectibleObject([1 => 7, 4 => 3, 2 => 5]);
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[1]);
		$this->assertEquals(3, $arr[4]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(1, $keys[0]);
		$this->assertEquals(4, $keys[1]);
		$this->assertEquals(2, $keys[2]);

		$arr->sort(false, 'k');
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[1]);
		$this->assertEquals(3, $arr[4]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(1, $keys[0]);
		$this->assertEquals(2, $keys[1]);
		$this->assertEquals(4, $keys[2]);

		$arr->sort(true, 'k');
		$keys = $arr->keys();

		$this->assertEquals(7, $arr[1]);
		$this->assertEquals(3, $arr[4]);
		$this->assertEquals(5, $arr[2]);
		$this->assertEquals(4, $keys[0]);
		$this->assertEquals(2, $keys[1]);
		$this->assertEquals(1, $keys[2]);
	}

	public function testWalk() {
		$arr = new collectibleObject([1, 2, 3]);

		$this->assertEquals(1, $arr[0]);
		$this->assertEquals(2, $arr[1]);
		$this->assertEquals(3, $arr[2]);

		$arr->walk(function(&$value, $key) {
			$value = $key;
		});

		$this->assertEquals(0, $arr[0]);
		$this->assertEquals(1, $arr[1]);
		$this->assertEquals(2, $arr[2]);
	}

	public function testKeysAndValues() {
		$arr = new collectibleObject(['foo' => 'bar', 'baz' => 'bat']);

		$keys   = $arr->keys();
		$values = $arr->values();

		$this->assertTrue(is_array($keys));
		$this->assertTrue(is_array($values));

		$this->assertEquals(2, count($keys));
		$this->assertEquals(2, count($values));

		$this->assertEquals('foo', $keys[0]);
		$this->assertEquals('baz', $keys[1]);
		$this->assertEquals('bar', $values[0]);
		$this->assertEquals('bat', $values[1]);
	}
}
