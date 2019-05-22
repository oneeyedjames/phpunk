<?php
/**
 * @package phpunk
 */

namespace PHPunk;

/**
 * Basic reference implementation. Multi-type, in-memory cache.
 */
class cache {
	/**
	 * @ignore internal variable
	 */
	protected $_memcache;

	/**
	 * @ignore internal variable
	 */
	protected $_data = array();

	/**
	 * Memcache is required to preserve cache across multiple requests.
	 * @param object $memcache OPTIONAL Memcache or Memcached instance
	 */
	public function __construct($memcache = false) {
		$this->_memcache = $memcache;
	}

	/**
	 * Returns whether or not a cached object exists for the given type and Id.
	 * If the Id is omitted, returns whether type exists.
	 * @param string $type Name of object type
	 * @param mixed $id OPTIONAL Unique object Id
	 * @return boolean TRUE if object or type exists in cache, FALSE otherwise
	 */
	public function has($type, $id = false) {
		if (!isset($this->_data[$type]))
			return false;

		return $id ? isset($this->_data[$type][$id]) : true;
	}

	/**
	 * Returns a cached object for the given type and Id.
	 * @param string $type Name of object type
	 * @param mixed $id OPTIONAL Unique object Id
	 * @return mixed The cached object, NULL if not exists
	 */
	public function get($type, $id = false) {
		if ($this->_memcache)
			return $this->_memcache->get("$type/$id");

		return $id ? @$this->_data[$type][$id] : @$this->_data[$type];
	}

	/**
	 * Caches an object with the given type and Id.
	 * @param string $type Name of object type
	 * @param mixed $id Unique object Id
	 * @param mixed $object The object to cache
	 * @return mixed The cached object
	 */
	public function put($type, $id, $object) {
		if ($this->_memcache) {
			$this->_memcache->set("$type/$id", $object); // TODO timeout?
			return $object;
		}

		return @$this->_data[$type][$id] = $object;
	}

	/**
	 * Removes an object with the given type and Id from the cache. If the Id is
	 * omitted, removes all objects of the given type.
	 * @param string $type Name of object type
	 * @param mixed $id OPTIONAL Unique object Id
	 */
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
