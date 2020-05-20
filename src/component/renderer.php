<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\api_error;
use PHPunk\Util\object;
use PHPunk\Database\result;
use PHPunk\Database\record;

/**
 * @property string $resource Name of the resource for this component
 */
class renderer {
	/**
	 * @ignore internal variable
	 */
	private $_resource = false;

	/**
	 * @property string $resource Resource name for this component
	 */
	public function __construct($resource) {
		$this->_resource = $resource;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'resource':
				return $this->_resource;
		}
	}

	/**
	 * Renders a database result for API response.
	 * @param object $result Database result, record, or error object
	 */
	protected function render($result) {
		if ($result instanceof record) {
			$response = $this->create_response($result);
		} elseif ($result instanceof result) {
			$response = [];
			foreach ($result as $record)
				$response[] = $this->create_response($record);
		} elseif ($result instanceof api_error) {
			if (isset($result['status'])) {
				http_response_code($result['status']);
				unset($result['status']);
			}

			$response = $result;
		} else {
			$response = new api_error('api_invalid_response',
				'The response was invalid.');

			http_response_code(500);
		}

		header('Content-type: text/json');
		echo json_encode($response);
	}

	/**
	 * Builds an API data object from a database record. Returned object will
	 * have transformed field names/values and contain API hyperlinks.
	 * @param object $record Database record
	 * @return object API data object
	 */
	protected function create_response($record) {
		$response = new object();

		foreach ($record as $key => $value) {
			$map_key = $key;
			$map_value = $this->map_field_value($value, $map_key);
			if ($map_key) $response[$map_key] = $map_value;
		}

		$links = [];

		foreach ($this->get_links($record) as $rel => $params) {
			$params['api'] = true;
			$links[$rel] = [
				'href' => $this->build_url($params)
			];
		}

		$response->links = $links;

		return $response;
	}

	/**
	 * Adds relevant hyperlinks to a data object in API response
	 * @param object $record The data object being rendered
	 * @return array Multidimensional array of keys and URL parameters
	 */
	protected function get_links($record) {
		return [
			'self' => [
				'resource' => $this->resource,
				'id'       => $record->id
			],
			'collection' => [
				'resource' => $this->resource
			]
		];
	}

	/**
	 * Maps database field name to API field name
	 * @param string $field Database field name
	 * @return string API field name
	 */
	protected function map_field_name($field) {
		return $field;
	}

	/**
	 * Transforms database field value into API field value
	 * @param mixed $value Database field value
	 * @param string $field Database field name
	 * @return mixed Database field value, NULL if field is not mapped
	 */
	protected function map_field_value($value, &$field) {
		$field = $this->map_field_name($field);
		return $field ? $value : null;
	}

	protected function build_url($params) {
		trigger_error("Function must be overridden, renderer::build_url()", E_USER_ERROR);
	}
}
