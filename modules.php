<?php
return [
    [
        'name' => 'whoops-middleware',
        'description' => 'Whoops middleware to catch errors',
        'module' => new Mouf\FrameworkInterop\WhoopsModule(),
        'priority' => -1,
    ],
    [
        'name' => 'mouf-container',
        'description' => 'Mouf container for Harmony',
        'module' => $moufModule = new Mouf\FrameworkInterop\Module(__DIR__.'/mouf/instances.php', 'Mouf\\AdminContainer', 'src-dev/Mouf/AdminContainer.php', __DIR__.'/mouf/config_tpl.php', __DIR__.'/mouf/variables.php'),
        'priority' => 0,
    ],
    [
        'name' => 'harmony-installer-module',
        'description' => 'Module to redirect to install pages',
        'module' => new Harmony\Installer\InstallerModule(),
        'priority' => 1,
    ],
    [
        'name' => 'splash-module',
        'description' => 'Splash middleware that manages most pages',
        'module' => new Mouf\FrameworkInterop\SplashModule($moufModule),
        'priority' => 2,
    ],
    [
        'name' => 'root-container',
        'description' => 'Facade for the root container',
        'module' => new Mouf\RootContainer\RootContainerModule(),
        'priority' => 3,
    ],
];
