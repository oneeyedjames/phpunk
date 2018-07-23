<?php

class renderer_base {
	private $_resource = false;

	public function __construct($resource) {
		$this->_resource = $resource;
	}

	public function __get($key) {
		switch ($key) {
			case 'resource':
				return $this->_resource;
		}
	}

	public function render($view, $controller = false) {
		$method = 'api_' . str_replace('-', '_', $view) . '_view';

		if (method_exists($controller, $method)) {
			$result = call_user_func([$controller, $method]);
		} else {
			$result = new api_error('api_undefined_view',
				'The requested API view is not defined', [
					'status'   => 400,
					'resource' => $this->resource,
					'view'     => $view
				]
			);
		}

		if ($result instanceof database_record) {
			$response = $this->create_response($result);
		} elseif ($result instanceof database_result) {
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
			$response = new api_error('api_invalid_backend_response',
				'The back-end response was invalid.');

			http_response_code(500);
		}

		header('Content-type: text/json');
		echo json_encode($response);
	}

	protected function create_response($record) {
		$response = new object();

		$record->each(function($value, $key) use ($response) {
			if ($field = $this->map_field_name($key))
				$response[$field] = $value;
		});

		$links = [];

		foreach ($this->get_links($record) as $rel => $params) {
			$params['api'] = true;

			$links[] = [
				'rel'  => $rel,
				'href' => build_url($params)
			];
		}

		$response->links = $links;

		return $response;
	}

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

	protected function map_field_name($field) {
		return $field;
	}
}
