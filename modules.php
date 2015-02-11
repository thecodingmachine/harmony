<?php
return [

    [
        'name' => 'whoops-middleware',
        'description' => 'Whoops middleware to catch errors',
        'module' => new Mouf\FrameworkInterop\WhoopsModule(),
        'enable' => true,
    ],
    [
        'name' => 'mouf-container',
        'description' => 'Mouf container for Harmony',
        'module' => $moufModule = new Mouf\FrameworkInterop\Module(__DIR__.'/mouf/instances.php', 'Mouf\\AdminContainer', 'src-dev/Mouf/AdminContainer.php', __DIR__.'/mouf/config_tpl.php', __DIR__.'/mouf/variables.php'),
        'enable' => true,
    ],
    [
        'name' => 'splash-module',
        'description' => 'Splash middleware that manages most pages',
        'module' => new Mouf\FrameworkInterop\SplashModule($moufModule),
        'enable' => true,
    ],
    [
        'name' => 'root-container',
        'description' => 'Facade for the root container',
        'module' => new Mouf\RootContainer\RootContainerModule(),
        'enable' => true,
    ],
];
