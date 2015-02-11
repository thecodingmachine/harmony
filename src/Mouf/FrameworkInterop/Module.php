<?php

namespace Mouf\FrameworkInterop;

use Interop\Framework\ModuleInterface;
use Mouf\AdminContainer;
use Mouf\MoufContainer;
use Mouf\MoufManager;
use Interop\Container\ContainerInterface;

/**
 * This module provides a base Mouf container in the application.
 */
class Module implements ModuleInterface
{
    private $rootContainer;
    private $instancesFile;
    private $className;
    private $classFile;
    private $configTplFile;
    private $moufContainer;

    /**
     * @param string $instancesFile
     * @param string $className
     * @param string $classFile Path to file containing the class, relative to ROOT_PATH.
     * @param string $configTplFile
     * @param string $variablesFile
     */
    public function __construct($instancesFile, $className, $classFile, $configTplFile, $variablesFile) {
        $this->instancesFile = $instancesFile;
        $this->className = $className;
        $this->classFile = $classFile;
        $this->configTplFile = $configTplFile;
        $this->variablesFile = $variablesFile;
    }

    public function getName()
    {
        return 'mouf';
    }

    public function getContainer(ContainerInterface $rootContainer)
    {
        $this->rootContainer = $rootContainer;

        $className = $this->className;
        $this->moufContainer = new $className($rootContainer);
        return $this->moufContainer;
    }

    /* (non-PHPdoc)
     * @see \Interop\Framework\ModuleInterface::init()
     */
    public function init()
    {
    }

    /**
     * @return MoufContainer
     */
    public function getMoufContainer() {
        return $this->moufContainer;
    }
}
