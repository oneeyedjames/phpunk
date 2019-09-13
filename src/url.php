<?php
/**
 * @package phpunk
 */

namespace PHPunk;

/**
 * @property array $resources Array of registered resources
 * @property array $actions Array of registered global actions
 * @property array $views Array of registered global views
 */
class url_schema {
	/**
	 * @ignore internal variable
	 */
	private $_http_host;

	/**
	 * @ignore internal variable
	 */
	private $_base_path;

	/**
	 * @ignore internal variable
	 */
	private $_resources = array();

	/**
	 * @ignore internal variable
	 */
	private $_aliases = array();

	/**
	 * @ignore internal variable
	 */
	private $_actions = array();

	/**
	 * @ignore internal variable
	 */
	private $_views = array();

	/**
	 * @param string $host A fully-qualified domain name
	 * @param string $path OPTIONAL base path for URL's
	 */
	public function __construct($host = false, $path = '/') {
		$this->_http_host = !empty($host) ? $host : $_SERVER['HTTP_HOST'];
		$this->_base_path = $path;
	}

	/**
	 * @ignore magic method
	 */
	public function __get($key) {
		switch ($key) {
			case 'resources':
			case 'actions':
			case 'views':
				return $this->{"_$key"};
		}
	}

	/**
	 * Registers a resource name. Registers alias for resource, if included.
	 * @param string $resource Resource name
	 * @param string $alias Alias for resource name
	 */
	public function add_resource($resource, $alias = false) {
		if (!$this->is_resource($resource)) {
			$this->_resources[$resource] = array(
				'actions' => array('save', 'delete'),
				'views'   => array('list', 'grid', 'item', 'form')
			);
		}

		if ($alias)
			$this->add_alias($alias, $resource);
	}

	/**
	 * Returns canonical resource name, if it exists.
	 * @param string $resource Resource name or alias
	 * @return string Canonical resource name, FALSE if no such resource or alias exists
	 */
	public function is_resource($resource) {
		return isset($this->_resources[$resource]) ? $resource : $this->is_alias($resource);
	}

