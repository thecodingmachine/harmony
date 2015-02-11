<?php

namespace Mouf\FrameworkInterop;

use Interop\Framework\HttpModuleInterface;
use Interop\Container\ContainerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Whoops\StackPhp\WhoopsMiddleWare;

/**
 * This module provides a Whoops error handler in the module stack.
 */
class WhoopsModule implements HttpModuleInterface
{

    public function getName()
    {
        return 'whoops';
    }

    public function getContainer(ContainerInterface $rootContainer)
    {
        return null;
    }

    public function init()
    {
    }

    public function getHttpMiddleware(HttpKernelInterface $app) {
        return new WhoopsMiddleWare($app);
    }
}
