#!/usr/bin/env php
<?php
require_once 'src/app.php';

$app->initApp();
$console = $app->getContainer()->get('console');
$console->run();