	/**
	 * Registers an alias for the given resource name.
	 * @param string $alias Alias for the resource name
	 * @param string $resource Original resource name
	 */
	public function add_alias($alias, $resource) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_alias($alias))
			$this->_aliases[$alias] = $resource;
	}

	/**
	 * Returns canonical resource name for the alias, if it exists.
	 * @param string $alias Alias for the resource name
	 * @return string Canonical resource name, FALSE if no such alias exists
	 */
	public function is_alias($alias) {
		return isset($this->_aliases[$alias]) ? $this->_aliases[$alias] : false;
	}

	/**
	 * Registers an action name. If resource is omitted, the action is registered
	 * as a global action.
	 * @param string $action Action name
	 * @param string $resource OPTIONAL Resource name
	 */
	public function add_action($action, $resource = false) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_action($action, $resource))
			$this->_resources[$resource]['actions'][] = $action;
		elseif ($resource === false && !$this->is_action($action))
			$this->_actions[] = $action;
	}

	/**
	 * Returns canonical action name, if it exists.
	 * @param string $action Action name
	 * @param string $resource OPTIONAL Resource name
	 * @return string Canonical action name, FALSE if no such action exists
	 */
	public function is_action($action, $resource = false) {
		$resource = $this->is_resource($resource);

		$actions = $resource ? $this->_resources[$resource]['actions'] : $this->_actions;

		return in_array($action, $actions) ? $action : false;
	}

	/**
	 * Registers a view name. If resource is omitted, the view is registered as
	 * a global view.
	 * @param string $action View name
	 * @param string $resource OPTIONAL Resource name
	 */
	public function add_view($view, $resource = false) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_view($view, $resource))
			$this->_resources[$resource]['views'][] = $view;
		elseif ($resource === false && !$this->is_view($view))
			$this->_views[] = $view;
	}

	/**
	 * Returns canonical view name, if it exists.
	 * @param string $action View name
	 * @param string $resource OPTIONAL Resource name
	 * @return string Canonical view name, FALSE if no such view exists
	 */
	public function is_view($view, $resource = false) {
		$resource = $this->is_resource($resource);

		$views = $resource ? $this->_resources[$resource]['views'] : $this->_views;

		return in_array($view, $views) ? $view : false;
	}

	/**
	 * Parses a URL path into key-value parameters.
	 * @param string $path OPTIONAL Path to parse
	 * @return array Key-value parameters
	 */
	public function parse_path($path = '') {
		if (is_string($path))
			$path = explode('/', trim($path, '/'));

		$params = array();

		if ($params['api'] = ('api' == $path[0]))
			array_shift($path);

		if (isset($path[0])) {
			$slug = self::strip_extension($path[0], $ext);
			if ($resource = $this->is_resource($slug)) {
				self::set_param($params, 'resource', $resource, $ext);
				array_shift($path);

				if (isset($path[0])) {
					$slug = self::strip_extension($path[0], $ext);
					if (is_numeric($slug)) {
						self::set_param($params, 'id', intval($slug), $ext);
						array_shift($path);
					}
				}
			} elseif ($this->is_action($slug)) {
				self::set_param($params, 'action', $slug, $ext);
				array_shift($path);
			} elseif ($this->is_view($slug)) {
				self::set_param($params, 'view', $slug, $ext);
				array_shift($path);
			}
		}

		if (isset($path[0], $params['resource'])) {
			$slug = self::strip_extension($path[0], $ext);
			if ($this->is_action($slug, $params['resource'])) {
				self::set_param($params, 'action', $slug, $ext);
				array_shift($path);
			} elseif ($this->is_view($slug, $params['resource'])) {
				self::set_param($params, 'view', $slug, $ext);
				array_shift($path);
			} elseif (isset($params['id']) && $resource = $this->is_resource($slug)) {
				$params['filter'] = array($params['resource'] => $params['id']);
				self::set_param($params, 'resource', $resource, $ext);

				unset($params['id']);
				array_shift($path);

				if (isset($path[0])) {
					$slug = self::strip_extension($path[0], $ext);
					if ($this->is_action($slug, $params['resource'])) {
						self::set_param($params, 'action', $slug, $ext);
						array_shift($path);
					} elseif ($this->is_view($slug, $params['resource'])) {
						self::set_param($params, 'view', $slug, $ext);
						array_shift($path);
					}
				}
			}
		}

		while (count($path) >= 2) {
			list($key, $suffix) = $this->split_slug(array_shift($path));
			$value = $this->explode_slug(array_shift($path));

			if ($key == 'sort')
				$params[$key][$value] = strtolower($suffix) == 'desc' ? 'desc' : 'asc';
			elseif (in_array($key, array('page', 'per_page')))
				$params[$key] = intval($value);
			elseif (!empty($suffix))
				@$params['filter'][$key][$suffix] = urldecode($value);
			else
				@$params['filter'][$key] = urldecode($value);
		}

		return $params;
	}

	/**
	 * Builds a URL path, based on given parameters.
	 * @param array $params OPTIONAL Key-value parameters for URL
	 * @return string The URL path
	 */
	public function build_path($params = array()) {
		$path = array();

		if (@$params['api'])
			$path[] = 'api';

		if ($this->is_resource(@$params['resource'])) {
			$path[] = $params['resource'];

			if (is_numeric(@$params['id']))
				$path[] = $params['id'];

			if ($this->is_action(@$params['action'], $params['resource']))
				$path[] = $params['action'];
			elseif ($this->is_view(@$params['view'], $params['resource']))
				$path[] = $params['view'];
		} elseif ($this->is_action(@$params['action'])) {
			$path[] = $params['action'];
		} elseif ($this->is_view(@$params['view'])) {
			$path[] = $params['view'];
		}

		foreach (array('page', 'per_page') as $filter) {
			if (isset($params[$filter]) && is_numeric($params[$filter])) {
				$path[] = $filter;
				$path[] = intval($params[$filter]);
			}
		}

		if (isset($params['sort']) && is_array($params['sort'])) {
			foreach ($params['sort'] as $key => $order) {
				$order = strtolower($order);
				if (in_array($order, array('asc', 'desc'))) {
					$path[] = $this->join_slug('sort', $order);
					$path[] = $key;
				}
			}
		}

		if (isset($params['filter']) && is_array($params['filter'])) {
			foreach ($params['filter'] as $key => $value) {
				if (is_scalar($value)) {
					$path[] = urlencode($key);
					$path[] = urlencode($value);
				}
			}
		}

		return implode('/', $path);
	}

	/**
	 * Builds a complete URL, based on given parameters. Includes any base path.
	 * @param array $params Key-value parameters for URL
	 * @param boolean $secure Whether the URL should use HTTPS
	 * @return string The complete URL
	 */
	public function build($params, $secure = false) {
		$scheme = $secure ? 'https' : 'http';

		$host = trim($this->_http_host, '/');
		$base = trim($this->_base_path, '/');
		$path = $this->build_path($params);

		if (!empty($base))
			$path = "$base/$path";

		return "$scheme://$host/$path";
	}

	/**
	 * Splits a URL slug at the first tilde (~). If slug does not contain a
	 * tilde, the first half will be the full slug and the second half will be
	 * an empty string.
	 * @param string $slug URL slug to be split
	 * @param array Array of two strings
	 */
	protected function split_slug($slug) {
		if (preg_match('/^([A-Z0-9-_]+)~([A-Z0-9-_]+)$/si', $slug, $matches))
			return array($matches[1], $matches[2]);

		static $empty = '';
		return array($slug, $empty);
	}

	/**
	 * Joins two strings with a tilde (~) into a single URL slug. If second
	 * argument is empty, result will contain the base string with no tilde.
	 * @param string $slug A base string
	 * @param string $suffix A suffix to append
	 * @return string The completed URL slug
	 */
	protected function join_slug($slug, $suffix) {
		if (!empty($suffix))
			return "$slug~$suffix";

		return $slug;
	}

	/**
	 * Splits a URL slug at each dot (.). If slug does not contain a dot, the
	 * original URL slug will be returned.
	 * @param string $slug URL slug to be split
	 * @return mixed Array of strings, or original slug
	 */
	protected function explode_slug($slug) {
		if (strpos($slug, '.') !== false)
			return explode('.', $slug);

		return $slug;
	}

	/**
	 * Joins an array of strings with dots (.) into a single URL slug.
	 * @param array $slug Array of strings to be joined
	 * @return string The completed URL slug
	 */
	protected function implode_slug($slug) {
		return implode('.', $slug);
	}

	protected static function strip_extension($slug, &$ext = null) {
		if (strpos($slug, '.') !== false)
			list($slug, $ext) = explode('.', $slug, 2);

		return $slug;
	}

	protected static function set_param(&$params, $key, $value, $ext = null) {
		$params[$key] = $value;

		unset($params['format']);
		if (!empty($ext))
			$params['format'] = $ext;
	}
}
