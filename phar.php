<?php
$srcRoot = './src';
$buildRoot = './build';
$buildFile = 'zombie.phar';

$flags = FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::KEY_AS_FILENAME;

$phar = new Phar("$buildRoot/$buildFile", $flags, $buildFile);

foreach (glob('src/*.php') as $srcFile) {
	echo "$srcFile\n";
	$phar->addFile($srcFile, basename($srcFile));
}

//$phar["index.php"] = file_get_contents($srcRoot . "/index.php");
//$phar["common.php"] = file_get_contents($srcRoot . "/common.php");
//$phar->setStub($phar->createDefaultStub("index.php"));

//copy($srcRoot . "/config.ini", $buildRoot . "/config.ini");
