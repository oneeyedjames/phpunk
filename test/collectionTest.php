<?php

class collectionObject implements collectible {
	use collection;
}

class collectionTest extends PHPUnit_Framework_TestCase {
	public function testArrayAccess() {
		$arr = new collectionObject;

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
		$arr = new collectionObject;

		$this->assertEquals(0, count($arr));

		$arr['foo'] = 'bar';
		$arr[0] = 1;
		$arr[] = true;

		$this->assertEquals(3, count($arr));
	}

	public function testIterator() {
		$arr = new collectionObject;

		for ($i = 0; $i < 10; $i++)
			$arr[$i] = $i + 1;

		foreach ($arr as $key => $value) {
			$this->assertEquals($key + 1, $value);
		}
	}
}
