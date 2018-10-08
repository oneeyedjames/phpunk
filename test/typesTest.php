<?php

use PHPUnit\Framework\TestCase;

class typesTest extends TestCase {
	public function testBoolVal() {
		$this->assertSame(false, boolval(0));
		$this->assertSame(true, boolval(1));
		$this->assertSame(true, boolval(-1));

		$this->assertSame(false, boolval(0.0));
		$this->assertSame(true, boolval(1.0));
		$this->assertSame(true, boolval(0.1));
		$this->assertSame(true, boolval(-1.0));
		$this->assertSame(true, boolval(-0.1));

		$this->assertSame(false, boolval(''));
		$this->assertSame(true, boolval(' '));
		$this->assertSame(true, boolval('.'));

		$this->assertSame(true, boolval(new object()));
		$this->assertSame(false, boolval(null));
	}

	public function testIntVal() {
		$this->assertSame(0, intval(false));
		$this->assertSame(1, intval(true));

		$this->assertSame(0, intval(0.0));
		$this->assertSame(0, intval(0.1));
		$this->assertSame(0, intval(0.0));
		$this->assertSame(1, intval(1.0));
		$this->assertSame(1, intval(1.1));
		$this->assertSame(1, intval(1.9));
		$this->assertSame(-1, intval(-1.0));
		$this->assertSame(-1, intval(-1.1));
		$this->assertSame(-1, intval(-1.9));

		$this->assertSame(127, intval('127'));
		$this->assertSame(0, intval('0x127'));
		$this->assertSame(0, intval(''));
	}

	public function testStringVal() {
		$this->assertSame('', stringval(false));
		$this->assertSame('1', stringval(true));

		$this->assertSame('0', stringval(0));
		$this->assertSame('1', stringval(1));
		$this->assertSame('-1', stringval(-1));

		$this->assertSame('0', stringval(0.0));
		$this->assertSame('0.1', stringval(0.1));
		$this->assertSame('1', stringval(1.0));
		$this->assertSame('-0.1', stringval(-0.1));
		$this->assertSame('-1', stringval(-1.0));
	}

	public function testIsIterable() {
		$this->assertFalse(is_iterable(false));
		$this->assertFalse(is_iterable(true));

		$this->assertFalse(is_iterable(0));
		$this->assertFalse(is_iterable(1));
		$this->assertFalse(is_iterable(-1));

		$this->assertFalse(is_iterable(0.0));
		$this->assertFalse(is_iterable(1.1));
		$this->assertFalse(is_iterable(-1.1));

		$this->assertFalse(is_iterable(''));
		$this->assertFalse(is_iterable(' '));
		$this->assertFalse(is_iterable('.'));

		$this->assertTrue(is_iterable(array()));
		$this->assertTrue(is_iterable(new object()));

		$this->assertFalse(is_iterable((object) array()));
	}
}
