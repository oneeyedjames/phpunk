<?php

foreach (glob('src/*.php') as $file)
	require_once $file;

foreach (glob('src/database/*.php') as $file)
	require_once $file;
