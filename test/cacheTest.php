<?php

use PHPUnit\Framework\TestCase;

class cacheTest extends TestCase {
	private $cache;

	public function setUp() {
		$this->cache = new cache;
	}

	public function testExists() {
		$c = $this->cache;

		$this->assertFalse($c->has('foo', 1));
		$this->assertFalse($c->has('foo'));

		$c->put('foo', 1, 'bar');

		$this->assertTrue($c->has('foo', 1));
		$this->assertTrue($c->has('foo'));

		$c->remove('foo', 1);

		$this->assertFalse($c->has('foo', 1));
		$this->assertTrue($c->has('foo'));

		$c->remove('foo');

		$this->assertFalse($c->has('foo', 1));
		$this->assertFalse($c->has('foo'));
	}

	public function testEmpty() {
		$c = $this->cache;

		$this->assertEmpty($c->get('foo', 1));

		$c->put('foo', 1, 'bar');

		$this->assertNotEmpty($c->get('foo', 1));

		$c->remove('foo', 1);

		$this->assertEmpty($c->get('foo', 1));
	}

	public function testType() {
		$c = $this->cache;

		$c->put('foo', 1, 'bar');
		$c->put('baz', 1, 'bar');

		$this->assertEquals('bar', $c->get('foo', 1));
		$this->assertEquals('bar', $c->get('baz', 1));

		$c->put('baz', 1, 'bat');

		$this->assertEquals('bar', $c->get('foo', 1));
		$this->assertEquals('bat', $c->get('baz', 1));
	}
}
