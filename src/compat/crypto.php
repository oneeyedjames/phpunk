<?php

/**
 * Backwards-compatibility functions and constants
 */

if (!function_exists('random_bytes')) {
	function random_bytes($length) {
		if (function_exists('openssl_random_pseudo_bytes'))
			return openssl_random_pseudo_bytes($length);
		elseif (function_exists('mcrypt_create_iv') && defined('MCRYPT_DEV_URANDOM'))
			return mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);

		trigger_error('No available CSPRNG could be found.', E_USER_ERROR);

		return str_pad('', $length, chr(0));
	}
}

defined('PASSWORD_BCRYPT')  or define('PASSWORD_BCRYPT',  1);
defined('PASSWORD_DEFAULT') or define('PASSWORD_DEFAULT', PASSWORD_BCRYPT);

if (!function_exists('password_hash')) {
	function password_hash($password, $algo, $options = array()) {
		extract($options, EXTR_SKIP);

		if (!isset($salt)) {
			$cost = isset($cost) ? sprintf('%02d', $cost) : '10';
			$salt = '$2y$' . $cost . '$' . str_replace(array('+', '='),
				array('.', ''), base64_encode(random_bytes(16)));
		}

		return crypt($password, $salt);
	}
}

if (!function_exists('password_verify')) {
	function password_verify($password, $hash) {
		return crypt($password, $hash) == $hash;
	}
}
