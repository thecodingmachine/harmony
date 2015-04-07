<?php
namespace Harmony\Services;

use Composer\Util\Filesystem;
use Mouf\Utils\Cache\CacheInterface;

class ReflectionService
{

    // Lifetime of the cache: very short (20 seconds)
    const CACHE_DURATION = 20;

    private $cache;
    private $reflectionData;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getClassesImplementing($type)
    {
        $reflectionData = $this->getReflectionData();

        $types = [];

        foreach ($reflectionData as $class => $reflection) {
            if ($reflection['type'] == 'class') {
                if ($type == $class) {
                    $types[] = $class;
                }
                if (array_search($type, $reflection['parents']) !== false) {
                    $types[] = $class;
                }
                if (array_search($type, $reflection['interfaces']) !== false) {
                    $types[] = $class;
                }
            }
        }

        return $types;
    }

    /**
     * Returns reflection data for the application.
     *
     * @return array
     */
    public function getReflectionData()
    {
        if ($this->reflectionData === null) {
            // Load precompiled data from vendor.
            $vendorReflectionData = require __DIR__."/../../../generated/vendorReflectionData.php";

            $appReflectionData = $this->cache->get('appReflectionData');

            if ($appReflectionData === false) {
                $appReflectionData = $this->computeAppReflectionData();
                $this->cache->set('appReflectionData', $appReflectionData, self::CACHE_DURATION);
            }

            $this->reflectionData = array_merge($vendorReflectionData, $appReflectionData);
        }

        return $this->reflectionData;
    }

    private function computeAppReflectionData()
    {
        if (file_exists(__DIR__.'/../../../generated/appClassMap.php')) {
            $oldClassMap = include __DIR__.'/../../../harmony/appClassMap.php';
            $oldClassMap = $oldClassMap['classMap'];
        } else {
            $oldClassMap = array();
        }

        $rootPath = __DIR__.'/../../../../../../';
        $composerService = new ComposerService($rootPath.'composer.json');
        $composer = $composerService->getComposer();

        $config = $composer->getConfig();
        $filesystem = new Filesystem();
        $filesystem->ensureDirectoryExists($config->get('vendor-dir'));
        $vendorPath = $filesystem->normalizePath(realpath($config->get('vendor-dir')));

        // Let's get all classes
        $classMapService = new ClassMapService($composer);
        $classMap = $classMapService->getClassMap(ClassMapService::MODE_APPLICATION_CLASSES, $oldClassMap);

        // Let's filter these classes
        $classExplorer = new ClassExplorer();
        $results = $classExplorer->analyze($classMap, $vendorPath.'/autoload.php');

        FileService::writePhpExportFile(__DIR__.'/../../../harmony/generated/appClassMap.php', $results);

        $reflectionExporter = new ReflectionExporter();
        $reflectionData = $reflectionExporter->getReflectionData($results['classMap'], $vendorPath.'/autoload.php');

        return $reflectionData;
    }
}
