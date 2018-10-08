<?php

use PHPUnit\Framework\TestCase;

class cryptTest extends TestCase {
	public function testRandomBytes() {
		$bytes1 = random_bytes(16);
		$bytes2 = random_bytes(16);

		$this->assertEquals(16, strlen($bytes1));
		$this->assertEquals(16, strlen($bytes2));

		$this->assertNotEquals($bytes1, $bytes2);
	}

	public function testPasswordHash() {
		$hash1 = password_hash('Hello, World!', PASSWORD_BCRYPT);
		$hash2 = password_hash('Hello, World!', PASSWORD_BCRYPT);

		$this->assertEquals(1, preg_match('/^\$2y\$10\$[A-Z0-9\/.]{53}$/i', $hash1));
		$this->assertEquals(1, preg_match('/^\$2y\$10\$[A-Z0-9\/.]{53}$/i', $hash2));

		$this->assertNotEquals($hash1, $hash2);
	}

	public function testPasswordVerify() {
		$hash1 = password_hash('Hello, World!', PASSWORD_BCRYPT);
		$hash2 = password_hash('Hello, World!', PASSWORD_BCRYPT);

		$this->assertTrue(password_verify('Hello, World!', $hash1));
		$this->assertTrue(password_verify('Hello, World!', $hash2));
	}
}
