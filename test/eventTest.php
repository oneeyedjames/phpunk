<?php

use PHPUnit\Framework\TestCase;

use PHPunk\event_manager;

class eventTest extends TestCase {
	var $manager;

	public function setUp() {
		$this->manager = new event_manager;
	}

	public function testListen() {
		$fired = false;

		$this->manager->listen('test', function() use (&$fired) {
			$fired = true;
		});

		$this->assertFalse($fired);

		$this->manager->trigger('test');

		$this->assertTrue($fired);
	}

	public function testArgs() {
		$args = null;

		$this->manager->listen('test', function() use (&$args) {
			$args = func_get_args();
		});

		$this->assertEquals(null, $args);

		$this->manager->trigger('test', 'foo', 'bar');

		$this->assertEquals('foo', $args[0]);
		$this->assertEquals('bar', $args[1]);
	}
}
