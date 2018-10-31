<?php
/**
 * @package phpunk
 */

namespace PHPunk;

/**
 * Basic reference implementation. Multi-type, in-memory cache.
 */
class cache {
	protected $_memcache;
	protected $_data = array();

	public function __construct($memcache = false) {
		$this->_memcache = $memcache;
	}

	public function has($type, $id = false) {
		if (!isset($this->_data[$type]))
			return false;

		return $id ? isset($this->_data[$type][$id]) : true;
	}

	public function get($type, $id = false) {
		if ($this->_memcache)
			return $this->_memcache->get("$type/$id");

		return $id ? @$this->_data[$type][$id] : @$this->_data[$type];
	}

	public function put($type, $id, $object) {
		if ($this->_memcache) {
			$this->_memcache->set("$type/$id", $object); // TODO timeout?
			return $object;
		}

		return @$this->_data[$type][$id] = $object;
	}

	public function remove($type, $id = false) {
		if ($this->_memcache) {
			$this->_memcache->delete("$type/$id");
			return;
		}

		if ($id)
			unset($this->_data[$type][$id]);
		else
			unset($this->_data[$type]);
	}
}
