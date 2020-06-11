<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\api_error;

/**
 * @property string $resource Resource name for this component
 */
class controller {
	/**
	 * @ignore internal variable
	 */
	protected $_model;

	/**
	 * @param object $model Model instance for this component
	 */
	public function __construct($model) {
		$this->_model = $model;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'resource':
				return @$this->_model->resource;
		}
	}

	/**
	 * Executes concrete action methods in child classes
	 * @param string $action API name of the relevant action
	 */
	public function do_action($action) {
		$method = str_replace('-', '_', $action) . '_action';
		if (method_exists($this, $method))
			return call_user_func([$this, $method], $_GET, $_POST);
		else
			trigger_error("Undefined action $this->resource:$action", E_USER_WARNING);
	}

	/**
	 * Executes concrete view methods in child classes
	 * @param string $view API name of the relevant view
	 * @param array $vars Array of named parameters that will be passed into the view
	 */
	public function pre_view($view, &$vars) {
		$method = str_replace('-', '_', $view) . '_view';
		if (method_exists($this, $method))
			$vars = call_user_func([$this, $method], $vars);
	}

	/**
	 * Executes concrete api view methods in child classes
	 * @param string $view API name of the relevant view
	 * @param mixed $vars Array of named parameters that will be passed into the renderer
	 */
	public function pre_render($view, &$vars) {
		$new_method = str_replace('-', '_', $view) . '_api';
		$old_method = 'api_' . str_replace('-', '_', $view) . '_view';
		if (method_exists($this, $new_method)) {
			$vars = call_user_func([$this, $new_method], $vars);
		} elseif (method_exists($this, $old_method)) {
			$vars = call_user_func([$this, $old_method], $_GET, $_POST);
		} else {
			$vars = new api_error('api_undefined_view',
				'The requested API view is not defined', [
					'status'   => 400,
					'resource' => $this->resource,
					'view'     => $view
				]
			);
		}
	}
}
