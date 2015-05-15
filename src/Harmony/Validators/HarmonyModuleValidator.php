<?php
namespace Harmony\Validators;


use Harmony\Validator\StaticValidatorInterface;
use Harmony\Validator\ValidatorResult;
use Harmony\Validator\ValidatorResultInterface;

/**
 * This class validates that there is a modules.php file in your project.
 */
class HarmonyModuleValidator implements StaticValidatorInterface {

    /**
     * Runs the validation of the class.
     * Returns a ValidatorResult object explaining the result, or an array
     * of ValidatorResult objects.
     *
     * @return ValidatorResultInterface|ValidatorResultInterface[]
     */
    public static function validateClass()
    {
        if (!file_exists(__DIR__."/../../../../../../modules.php")) {
            return new ValidatorResult(ValidatorResult::WARN,
                "<strong>Harmony modules</strong> Unable to find the <code>modules.php</code> file at the root of your " .
                "project. Harmony can connect to your code through modules. We strongly recommend that you install one " .
                "for your framework.",
                "Harmony modules: Unable to find the modules.php file at the root of your " .
                "project. Harmony can connect to your code through modules. We strongly recommend that you install one " .
                "for your framework."
                );
        } else {
            return new ValidatorResult(ValidatorResult::SUCCESS,
                "<strong>Harmony modules</strong>: Found <code>modules.php</code> file",
                "Harmony modules: Found modules.php file");
        }
    }
}
