<?php

class objectTest extends PHPUnit_Framework_TestCase {
	public function testJSON() {
		$obj = new object(['foo' => 'bar']);

		$this->assertEquals('{"foo":"bar"}', json_encode($obj));
	}
}
