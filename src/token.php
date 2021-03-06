<?php
/**
 * @package phpunk
 */

namespace PHPunk;

define('HASH_ALGO_MD5', 'md5');
define('HASH_ALGO_SHA1', 'sha1');
define('HASH_ALGO_DEFAULT', HASH_ALGO_MD5);

/**
 * Generates a random string of $length bytes.
 *
 * @param integer $length The length, in bytes, for the generated string
 * @return string The randomly generated string
 */
function create_nonce($length) {
	return bin2hex(random_bytes($length));
}

/**
 * Hashes the input data and creates a signed token with the provided key.
 *
 * @param string $data The data to be hashed
 * @param string $key The secret key to be used for signing the token
 * @param string $algo OPTIONAL The algorithm to use for hashing the input data
 * @return string The signed token
 */
function create_token($data, $key, $algo = HASH_ALGO_DEFAULT) {
	$length = strlen(hash($algo, 'hash', true));

	$salt = create_nonce($length);
	$hash = hash($algo, $data);
	$hmac = hash_hmac($algo, $hash . $salt, $key);

	return $hmac . $salt;
}

/**
 * Verifies that a signed token is valid.
 *
 * @param string $token the signed token
 * @param string $data The data to be hashed
 * @param string $key The secret key to be used for signing the token
 * @param string $algo OPTIONAL The algorithm to use for hashing the input data
 * @return boolean Whether the signed token is valid for the input data
 */
function verify_token($token, $data, $key, $algo = HASH_ALGO_DEFAULT) {
	$length = strlen(hash($algo, 'hash'));

	if (strlen($token) != $length * 2)
		return false;

	$hash = hash($algo, $data);

	list($hmac, $salt) = str_split($token, $length);

	return hash_hmac($algo, $hash . $salt, $key) == $hmac;
}
