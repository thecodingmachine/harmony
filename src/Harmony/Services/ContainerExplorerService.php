<?php
namespace Harmony\Services;

use Acclimate\Container\CompositeContainer;
use Composer\Util\Filesystem;
use Harmony\HarmonyException;
use Harmony\Module\HarmonyModuleInterface;
use Interop\Container\ContainerInterface;
use Mouf\Utils\Cache\CacheInterface;

/**
 * Service in class of exploring the container(s) of the application (using declared modules).
 */
class ContainerExplorerService
{
    /**
     * List of modules of the application.
     *
     * @var HarmonyModuleInterface[]
     */
    private $modules;

    /**
     * @var ContainerInterface
     */
    private $compositeContainer;

    /**
     * @param array $modules List of modules of the application.
     */
    public function __construct(array $modules)
    {
        $this->modules = $modules;
    }

    /**
     * Returns an instance of ContainerExplorerService build from the "modules.php" file at the root of the project.
     * @param string $modulesFile
     */
    public static function create($modulesFile = null) {
        if ($modulesFile === null) {
            $modulesFile = __DIR__.'/../../../../../../modules.php';
        }
        if (!file_exists($modulesFile)) {
            throw new HarmonyException("Could not find file '".$modulesFile."'");
        }

        $modules = require $modulesFile;

        return new self($modules);
    }

    /**
     * Returns the name of the instances implementing `$type`, searching in all the available modules.
     *
     * @return string[]
     */
    public function getInstancesByType($type) {
        $instances = [];
        foreach ($this->modules as $module) {
            $instancesForModule = $module->getContainerExplorer()->getInstancesByType($type);
            $instances = array_merge($instances, $instancesForModule);
        }
        // Remove duplicate instances
        $instances = array_flip(array_flip($instances));
        return $instances;
    }

    /**
     * Returns a composite container aggregating all containers of all modules.
     *
     * @return ContainerInterface
     */
    public function getCompositeContainer() {
        if ($this->compositeContainer === null) {
            $this->compositeContainer = new CompositeContainer();
            foreach ($this->modules as $module) {
                $this->compositeContainer->addContainer($module->getContainer($this->compositeContainer));
            }
        }
        return $this->compositeContainer;
    }
}
