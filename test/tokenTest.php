<?php

use PHPUnit\Framework\TestCase;

class tokenTest extends TestCase {
	public function testCreateNonce() {
		$nonce1 = create_nonce(16);
		$nonce2 = create_nonce(16);

		$this->assertEquals(1, preg_match('/^[A-F0-9]{32}$/i', $nonce1));
		$this->assertEquals(1, preg_match('/^[A-F0-9]{32}$/i', $nonce2));

		$this->assertNotEquals($nonce1, $nonce2);
	}

	public function testCreateToken() {
		$token1 = create_token('Hello, World!', 'secret');
		$token2 = create_token('Hello, World!', 'secret');

		$this->assertEquals(1, preg_match('/^[A-F0-9]{64}$/i', $token1));
		$this->assertEquals(1, preg_match('/^[A-F0-9]{64}$/i', $token2));

		$this->assertNotEquals($token1, $token2);
		$this->assertNotEquals(substr($token1, 0, 32), substr($token2, 0, 32));
		$this->assertNotEquals(substr($token1, 32), substr($token2, 32));

		$token1 = create_token('Hello, World!', 'secret', HASH_ALGO_MD5);
		$token2 = create_token('Hello, World!', 'secret', HASH_ALGO_MD5);

		$this->assertEquals(1, preg_match('/^[A-F0-9]{64}$/i', $token1));
		$this->assertEquals(1, preg_match('/^[A-F0-9]{64}$/i', $token2));

		$this->assertNotEquals($token1, $token2);
		$this->assertNotEquals(substr($token1, 0, 32), substr($token2, 0, 32));
		$this->assertNotEquals(substr($token1, 32), substr($token2, 32));

		$token1 = create_token('Hello, World!', 'secret', HASH_ALGO_SHA1);
		$token2 = create_token('Hello, World!', 'secret', HASH_ALGO_SHA1);

		$this->assertEquals(1, preg_match('/^[A-F0-9]{80}$/i', $token1));
		$this->assertEquals(1, preg_match('/^[A-F0-9]{80}$/i', $token2));

		$this->assertNotEquals($token1, $token2);
		$this->assertNotEquals(substr($token1, 0, 40), substr($token2, 0, 40));
		$this->assertNotEquals(substr($token1, 40), substr($token2, 40));
	}

	public function testVerifyToken() {
		$token1 = create_token('Hello, World!', 'secret');
		$token2 = create_token('Hello, World!', 'secret');

		$match1 = verify_token($token1, 'Hello, World!', 'secret');
		$match2 = verify_token($token2, 'Hello, World!', 'secret');

		$this->assertTrue($match1);
		$this->assertTrue($match2);

		$token1 = create_token('Hello, World!', 'secret', HASH_ALGO_MD5);
		$token2 = create_token('Hello, World!', 'secret', HASH_ALGO_MD5);

		$match1 = verify_token($token1, 'Hello, World!', 'secret', HASH_ALGO_MD5);
		$match2 = verify_token($token2, 'Hello, World!', 'secret', HASH_ALGO_MD5);

		$this->assertTrue($match1);
		$this->assertTrue($match2);

		$token1 = create_token('Hello, World!', 'secret', HASH_ALGO_SHA1);
		$token2 = create_token('Hello, World!', 'secret', HASH_ALGO_SHA1);

		$match1 = verify_token($token1, 'Hello, World!', 'secret', HASH_ALGO_SHA1);
		$match2 = verify_token($token2, 'Hello, World!', 'secret', HASH_ALGO_SHA1);

		$this->assertTrue($match1);
		$this->assertTrue($match2);
	}
}
