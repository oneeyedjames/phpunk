<?php

use PHPUnit\Framework\TestCase;

use PHPunk\api_error;

class errorTest extends TestCase {
	public function testError() {
		$error = new api_error(101, 'Hello, World!');

		$this->assertEquals(101, $error->code);
		$this->assertEquals('Hello, World!', $error->message);
	}

	public function testData() {
		$error = new api_error(101, 'Hello, World!', ['foo' => 'bar']);

		$this->assertEquals('bar', $error['foo']);
		$this->assertFalse(isset($error['baz']));

		$error['baz'] = 'bat';

		$this->assertEquals('bat', $error['baz']);
		$this->assertTrue(isset($error['baz']));
	}
}
