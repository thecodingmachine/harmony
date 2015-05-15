<?php
use Mouf\FrameworkInterop\Application;

require_once __DIR__.'/../vendor/autoload.php';

if (file_exists(__DIR__.'/../vendor-harmony/autoload.php')) {
    require_once __DIR__.'/../vendor-harmony/autoload.php';
}

if (file_exists(__DIR__.'/../modules.php')) {
    $moduleDescriptors = require __DIR__.'/../modules.php';
} else {
    $moduleDescriptors = require __DIR__.'/../modules.init.php';
}

$modules = array_map(function ($descriptor) { return $descriptor['module']; }, $moduleDescriptors);

$app = new Application(
    $modules
);
