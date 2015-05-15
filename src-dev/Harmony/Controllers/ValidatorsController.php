<?php
/*
 * This file is part of the Mouf core package.
 *
 * (c) 2012-2015 David Negrier <david@mouf-php.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
namespace Harmony\Controllers;

use Harmony\Blocks\Validators;
use Harmony\Services\ValidatorService;
use Harmony\Validator\ValidatorResultInterface;
use Mouf\Html\Renderer\Twig\MoufTwigEnvironment;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\HtmlResponse;
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
     * @var ValidatorService
     */
    private $validatorService;

    /**
     * @param TemplateInterface   $template
     * @param HtmlBlock           $contentBlock
     * @param HtmlBlock           $leftBlock
     * @param MoufTwigEnvironment $twigEnvironment
     */
    public function __construct(TemplateInterface $template, HtmlBlock $contentBlock, MoufTwigEnvironment $twigEnvironment,
        ValidatorService $validatorService)
    {
        $this->template = $template;
        $this->contentBlock = $contentBlock;
        $this->twigEnvironment = $twigEnvironment;
        $this->validatorService = $validatorService;
    }

    /**
     * Returns the complete list of validators to be called.
     *
     * @URL welcome
     */
    public function index()
    {
        $validatorsBlock = new Validators();

        $this->template->getWebLibraryManager()->addJsFile("vendor/bower_components/angular/angular.min.js");
        $this->template->getWebLibraryManager()->addJsFile("vendor/bower_components/angular-ui-bootstrap-bower/ui-bootstrap.min.js");
        $this->template->getWebLibraryManager()->addJsFile("vendor/bower_components/angular-ui-bootstrap-bower/ui-bootstrap-tpls.min.js");

        $this->template->getWebLibraryManager()->addJsFile("src-dev/views/validators/validators-app.js");
        $this->template->getWebLibraryManager()->addJsFile("src-dev/views/validators/validators-controller.js");
        $this->contentBlock->addHtmlElement($validatorsBlock);

        return new HtmlResponse($this->template);
    }

    /**
     * Returns the complete list of validators to be called.
     *
     * @URL validators/get_list
     */
    public function getValidatorsList()
    {
        return new JsonResponse([
            "classes" => $this->validatorService->getClassValidators(),
            "instances" => $this->validatorService->getInstanceValidators()
        ]);
    }

    /**
     *
     * @URL validators/get_class
     *
     * @param string $class
     * @return JsonResponse
     */
    public function getClassValidator($class)
    {
        return new JsonResponse($this->toJson($this->validatorService->validateClass($class)));
    }

    /**
     *
     * @URL validators/get_instance
     *
     * @param string $instance
     * @return JsonResponse
     */
    public function getInstanceValidator($instance)
    {
        return new JsonResponse($this->toJson($this->validatorService->validateInstance($instance)));
    }

    /**
     * @param ValidatorResultInterface[] $validators
     */
    private function toJson(array $validators) {
        return array_map(function(ValidatorResultInterface $result) {
            return array(
                "code"=>$result->getCode(),
                "htmlMessage"=>$result->getHtmlMessage(),
                "textMessage"=>$result->getTextMessage(),
            );
        }, $validators);
    }
}
