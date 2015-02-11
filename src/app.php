<?php
use Mouf\FrameworkInterop\Application;

require_once __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../vendor-harmony/autoload.php')) {
	require_once __DIR__.'/../vendor-harmony/autoload.php';
}

if (!file_exists(__DIR__.'/../../../../mouf/no_commit/MoufUsers.php')) {
	
	$rootUrl = $_SERVER['BASE']."/";
	
	if ($_SERVER['REQUEST_URI'] != $rootUrl.'install') {
		define('ROOT_URL', $rootUrl);
		require '../install_screen.php';
		exit;
	}
}

$moduleDescriptors = require __DIR__.'/../modules.php';

$modules = array_map(function($descriptor) { return $descriptor['module']; }, $moduleDescriptors);

$app = new Application(
	$modules
);
