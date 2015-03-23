<?php
/*
 * This file is part of the Mouf core package.
 *
 * (c) 2012 David Negrier <david@mouf-php.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
namespace Harmony\Controllers;

use Harmony\Proxy\CodeProxy;
use Harmony\Services\ClassMapService;
use Mouf\Html\Renderer\Twig\MoufTwigEnvironment;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Moufspector;
use Mouf\MoufManager;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * This controller is in charge of running validators.
 *
 */
class ValidatorsController extends Controller
{

    /**
     * The template used by the main page for mouf.
     *
     * @var TemplateInterface
     */
    private $template;

    /**
     * The content block the template will be writting into.
     *
     * @var HtmlBlock
     */
    private $contentBlock;

    /**
     * @var MoufTwigEnvironment
     */
    private $twigEnvironment;

    /**
     * @param TemplateInterface   $template
     * @param HtmlBlock           $contentBlock
     * @param HtmlBlock           $leftBlock
     * @param MoufTwigEnvironment $twigEnvironment
     */
    public function __construct(TemplateInterface $template, HtmlBlock $contentBlock, MoufTwigEnvironment $twigEnvironment)
    {
        $this->template = $template;
        $this->contentBlock = $contentBlock;
        $this->twigEnvironment = $twigEnvironment;
    }

    /**
     * Returns the complete list of validators to be called.
     *
     * @URL validators/get_list
     */
    public function getValidatorsList()
    {
        $classMapService = new ClassMapService(__DIR__.'/../../../../../../composer.json');
        $list = $classMapService->getClassMap();
        /*
         $codeProxy = new CodeProxy();
        $list = $codeProxy->execute(function() {


            ini_set('display_errors', 1);
            // Add E_ERROR to error reporting it it is not already set
            error_reporting(E_ERROR | error_reporting());

            define('ROOT_URL', '/');

            //$moufManager = MoufManager::getMoufManager();

            $response = array("instances" => array(), "classes" => array());

            define('PROFILE_MOUF', false);

            if (PROFILE_MOUF) {
                error_log("PROFILING: Starting get_validators_list: ".date('H:i:s', time()));
            }

            //$instanceList = $moufManager->findInstances("Mouf\\Validator\\MoufValidatorInterface");
            //$response["instances"] = $instanceList;

            if (PROFILE_MOUF) {
                error_log("PROFILING: findInstance done, starting getComponentsList: ".date('H:i:s', time()));
            }

            // Now, let's get the full list of absolutely all classes implementing "MoufStaticValidatorInterface".
            $classList = Moufspector::getComponentsList("Mouf\\Validator\\MoufStaticValidatorInterface", $selfEdit);
            $response["classes"] = $classList;

            if (PROFILE_MOUF) {
                error_log("PROFILING: Ending get_validators_list: ".date('H:i:s', time()));
            }
            return $response;
        });*/



        return new JsonResponse($list);
    }

}
