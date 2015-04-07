<?php
namespace Harmony\Proxy;

use Harmony\HarmonyException;
use SuperClosure\Serializer;
use Symfony\Component\Process\PhpProcess;

/**
 * A CodeProxy class is an object enabling you to run code defined in Harmony environment into the application environment.
 * In order to do this, the code is serialized and unserialized in the application environment, then executed.
 *
 * Note: the code is executed in a dedicated process. Therefore, no state is kept between 2 successive calls.
 */
class CodeProxy
{

    /**
     * Executes the code in the application context and returns the result.
     * @param  callable $fn
     * @return mixed
     */
    public function execute(callable $fn)
    {
        // Use the default analyzer.
        $serializer = new Serializer();

        $serializedFn = $serializer->serialize($fn);

        // Let's cheat and pretend the closure is static (in order to avoid an autoloading problem with the parent that is no more.)
        $serializedFn = str_replace('"isStatic";b:0;', '"isStatic";b:1;', $serializedFn);

        $code = '<?php
            require_once "vendor/autoload.php";

            $serializer = new SuperClosure\\Serializer();
            $fn = $serializer->unserialize('.var_export($serializedFn, true).');

            $ret = $fn();
            echo serialize($ret);
        ';

        $process = new PhpProcess($code, __DIR__.'/../../../../../../');

        // Let's increase the performance as much as possible by disabling xdebug.
        // Also, let's set opcache.revalidate_freq to 0 to avoid caching issues with generated files.
        $process->setPhpBinary(PHP_BINARY." -d xdebug.remote_autostart=0 -d xdebug.remote_enable=0 -d opcache.revalidate_freq=0 ");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new HarmonyException("An error occurred while running remote code in application:\n".$process->getErrorOutput());
        }

        $output = $process->getOutput();

        ob_start();
        $obj = unserialize($output);
        $unexpectedOutput = ob_get_clean();

        if ($obj === false) {
            // Is this an unserialized "false" or an error in unserialization?
            if ($output != serialize(false)) {
                throw new HarmonyException("Unable to unserialize message:\n".$output."\n".$unexpectedOutput);
            }
        }

        return $obj;
    }
}
