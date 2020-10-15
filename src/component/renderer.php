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
 * @property string $result_name Collective name for embedded collection
 */
class renderer {
	/**
	 * @ignore internal constant
	 */
	const MIME_TYPE = 'application/hal+json';

	const RESULT = 'result';
	const RECORD = 'record';
	const EMBEDDED = 'embedded';
	const URL_PARAMS = 'url_params';

	/**
	 * @ignore internal variable
	 */
	private $_resource;

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
			case 'result_name':
				return $this->_resource;
		}
	}

	/**
	 * Renders the named view with the provided data
	 * @param string $view Name of view to render
	 * @param array $vars OPTIONAL Array of variables to render
	 */
	public function render($view, $vars = []) {
		if ($result = @$vars[self::RESULT]) {
			$params = @$vars[self::URL_PARAMS];

			$output = new object();
			$output->count = count($result);
			$output->total = $result->found;
			$output->_links = $this->get_result_links($result, $params);

			$vars[self::EMBEDDED][$this->result_name] = $result;
		} elseif ($record = @$vars[self::RECORD]) {
			$output = $this->map_record($record);
			$output->_links = $this->get_record_links($record);
		}

		if ($embedded = @$vars[self::EMBEDDED])
			$output->_embedded = array_map([$this, 'embed'], $embedded);

		header('Content-Type: ' . self::MIME_TYPE);
		echo json_encode($output);
	}

	/**
	 * TODO backport to PHPunk project
	 * @param object $object A database result or record
	 * @return object Formatted record with links for embedded context
	 */
	protected function embed($object) {
		if ($object instanceof record) {
			$embedded = $this->map_record($object, true);
			$embedded['_links'] = $this->get_record_links($object);
		} elseif ($object instanceof result) {
			$embedded = $object->map(function($record) {
				return $this->embed($record);
			});
		} else {
			$class = get_class($object);
			trigger_error("Invalid object type: $class", E_USER_WARNING);
			return false;
		}

		return $embedded;
	}

	/**
	 * Transforms a database record into its final API representation
	 * @param object $record A database record
	 * @param boolean $embedded OPTIONAL flag to indicate embedded context
	 * @return object Formatted record for appropriate context
	 */
	protected function map_record($record, $embedded = false) {
		$object = new object();

		foreach ($record as $key => $value) {
			$map_key = $key;
			$map_value = $this->map_field_value($value, $map_key, $embedded);
			if ($map_key) $object[$map_key] = $map_value;
		}

		return $object;
	}

	/**
	 * Maps database field name to API field name
	 * @param string $field Database field name
	 * @param boolean $embedded OPTIONAL flag indicating embedded context
	 * @return string API field name
	 */
	protected function map_field_name($field, $embedded = false) {
		return $field;
	}

	/**
	 * Transforms database field value into API field value
	 * @param mixed $value Database field value
	 * @param string $field Database field name
	 * @param boolean $embedded OPTIONAL flag indicating embedded context
	 * @return mixed Database field value, NULL if field is not mapped
	 */
	protected function map_field_value($value, &$field, $embedded = false) {
		$field = $this->map_field_name($field, $embedded);
		return $field ? $value : null;
	}

	protected function build_url($params) {
		trigger_error("Function must be overridden, renderer::build_url()", E_USER_WARNING);
	}

	/**
	 * Generates API links for a database result
	 * @see PHPunk\url_schema
	 * @param object $result A database result
	 * @param array $params OPTIONAL url schema parameters
	 * @return array Named set of API links
	 */
	protected function get_result_links($result, $params = []) {
		$params['resource'] = $this->resource;
		$params['api'] = true;

		$first = $params;
		$first['page'] = 1;

		$last = $params;
		$last['page'] = intval(ceil($result->found / $params['per_page']));

		$prev = $params;
		$prev['page']--;

		$next = $params;
		$next['page']++;

		$links = ['self' => ['href' => $this->build_url($params)]];

		if ($first['page'] != $last['page']) {
			$links['first'] = ['href' => $this->build_url($first)];
			$links['last']  = ['href' => $this->build_url($last)];
		}

		if ($prev['page'] >= $first['page'])
			$links['prev'] = ['href' => $this->build_url($prev)];

		if ($next['page'] <= $last['page'])
			$links['next'] = ['href' => $this->build_url($next)];

		return $links;
	}

	/**
	 * Generates API links for an individual record
	 * @see PHPunk\url_schema
	 * @param object $record A database record
	 * @param array $params OPTIONAL url schema parameters
	 * @return array Named set of API links
	 */
	protected function get_record_links($record, $params = []) {
		$params['resource'] = $this->resource;
		$params['id'] = $record->id;
		$params['api'] = true;

		return ['self' => ['href' => $this->build_url($params)]];
	}
}
