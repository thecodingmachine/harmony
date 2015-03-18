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

        $code = '<?php
            require_once "vendor/autoload.php";

            $serializer = new SuperClosure\\Serializer();
            $fn = $serializer->unserialize('.var_export($serializedFn, true).');

            $ret = $fn();
            echo serialize($fn);
        ';

        $process = new PhpProcess($code, __DIR__.'/../../../../../../');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new HarmonyException("An error occurred while running remote code in application:\n".$process->getErrorOutput());
        }

        $output = $process->getOutput();

        $obj = @unserialize($output);

        if ($obj === false) {
            // Is this an unserialized "false" or an error in unserialization?
            if ($output != serialize(false)) {
                throw new HarmonyException("Unable to unserialize message:\n".$output);
            }
        }

        return $obj;
    }
}
