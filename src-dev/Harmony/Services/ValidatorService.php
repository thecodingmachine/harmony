<?php
namespace Harmony\Services;

use Harmony\Proxy\CodeProxy;
use Harmony\Validator\StaticValidatorInterface;
use Harmony\Validator\ValidatorInterface;
use Harmony\Validator\ValidatorResult;
use Harmony\Validator\ValidatorException;

/**
 * This class is in charge of running Harmony validators and returning a result.
 */
class ValidatorService
{

    /**
     * @var ReflectionService
     */
    private $reflectionService;

    /**
     * @var ContainerService
     */
    private $containerService;

    /**
     * @param ReflectionService $reflectionService
     */
    public function __construct(ReflectionService $reflectionService, ContainerService $containerService)
    {
        $this->reflectionService = $reflectionService;
        $this->containerService = $containerService;
    }

    /**
     * Returns the list of all classes implementing the StaticValidatorInterface
     */
    public function getClassValidators()
    {
        return $this->reflectionService->getClassesImplementing("Harmony\\Validator\\StaticValidatorInterface");
    }

    /**
     * Returns the list of all instances implementing the ValidatorInterface
     */
    public function getInstanceValidators() {
        return $this->containerService->getInstancesImplementing("Harmony\\Validator\\ValidatorInterface");
    }

    /**
     * Runs a remote validator for class $className.
     *
     * @param string $className A class name
     */
    public function validateClass($className)
    {
        return $this->doValidate([ $className ], []);
    }

    /**
     * Runs a remote validator for instance $instanceName.
     *
     * @param string $instanceName An instance name
     */
    public function validateInstance($instanceName)
    {
        return $this->doValidate([], [ $instanceName ]);
    }

    /**
     * Runs all validators.
     *
     * @return ValidatorResult[]
     */
    public function validateAll() {
        $classes = $this->getClassValidators();
        $instances = $this->getInstanceValidators();
        return $this->doValidate($classes, $instances);
    }

    /**
     * @param array $classes
     * @param array $instances
     * @return ValidatorResult[]
     * @throws \Harmony\HarmonyException
     */
    private function doValidate(array $classes, array $instances)
    {
        $codeProxy = new CodeProxy();
        $ret = $codeProxy->execute(function () use ($classes, $instances) {
            $results = [];
            foreach ($classes as $class) {
                /* @var $class StaticValidatorInterface */
                try {
                    $result = call_user_func([ $class, "validateClass"]);
                } catch (\Exception $e) {
                    $result = new ValidatorResult(ValidatorResult::ERROR,
                        "An exception was triggered while running validation for class '$class': '".$e->getMessage()."'<pre>".$e->getTraceAsString()."</pre>",
                        "An exception was triggered while running validation for class '$class': '".$e->getMessage()."'\n".$e->getTraceAsString());
                }
                if (!is_array($result)) {
                    $result = [ $result ];
                }
                foreach ($result as $item) {
                    if (!$item instanceof ValidatorResult) {
                        throw new ValidatorException("Error while running validator for class '$class', expected a ValidatorResult or an array of ValidatorResult as answer.");
                    }
                }
                $results = array_merge($results, $result);
            }

            $containerExplorerService = ContainerExplorerService::create();
            foreach ($instances as $instanceName) {
                /* @var $instance ValidatorInterface */

                $instance = $containerExplorerService->getCompositeContainer()->get($instanceName);

                try {
                    $result = $instance->validateInstance();
                } catch (\Exception $e) {
                    $result = new ValidatorResult(ValidatorResult::ERROR,
                        "An exception was triggered while running validation for instance '$instanceName': '".$e->getMessage()."'<pre>".$e->getTraceAsString()."</pre>",
                        "An exception was triggered while running validation for instance '$instanceName': '".$e->getMessage()."'\n".$e->getTraceAsString());
                }
                if (!is_array($result)) {
                    $result = [ $result ];
                }
                foreach ($result as $item) {
                    if (!$item instanceof ValidatorResult) {
                        throw new ValidatorException("Error while running validator for instance '$instanceName', expected a ValidatorResult or an array of ValidatorResult as answer.");
                    }
                }
                $results = array_merge($results, $result);
            }

            return $results;
        });

        return $ret;
    }
}
