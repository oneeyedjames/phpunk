<?php

use PHPUnit\Framework\TestCase;

class cryptTest extends TestCase {
	const CLEAR_TEXT = 'Hello, World!';

	public function testRandomBytes() {
		$bytes1 = _random_bytes(16);
		$bytes2 = _random_bytes(16);

		$this->assertEquals(16, strlen($bytes1));
		$this->assertEquals(16, strlen($bytes2));

		$this->assertNotEquals($bytes1, $bytes2);
	}

	public function testHashEquals() {
		$hash1 = _password_hash(self::CLEAR_TEXT . ' A', PASSWORD_BCRYPT);
		$hash2 = _password_hash(self::CLEAR_TEXT . ' B', PASSWORD_BCRYPT);

		$this->assertTrue(_hash_equals($hash1, $hash1));
		$this->assertTrue(_hash_equals($hash2, $hash2));
		$this->assertFalse(_hash_equals($hash1, $hash2));
		$this->assertFalse(_hash_equals($hash2, $hash1));
	}

	public function testPasswordHash() {
		$hash1 = _password_hash(self::CLEAR_TEXT, PASSWORD_BCRYPT);
		$hash2 = _password_hash(self::CLEAR_TEXT, PASSWORD_BCRYPT);

		$this->assertEquals(1, preg_match('/^\$2y\$10\$[A-Z0-9\/.]{53}$/i', $hash1));
		$this->assertEquals(1, preg_match('/^\$2y\$10\$[A-Z0-9\/.]{53}$/i', $hash2));

		$this->assertNotEquals($hash1, $hash2);
	}

	public function testPasswordVerify() {
		$hash1 = _password_hash(self::CLEAR_TEXT, PASSWORD_BCRYPT);
		$hash2 = _password_hash(self::CLEAR_TEXT, PASSWORD_BCRYPT);

		$this->assertTrue(_password_verify(self::CLEAR_TEXT, $hash1));
		$this->assertTrue(_password_verify(self::CLEAR_TEXT, $hash2));
	}
}
