<?php
require 'app.php';

//$app->getContainer()->get('react');

$container = $app->getContainer();

$pimple = new Pimple();
$pimple['port'] = 8001;

$pimple['loop'] = $pimple->share(function() {
    return \React\EventLoop\Factory::create();
});

/*$pimple['reactor'] = $pimple->share(function($pimple) {
    $socket = new Reactor($pimple['loop']);
    $socket->listen($pimple['port'], '127.0.0.1');
    return $socket;
});*/


/*$pimple['ioserver'] = $pimple->share(function($pimple) {
    return new \Ratchet\Server\IoServer(new DisplayActivityApp(), $pimple['loop']);
});*/


//$app['ioserver']->run();

$pimple['consoleRepository'] = $pimple->share(function($pimple) {
    return new \Harmony\React\ConsoleRepository($pimple['loop']);
});

$pimple['ratchetConsoleHttp'] = $pimple->share(function($pimple) {
    return new \Harmony\React\RatchetConsoleLauncher($pimple['consoleRepository']);
});

$pimple['ratchetConsole'] = $pimple->share(function($pimple) {
    return new \Harmony\React\RatchetConsole($pimple['consoleRepository']);
});

$pimple['ratchetApp'] = $pimple->share(function($pimple) {
    // TODO: put 'hostname' instead of "localhost"
    $app =  new \Ratchet\App('localhost', $pimple['port'], '127.0.0.1', $pimple['loop']);
    $app->route('/console', $pimple['ratchetConsole']);
    $app->route('/run', $pimple['ratchetConsoleHttp'], ['*']);
    return $app;
});

$pimple['ratchetApp']->run();
