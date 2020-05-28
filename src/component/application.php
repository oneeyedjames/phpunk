<?php
/**
 * @package phpunk\component
 */

namespace PHPunk\Component;

use PHPunk\cache;
use PHPunk\url_schema;
use PHPunk\Database\schema as db_schema;

/**
 * @property object $database Single database schema instance
 * @property object $router Single url schema instance
 * @property object $cache Single cache instance
 * @property object $model Default model instance
 * @property object $controller Default controller instance
 * @property object $renderer Default renderer instance
 */
class application {
	/**
	 * @ignore internal variable
	 */
	private $_database;

	/**
	 * @ignore internal variable
	 */
	private $_database_conn;

	/**
	 * @ignore internal variable
	 */
	private $_database_meta;

	/**
	 * @ignore internal variable
	 */
	private $_router;

	/**
	 * @ignore internal variable
	 */
	private $_router_host;

	/**
	 * @ignore internal variable
	 */
	private $_router_meta;

	/**
	 * @ignore internal variable
	 */
	private $_cache;

	/**
	 * @ignore internal variable
	 */
	private $_cache_server;

	/**
	 * @ignore internal variable
	 */
	private $_default_model = false;

	/**
	 * @ignore internal variable
	 */
	private $_default_controller = false;

	/**
	 * @ignore internal variable
	 */
	private $_default_renderer = false;

	/**
	 * @ignore internal variable
	 */
	private $_models = [];

	/**
	 * @ignore internal variable
	 */
	private $_controllers = [];

	/**
	 * @ignore internal variable
	 */
	private $_renderers = [];

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'database':
				return $this->load_database();
			case 'router':
				return $this->load_router();
			case 'cache':
				return $this->load_cache();
			case 'model':
				return $this->load_model();
			case 'controller':
				return $this->load_controller();
			case 'renderer':
				return $this->load_renderer();
		}
	}

	/**
	 * @ignore magic method
	 */
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

	/**
	 * Initializes database connection and table structures. An E_USER_ERROR
	 * error will be triggered if the database is inaccessible.
	 * @param object $config Traversable object containing DB credentials
	 * @param object $meta Traversable object containing table structures
	 */
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

	/**
	 * Lazy-loads a database schema instance, using the previously-initialized
	 * database connection and table structures. An E_USER_ERROR error will be
	 * triggered if the database connection has not yet been initialized.
	 * @return object The database schema object
	 */
	public function load_database() {
		if (is_null($this->_database)) {
			if (!($connection = $this->_database_conn)) {
				trigger_error("No database connection", E_USER_ERROR);
				return false;
			}

			$this->_database = new db_schema($connection);

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

	/**
	 * Initializes host and metadata for url schema.
	 * @param string $host Hostname for url schema
	 * @param object $meta Traversable object containing url structures
	 */
	public function init_router($host, $meta) {
		$this->_router_host = $host;
		$this->_router_meta = $meta;
	}

	/**
	 * Lazy-loads a url schema instance, using the previously-initialized host
	 * and metadata.
	 * @return object The url schema object
	 */
	public function load_router() {
		if (is_null($this->_router)) {
			$this->_router = new url_schema($this->_router_host);

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

	/**
	 * Initializes caching server connection.
	 * @param object Traversable object containing host and port for server
	 */
	public function init_cache($config) {
		if (is_null($this->_cache_server)) {
			switch (strtolower($config->driver)) {
				case 'memcached':
					$this->_cache_server = new Memcached();
					break;
				case 'memcache':
				default:
					$this->_cache_server = new Memcache();
					break;
			}

			$this->_cache_server->addServer($config->host, $config->port);
		}
	}

	/**
	 * Lazy-loads a cache instance, using the previously-initialized connection.
	 * If server is not initalized, cache object will only use in-memory data.
	 * @return object The cache object
	 */
	public function load_cache() {
		if (is_null($this->_cache)) {
			$this->_cache = new cache($this->_cache_server);
		}

		return $this->_cache;
	}

	/**
	 * Lazy-loads a model object for the specified resource, if given.
	 * @param string $resource OPTIONAL resource name
	 * @return object A model object
	 */
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

	/**
	 * Lazy-loads a controller object for the specified resource, if given.
	 * @param string $resource OPTIONAL resource name
	 * @return object A controller object
	 */
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

	/**
	 * Lazy-loads a renderer object for the specified resource, if given.
	 * @param string $resource OPTIONAL resource name
	 * @return object A renderer object
	 */
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

	/**
	 * Constructs a model object for the specified resource. An E_USER_ERROR is
	 * triggered if the database connection has not yet been initialized.
	 * This method is intended to be extensible for child classes.
	 * @param string $resource OPTIONAL resource name
	 * @return object A model object
	 */
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

	/**
	 * Constructs a controller object for the specified resource. An E_USER_ERROR
	 * is triggered if the database connection has not yet been initialized.
	 * This method is intended to be extensible for child classes.
	 * @param string $resource OPTIONAL resource name
	 * @return object A controller object
	 */
	protected function create_controller($resource = false) {
		$model = $this->load_model($resource);

		if ($resource) {
			$class = "{$resource}_controller";

			if (class_exists($class))
				return new $class($model);
		}

		return new controller($model);
	}

	/**
	 * Constructs a renderer object for the specified resource. An E_USER_ERROR
	 * is triggered if the database connection has not yet been initialized.
	 * This method is intended to be extensible for child classes.
	 * @param string $resource OPTIONAL resource name
	 * @return object A renderer object
	 */
	protected function create_renderer($resource = false) {
		if ($resource) {
			$class = "{$resource}_renderer";

			if (class_exists($class))
				return new $class();
		}

		return new renderer($resource);
	}
}
