<?php
namespace Harmony\Services;

use Composer\Util\Filesystem;
use Harmony\Proxy\CodeProxy;
use Mouf\Utils\Cache\CacheInterface;

/**
 * Service in class of exploring the container(s) of the application (using declared modules).
 */
class ContainerService
{

    // Lifetime of the cache: very short (20 seconds)
    const CACHE_DURATION = 20;

    private $cache;

    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Returns a list of instances implementing `$type`
     *
     * @param string $type
     * @return string[] An array of instances name.
     */
    public function getInstancesImplementing($type) {

        $instances = $this->cache->get('instances_by_type_'.$type);

        if ($instances === null) {
            $instances = $this->_getInstancesImplementingWithoutCache($type);
            $this->cache->set('instances_by_type_'.$type, $instances, self::CACHE_DURATION);
        }
        return $instances;
    }

    /**
     * Returns a list of instances implementing `$type`
     *
     * @param string $type
     * @return string[] An array of instances name.
     */
    public function _getInstancesImplementingWithoutCache($type)
    {
        // Let's first check if a "modules.php" file exist. If not, we can't do anything.
        if (!file_exists(__DIR__.'/../../../../../../modules.php')) {
            return array();
        }

        $codeProxy = new CodeProxy();
        $instances = $codeProxy->execute(function() use ($type) {
            $containerExplorerService = ContainerExplorerService::create();
            return $containerExplorerService->getInstancesByType($type);
        });

        return $instances;
    }
}
