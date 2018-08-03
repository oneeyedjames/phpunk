<?php

class url_schema {
	private $_http_host;
	private $_base_path;

	private $_resources = array();
	private $_aliases = array();
	private $_actions = array();
	private $_views = array();

	public function __construct($host = false, $path = '/') {
		$this->_http_host = !empty($host) ? $host : $_SERVER['HTTP_HOST'];
		$this->_base_path = $path;
	}

	public function __get($key) {
		switch ($key) {
			case 'resources':
			case 'actions':
			case 'views':
				return $this->{"_$key"};
		}
	}

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

	public function is_resource($resource) {
		return isset($this->_resources[$resource]) ? $resource : $this->is_alias($resource);
	}

	public function add_alias($alias, $resource) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_alias($alias))
			$this->_aliases[$alias] = $resource;
	}

	public function is_alias($alias) {
		return isset($this->_aliases[$alias]) ? $this->_aliases[$alias] : false;
	}

	public function add_action($action, $resource = false) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_action($action, $resource))
			$this->_resources[$resource]['actions'][] = $action;
		elseif ($resource === false && !$this->is_action($action))
			$this->_actions[] = $action;
	}

	public function is_action($action, $resource = false) {
		$resource = $this->is_resource($resource);

		$actions = $resource ? $this->_resources[$resource]['actions'] : $this->_actions;

		return in_array($action, $actions) ? $action : false;
	}

	public function add_view($view, $resource = false) {
		$resource = $this->is_resource($resource);

		if ($resource && !$this->is_view($view, $resource))
			$this->_resources[$resource]['views'][] = $view;
		elseif ($resource === false && !$this->is_view($view))
			$this->_views[] = $view;
	}

	public function is_view($view, $resource = false) {
		$resource = $this->is_resource($resource);

		$views = $resource ? $this->_resources[$resource]['views'] : $this->_views;

		return in_array($view, $views) ? $view : false;
	}

	public function parse_path($path = '') {
		if (is_string($path))
			$path = explode('/', trim($path, '/'));

		$params = array();

		if ($params['api'] = ('api' == $path[0]))
			array_shift($path);

		if (isset($path[0])) {
			if ($resource = $this->is_resource($path[0])) {
				$params['resource'] = $resource;
				array_shift($path);

				if (isset($path[0]) && is_numeric($path[0]))
					$params['id'] = intval(array_shift($path));
			} else {
				if ($this->is_action($path[0]))
					$params['action'] = array_shift($path);
				elseif ($this->is_view($path[0]))
					$params['view'] = array_shift($path);
			}
		}

		if (isset($path[0], $params['resource'])) {
			if ($this->is_action($path[0], $params['resource'])) {
				$params['action'] = array_shift($path);
			} elseif ($this->is_view($path[0], $params['resource'])) {
				$params['view'] = array_shift($path);
			} elseif (isset($params['id']) && $resource = $this->is_resource($path[0])) {
				$params['filter'] = array($params['resource'] => $params['id']);
				$params['resource'] = $resource;

				unset($params['id']);
				array_shift($path);

				if (isset($path[0])) {
					if ($this->is_action($path[0], $params['resource']))
						$params['action'] = array_shift($path);
					elseif ($this->is_view($path[0], $params['resource']))
						$params['view'] = array_shift($path);
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
				@$params['filter'][$key][$suffix] = $value;
			else
				@$params['filter'][$key] = $value;
		}

		return $params;
	}

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

	public function build($params, $secure = false) {
		$scheme = $secure ? 'https' : 'http';

		$host = trim($this->_http_host, '/');
		$base = trim($this->_base_path, '/');
		$path = $this->build_path($params);

		if (!empty($base))
			$path = "$base/$path";

		return "$scheme://$host/$path";
	}

	protected function split_slug($slug) {
		if (preg_match('/^([A-Z0-9-_]+)~([A-Z0-9-_]+)$/si', $slug, $matches))
			return array($matches[1], $matches[2]);

		static $empty = '';
		return array($slug, $empty);
	}

	protected function join_slug($slug, $suffix) {
		if (!empty($suffix))
			return "$slug~$suffix";

		return $slug;
	}

	protected function explode_slug($slug) {
		if (strpos($slug, '.') !== false)
			return explode('.', $slug);

		return $slug;
	}

	protected function implode_slug($slug) {
		return implode('.', $slug);
	}
}
