<?php
/**
 * Backwards-compatibility functions and constants
 *
 * @package phpunk\compat\rest
 */

/**
 * Returns the REST action name that corresponds with the current HTTP request
 * method, or FALSE for unrecognized request methods.
 *
 * Recognized request methods:
 *   - GET -> read
 *   - POST -> create
 *   - PUT -> update
 *   - DELETE -> delete
 *
 * @return string The REST action name for the current request
 */
function rest_action() {
	switch (strtoupper($_SERVER['REQUEST_METHOD'])) {
		case 'GET':
			return 'read';
		case 'POST':
			return 'create';
		case 'PUT':
			return 'update';
		case 'DELETE':
			return 'delete';
		default:
			return false;
	}
}

/**
 * Returns a shorthand name for the current request body's MIME type.
 * In most cases the shorthand name will match the corresponding file extenson
 * (eg: 'json'  or 'xml').
 * @return string The shorthand MIME type for the current request
 */
function rest_content_type() {
	switch (strtoupper($_SERVER['CONTENT_TYPE'])) {
		case 'application/json':
		case 'application/hal+json':
			return 'json';
		case 'application/xml':
		case 'text/xml':
			return 'xml';
		case 'application/x-www-form-urlencoded':
			return 'url';
		default:
			return false;
	}
}

/**
 * Returns a parsed representation of the raw request body.
 * In most cases, this takes the form of an associative array.
 * @return mixed Associative array representing the body of the current request
 */
function rest_request_body() {
	if (in_array(rest_action(), ['create', 'update'])) {
		$data = file_get_contents('php://input');

		switch (rest_content_type()) {
			case 'json':
				return json_decode($data, true);
			case 'xml':
				return simplexml_load_string($data);
			case 'url':
				parse_str($data, $vars);
				return $vars;
		}

		return $data;
	}

	return false;
}

