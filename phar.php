<?php

$srcRoot = 'src';
$buildRoot = 'build';
$buildFile = basename(__DIR__);

$flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME;

$phar = new Phar("$buildRoot/$buildFile.phar", $flags, $buildFile);

foreach (glob('src/*.php') as $srcFile) {
	echo "Including $srcFile\n";
	$phar->addFile($srcFile, basename($srcFile));
}

echo "...\nFinished $buildRoot/$buildFile.phar\n";
