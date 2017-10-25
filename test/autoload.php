<?php

spl_autoload_register(function ($class) {
	$path = realpath(dirname(dirname(__FILE__)) . '/src');
	$file = $class;

	if (stripos($class, 'database_') === 0) {
		$path .= '/database';
		$file = substr($class, 9);
	}

	$file = "$path/$file.php";

	if (is_file($path))
		require_once $path;
});
