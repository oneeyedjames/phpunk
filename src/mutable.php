<?php

interface mutable {
	function has($key);
	function get($key, $default = null);
	function put($key, $value);
	function remove($key);
}
