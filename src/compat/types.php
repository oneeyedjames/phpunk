<?php
/**
 * Backwards-compatibility functions
 *
 * @package phpunk\compat
 */

if (!function_exists('boolval')) {
	/**
	 * Returns the boolean value of var.
	 *
	 * @param mixed $var The scalar value being converted to a boolean
	 * @return boolean The boolean value of <b>var</b>
	 */
	function boolval($var) {
		return (bool)$var;
	}
}

if (!function_exists('intval')) {
	/**
	 * Returns the integer value of var.
	 *
	 * @param mixed $var The scalar value being converted to an integer
	 * @return boolean The integer value of <b>var</b>
	 */
	function intval($var) {
		return (int)$var;
	}
}

if (!function_exists('floatval')) {
	/**
	 * Returns the float value of var.
	 *
	 * @param mixed $var The scalar value being converted to a float
	 * @return float The float value of <b>var</b>
	 */
	function floatval($var) {
		return (float)$var;
	}
}

if (!function_exists('stringval')) {
	/**
	 * Returns the string value of var.
	 *
	 * @param mixed $var The scalar value being converted to a string
	 * @return float The string value of <b>var</b>
	 */
	function stringval($var) {
		return (string)$var;
	}
}

if (!function_exists('is_iterable')) {
	/**
	 * Verify that the contents of a variable is accepted by the iterable pseudo-type, i.e. that it is an array or an object implementing Traversable.
	 *
	 * @param mixed $var The value to check
	 * @return boolean Returns <b>TRUE</b> if <b>var</b> is iterable, <b>FALSE</b> otherwise.
	 */
	function is_iterable($var) {
		return is_array($var) || $var instanceof Traversable;
	}
}
