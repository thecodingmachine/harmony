<?php
/*
 * This file is part of the Mouf core package.
 *
 * (c) 2012 David Negrier <david@mouf-php.com>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */
namespace Mouf\Controllers;

use Harmony\Proxy\CodeProxy;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Security\UserService\Splash\Logged;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Reflection\MoufReflectionProxy;

/**
 * The controller that will call all validators on Mouf.
 *
 */
class MoufValidatorController extends Controller
{

    /**
     * The validation service.
     *
     * @Property
     * @Compulsory
     * @var MoufValidatorService
     */
    public $validatorService;

    /**
     * The template used by the main page for mouf.
     *
     * @Property
     * @Compulsory
     * @var TemplateInterface
     */
    public $template;

    /**
     * The content block the template will be writting into.
     *
     * @Property
     * @Compulsory
     * @var HtmlBlock
     */
    public $contentBlock;

    /**
     * The default action will redirect to the MoufController defaultAction.
     *
     * @Action
     * @Logged
     */
    public function defaultAction($selfedit = "false")
    {
        // Before running the other validation steps, we should make sure we can successfully cURL
        // into the server, from the server.
        $codeProxy = new CodeProxy();
        $ret = $codeProxy->execute(function () { return "foo"; });

        if ($ret != "foo") {
            $this->contentBlock->addFile(ROOT_PATH."src-dev/views/connection-problem.php", $this);
            $this->template->toHtml();

            return;
        }

        /*if (!MoufReflectionProxy::checkConnection()) {
            $this->contentBlock->addFile(ROOT_PATH."src-dev/views/connection-problem.php", $this);
            $this->template->toHtml();
            return;
        }*/

        if ($selfedit == "true") {
        }
        $this->contentBlock->addFile(ROOT_PATH."src-dev/views/validate.php", $this);
        $this->template->toHtml();
    }
}
