<?php

spl_autoload_register(function ($class) {
	$path = realpath(dirname(dirname(__FILE__)) . "/src/$class.php");

	if (is_file($path))
		require_once $path;
});