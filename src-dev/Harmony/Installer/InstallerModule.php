<?php
namespace Harmony\Installer;

use Interop\Container\ContainerInterface;
use Interop\Framework\HttpModuleInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class InstallerModule implements HttpModuleInterface
{

    /**
     * You should return a StackPHP middleware.
     *
     * @param $app HttpKernelInterface The kernel your middleware will be wrapping.
     * @return HttpKernelInterface
     */
    public function getHttpMiddleware(HttpKernelInterface $app)
    {
        return new InstallerMiddleware($app);
    }

    /**
     * Returns the name of the module.
     *
     * @return string
     */
    public function getName()
    {
        return "harmony-install-checker";
    }

    /**
     * You can return a container if the module provides one.
     *
     * It will be chained to the application's root container.
     *
     * @param  ContainerInterface      $rootContainer
     * @return ContainerInterface|null
     */
    public function getContainer(ContainerInterface $rootContainer)
    {
        return;
    }

    /**
     * You can provide init scripts here.
     */
    public function init()
    {
    }
}
