<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\url_schema;
use PHPunk\Database\schema as db_schema;

class application {
	private $_database;
	private $_database_conn;
	private $_database_meta;

	private $_router;
	private $_router_host;
	private $_router_meta;

	private $_default_cache = false;
	private $_default_model = false;
	private $_default_controller = false;
	private $_default_renderer = false;

	private $_models = [];
	private $_controllers = [];
	private $_renderers = [];

	public function __get($key) {
		switch ($key) {
			case 'database':
				return $this->load_database();
			case 'router':
				return $this->load_router();
			case 'cache':
				return $this->_default_cache;
			case 'model':
				return $this->load_model();
			case 'controller':
				return $this->load_controller();
			case 'renderer':
				return $this->load_renderer();
		}
	}

	public function __call($func, $args) {
		if (count($args) == 1) {
			$resource = $args[0];

			switch ($func) {
				case 'model':
					return $this->load_model($resource);
				case 'controller':
					return $this->load_controller($resource);
				case 'renderer':
					return $this->load_renderer($resource);
			}
		}

		$class = get_class($this);

		trigger_error("Call to undefined method $class::$func()", E_USER_WARNING);
	}

	public function init_database($config, $meta) {
		if (is_null($this->_database_conn)) {
			$this->_database_conn = new \mysqli(
				$config->hostname,
				$config->username,
				$config->password
			);

			if ($this->_database_conn->connect_errno) {
				trigger_error($this->_database_conn->connect_error, E_USER_ERROR);
				return false;
			}

			$this->_database_conn->set_charset('utf8');

			$query = "SHOW DATABASES LIKE '$config->database'";

			if ($result = $this->_database_conn->query($query)) {
				if (0 == $result->num_rows) {
					$query = "CREATE DATABASE `$config->database`";

					if (!$this->_database_conn->query($query)) {
						trigger_error($this->_database_conn->error, E_USER_ERROR);
						return false;
					}
				}

				$result->close();
			}

			$this->_database_conn->select_db($config->database);
			$this->_database_meta = $meta;
		}
	}

	public function load_database() {
		if (is_null($this->_database)) {
			if (!($connection = $this->_database_conn)) {
				trigger_error("No database connection", E_USER_ERROR);
				return false;
			}

			$this->_database = $this->create_database($connection);

			foreach ($this->_database_meta as $table_name => $table_meta) {
				$this->_database->add_table($table_name, @$table_meta->pkey);

				if (isset($table_meta->relations)) {
					foreach ($table_meta->relations as $rel_name => $rel_meta) {
						$rel_meta->ftable = $table_name;

						$this->_database->add_relation(
							$rel_name,
							$rel_meta->ptable,
							$rel_meta->ftable,
							$rel_meta->fkey
						);
					}
				}
			}
		}

		return $this->_database;
	}

	public function init_router($host, $meta) {
		$this->_router_host = $host;
		$this->_router_meta = $meta;
	}

	public function load_router() {
		if (is_null($this->_router)) {
			$this->_router = $this->create_router($this->_router_host);

			foreach ($this->_router_meta as $res_name => $res_meta) {
				if ('<global>' == $res_name)
					$res_name = false;
				else
					$this->_router->add_resource($res_name);

				$aliases = @$res_meta->aliases ?: [];
				$actions = @$res_meta->actions ?: [];
				$views   = @$res_meta->views   ?: [];

				foreach ($aliases as $alias)
					$this->_router->add_alias($alias, $res_name);

				foreach ($actions as $action)
					$this->_router->add_action($action, $res_name);

				foreach ($views as $view)
					$this->_router->add_view($view, $res_name);
			}
		}

		return $this->_router;
	}

	public function init_model($cache) {
		if (!$this->_default_cache)
			$this->_default_cache = $cache;
	}

	public function load_model($resource = false) {
		if ($resource) {
			if (!isset($this->_models[$resource])) {
				$this->_models[$resource] = $this->create_model($resource);
			}

			return $this->_models[$resource];
		} else {
			if (is_null($this->_default_model))
				$this->_default_model = $this->create_model();

			return $this->_default_model;
		}
	}

	public function load_controller($resource = false) {
		if ($resource) {
			if (!isset($this->_controllers[$resource]))
				$this->_controllers[$resource] = $this->create_controller($resource);

			return $this->_controllers[$resource];
		} else {
			if (is_null($this->_default_controller))
				$this->_default_controller = $this->create_controller();

			return $this->_default_controller;
		}
	}

	public function load_renderer($resource = false) {
		if ($resource) {
			if (!isset($this->_renderers[$resource]))
				$this->_renderers[$resource] = $this->create_renderer($resource);

			return $this->_renderers[$resource];
		} else {
			if (is_null($this->_default_renderer))
				$this->_default_renderer = $this->create_renderer();

			return $this->_default_renderer;
		}
	}

	protected function create_database($connection) {
		return new db_schema($connection);
	}

	protected function create_router($host) {
		return new url_schema($host);
	}

	protected function create_model($resource = false) {
		if (!($database = $this->database)) {
			trigger_error("No default database", E_USER_ERROR);
			return false;
		}

		if ($resource) {
			$class = "{$resource}_model";

			if (class_exists($class))
				return new $class($database, $this->cache);
		}

		return new model($resource, $database, $this->cache);
	}

	protected function create_controller($resource = false) {
		$model = $this->load_model($resource);

		if ($resource) {
			$class = "{$resource}_controller";

			if (class_exists($class))
				return new $class($model);
		}

		return new controller($model);
	}

	protected function create_renderer($resource = false) {
		if ($resource) {
			$class = "{$resource}_renderer";

			if (class_exists($class))
				return new $class();
		}

		return new renderer($resource);
	}
}
