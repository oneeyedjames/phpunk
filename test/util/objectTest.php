<?php

use PHPUnit\Framework\TestCase;

use PHPunk\util\object;

class objectTest extends TestCase {
	public function testJSON() {
		$obj = new object(['foo' => 'bar']);

		$this->assertEquals('{"foo":"bar"}', json_encode($obj));
	}
}
