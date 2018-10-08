<?php

namespace PHPunk;

/**
 * Basic reference implementation(s). Private key signing.
 */

function create_nonce($length) {
	return bin2hex(random_bytes($length));
}

function create_token($data, $key, $algo = 'md5') {
	$length = strlen(hash($algo, 'hash', true));

	$salt = create_nonce($length);
	$hash = hash($algo, $data);
	$hmac = hash_hmac($algo, $hash . $salt, $key);

	return $hmac . $salt;
}

function verify_token($token, $data, $key, $algo = 'md5') {
	$length = strlen(hash($algo, 'hash'));

	if (strlen($token) != $length * 2)
		return false;

	$hash = hash($algo, $data);

	list($hmac, $salt) = str_split($token, $length);

	return hash_hmac($algo, $hash . $salt, $key) == $hmac;
}
