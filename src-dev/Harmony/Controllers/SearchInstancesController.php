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

use Harmony\Services\ContainerService;
use Harmony\Validator\ValidatorResultInterface;
use Mouf\Html\Renderer\Twig\MoufTwigEnvironment;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Mvc\Splash\Controllers\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * This controller is in charge of running validators.
 *
 */
class SearchInstancesController extends Controller
{

    /**
     * @var ContainerService
     */
    private $containerService;

    /**
     * @param ContainerService $containerService
     */
    public function __construct(ContainerService $containerService)
    {
        $this->containerService = $containerService;
    }

    /**
     * Returns the complete list of instances implementing type $class
     *
     * @URL get_instances
     * @param string $type
     * @return JsonResponse
     */
    public function index($type)
    {
        $instances = $this->containerService->getInstancesImplementing($type);
        return new JsonResponse($instances);
    }

}
