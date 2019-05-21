<?php
/**
 * @package phpunk
 */

namespace PHPunk;

class event_manager {
	/**
	 * @ignore internal variable
	 */
	private $_handlers = [];

	/**
	 * Adds a callback handler for the specified event
	 *
	 * @param string $event The name of the event
	 * @param Callable $callback The callback function to handle the event
	 */
	public function listen($event, $callback) {
		if (is_callable($callback)) {
			if (!isset($this->_handlers[$event]))
				$this->_handlers[$event] = [];

			$this->_handlers[$event][] = $callback;
		}
	}

	/**
	 * Fires all callback handlers for the specified event
	 *
	 * @param string $event The name of the event
	 */
	public function trigger($event) {
		$args = array_slice(func_get_args(), 1);

		if (isset($this->_handlers[$event])) {
			foreach ($this->_handlers[$event] as $callback) {
				call_user_func_array($callback, $args);
			}
		}
	}
}
