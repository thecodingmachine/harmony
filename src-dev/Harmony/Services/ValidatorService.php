<?php
namespace Harmony\Services;

use Harmony\Proxy\CodeProxy;
use Harmony\Validator\StaticValidatorInterface;
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
     * @param ReflectionService $reflectionService
     */
    public function __construct(ReflectionService $reflectionService)
    {
        $this->reflectionService = $reflectionService;
    }

    /**
     * Returns the list of all classes implementing the StaticValidatorInterface
     */
    public function getValidators()
    {
        return $this->reflectionService->getClassesImplementing("Harmony\\Validator\\StaticValidatorInterface");
    }

    /**
     * Runs a remote validator for class $className.
     * If no class is passed, runs a validator on ALL classes implementing StaticValidatorInterface
     *
     * @param string $className A class name or an array of class names or null.
     */
    public function validate($className = null)
    {
        if ($className === null) {
            $classes = $this->getValidators();
        } elseif (!is_array($className)) {
            $classes = [ $className ];
        } else {
            $classes = $className;
        }

        return $this->doValidate($classes);
    }

    private function doValidate($classes)
    {
        $codeProxy = new CodeProxy();
        $ret = $codeProxy->execute(function () use ($classes) {
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

            return $results;
        });

        return $ret;
    }
}
