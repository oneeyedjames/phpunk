<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

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
			return call_user_func(array($this, $method), $_GET, $_POST);
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
			$vars = call_user_func(array($this, $method), $vars);
	}
}
