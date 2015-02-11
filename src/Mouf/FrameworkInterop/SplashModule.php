<?php

namespace Mouf\FrameworkInterop;

use Interop\Framework\HttpModuleInterface;
use Interop\Container\ContainerInterface;
use Mouf\Mvc\Splash\Routers\SplashDefaultRouter;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * This module provides a base Mouf container in the application.
 */
class SplashModule implements HttpModuleInterface
{
    private $moufModule;
    private $rootContainer;

    public function __construct(Module $moufModule) {
        $this->moufModule = $moufModule;
    }

    public function getName()
    {
        return 'splash';
    }

    public function getContainer(ContainerInterface $rootContainer)
    {
        $this->rootContainer = $rootContainer;
        return null;
    }

    /* (non-PHPdoc)
     * @see \Interop\Framework\ModuleInterface::init()
     */
    public function init()
    {
        // Let's define the 'ROOT_URL' constant
        if (isset($_SERVER['BASE'])) {
            define('ROOT_URL', $_SERVER['BASE']."/");
        } else {
            define('ROOT_URL', "/");
        }

        define('ROOT_PATH', dirname(dirname(dirname(__DIR__))).'/');
    }

    public function getHttpMiddleware(HttpKernelInterface $app) {
        // TODO: enable APC cache!!!
        return new SplashDefaultRouter($app, $this->moufModule->getMoufContainer(), $this->rootContainer->get('filterRepository') /*, $this->rootContainer->get('splashCacheApc')*/);
    }
}
