<?php
/**
 * Backwards-compatibility functions and constants
 *
 * @package phpunk\compat\crypto
 */

defined('PASSWORD_BCRYPT')  or define('PASSWORD_BCRYPT',  1);
defined('PASSWORD_DEFAULT') or define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

if (!function_exists('random_bytes')) {
	/**
	 * Generates an arbitrary length string of cryptographic random bytes that are suitable for cryptographic use, such as when generating salts, keys or initialization vectors.
	 * Triggers error if no crytpographically secure random number generator can be found.
	 *
	 * @param integer $length The length of the random string that should be returned in bytes.
	 * @return string Returns a string containing the requested number of cryptographically secure random bytes.
	 */
	function random_bytes($length) {
		return _random_bytes($length);
	}
}

if (!function_exists('hash_equals')) {
	/**
	 * Compares two strings using the same time whether they're equal or not.
	 * This function should be used to mitigate timing attacks; for instance, when testing crypt() password hashes.
	 *
	 * @param string $hash1 The string of known length to compare against
	 * @param string $hash2 The user-supplied string
	 */
	function hash_equals($hash1, $hash2) {
		return _hash_equals($hash1, $hash2);
	}
}

if (!function_exists('password_hash')) {
	/**
	 * Creates a new password hash using a strong one-way hashing algorithm.
	 * The following algorithms are supported:
	 * * **PASSWORD_DEFAULT** - Use the bcrypt algorithm
	 * * **PASSWORD_BCRYPT** - Use the CRYPT_BLOWFISH algorithm to create the hash. This will produce a standard crypt() compatible hash using the "$2y$" identifier. The result will always be a 60 character string, or FALSE on failure.
	 *
	 * Supported options for **PASSWORD_BCRYPT**:
	 * * **salt** - to manually provide a salt to use when hashing the password. If omitted, a random salt will be generated.
	 * * **cost** - which denotes the algorithmic cost that should be used. If omitted, a default value of 10 will be used.
	 *
	 * @param string $password The user's password
	 * @param integer $algo A password algorithm constant denoting the algorithm to use when hashing the password
	 * @param array $options
	 * @return string Returns the hashed password, or FALSE on failure.
	 */
	function password_hash($password, $algo, $options = []) {
		return _password_hash($password, $algo, $options);
	}
}

if (!function_exists('password_verify')) {
	/**
	 * Verifies that the given hash matches the given password.
	 *
	 * @param string $password The user's password
	 * @param string $hash A hash created by password_hash()
	 * @return boolean Returns TRUE if the password and hash match, or FALSE otherwise.
	 */
	function password_verify($password, $hash) {
		return _password_verify($password, $hash);
	}
}

/**
 * @ignore internal function
 */
function _random_bytes($length) {
	if (function_exists('openssl_random_pseudo_bytes'))
		return openssl_random_pseudo_bytes($length);
	elseif (function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM'))
		return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);

	trigger_error('No available CSPRNG could be found.', E_USER_ERROR);

	return str_pad('', $length, chr(0));
}

 /**
  * @ignore internal function
  */
function _hash_equals($hash1, $hash2) {
	if (strlen($hash1) !== strlen($hash2)) return false;

	$bin = $hash1 ^ $hash2;
	$ret = 0;

	for ($i = 0, $n = strlen($bin); $i < $n; $i++)
		$ret |= ord($bin[$i]);

	return $ret === 0;
}

/**
  * @ignore internal function
  */
function _password_hash($password, $algo, $options = []) {
	extract($options, EXTR_SKIP);

	if (!isset($salt)) {
		$cost = isset($cost) ? sprintf('%02d', $cost) : '10';
		$salt = '$2y$' . $cost . '$' . str_replace(['+', '='],
			['.', ''], base64_encode(random_bytes(16)));
	}

	return crypt($password, $salt);
}

 /**
  * @ignore internal function
  */
function _password_verify($password, $hash) {
	return hash_equals($hash, crypt($password, $hash));
}
