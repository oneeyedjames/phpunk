<?php

use PHPUnit\Framework\TestCase;

use PHPunk\Collection\collection;

class collectionTest extends TestCase {
	public function testJSON() {
		$obj = new collection(['foo' => 'bar']);

		$this->assertEquals('{"foo":"bar"}', json_encode($obj));
	}
}
