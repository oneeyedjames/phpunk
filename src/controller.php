<?php

class controller_base {
	protected $_resource;

	public function __construct($resource) {
		$this->_resource = $resource;
	}

	public function __get($key) {
		switch ($key) {
			case 'resource':
				return @$this->_resource->name;
		}
	}

	public function do_action($action) {
		$method = str_replace('-', '_', $action) . '_action';
		if (method_exists($this, $method))
			return call_user_func(array($this, $method), $_GET, $_POST);
		else
			trigger_error("Undefined action $this->resource:$action", E_USER_WARNING);
	}

	public function pre_view($view, &$vars) {
		$method = str_replace('-', '_', $view) . '_view';
		if (method_exists($this, $method))
			$vars = call_user_func(array($this, $method), $vars);
	}
}
