<?php

/**
 * Backwards-compatibility functions
 */

if (!function_exists('boolval')) {
	function boolval($var) {
		return (bool)$var;
	}
}

if (!function_exists('intval')) {
	function intval($var) {
		return (int)$var;
	}
}

if (!function_exists('floatval')) {
	function floatval($var) {
		return (float)$var;
	}
}

if (!function_exists('stringval')) {
	function stringval($var) {
		return (string)$var;
	}
}

if (!function_exists('is_iterable')) {
	function is_iterable($var) {
		return is_array($var) || $var instanceof Traversable;
	}
}
