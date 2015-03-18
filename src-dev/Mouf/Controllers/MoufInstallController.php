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

use Harmony\Services\FileNotWritableException;
use Harmony\Services\FileService;
use Mouf\Html\Renderer\Twig\MoufTwigEnvironment;
use Mouf\Html\Renderer\Twig\TwigTemplate;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\HtmlResponse;
use Mouf\Security\UserFileDao\UserFileBean;
use Mouf\Security\UserFileDao\UserFileDao;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * This controller displays the Mouf install process (when Mouf is started the first time).
 *
 */
class MoufInstallController extends Controller
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
     * @var UserFileDao
     */
    private $userFileDao;

    /**
     * @param $template
     * @param $contentBlock
     * @param $twigEnvironment
     */
    public function __construct(TemplateInterface $template, HtmlBlock $contentBlock, MoufTwigEnvironment $twigEnvironment, UserFileDao $userFileDao)
    {
        $this->template = $template;
        $this->contentBlock = $contentBlock;
        $this->twigEnvironment = $twigEnvironment;
        $this->userFileDao = $userFileDao;
    }

    /**
     * Displays the page to install Mouf.
     * Note: this is not a typical controller. This controller is called directly from index.php
     *
     * @URL install/
     */
    public function index()
    {
        $root_path = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
        $harmonyUsersFile = $this->userFileDao->getUserFilePath();

        try {
            FileService::detectWriteIssues($harmonyUsersFile);
        } catch (FileNotWritableException $ex) {
            $dirname = $ex->getPath();
            $isDir = is_dir($dirname);
            $stat = stat($dirname);
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $isWin = true;
            } else {
                $isWin = false;
            }
            $user = posix_getpwuid($stat['uid']);
            $group = posix_getgrgid($stat['gid']);
            $phpUser = posix_getpwuid(posix_geteuid());
            $perms = fileperms($dirname);
            $phpUserMemberOfGroup = array_search($phpUser['name'], $group['members']) !== false;

            $this->contentBlock->addHtmlElement(new TwigTemplate($this->twigEnvironment, 'src-dev/views/harmony_installer/rights_issue.twig',
                array(
                    "dirname" => $dirname,
                    "harmonyUsersFile" => $harmonyUsersFile,
                    "isDirectory" => $isDir,
                    "isWin" => $isWin,
                    "group" => $group,
                    "user" => $user,
                    "phpUser" => $phpUser,
                    "userWritable" => ($perms & 0x0080),
                    "groupWritable" => ($perms & 0x0010),
                    "rootPath" => $root_path,
                    "phpUserMemberOfGroup" => $phpUserMemberOfGroup,
                )));

            return new HtmlResponse($this->template);
        }

        if (!extension_loaded("curl")) {
            $this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/missing_curl.php", $this);
        } else {
            $this->contentBlock->addHtmlElement(new TwigTemplate($this->twigEnvironment, 'src-dev/views/harmony_installer/welcome.twig',
                array(
                    "isUserfileAvailable" => $this->userFileDao->isUserFileAvailable(),
                    "harmonyUsersFile" => $harmonyUsersFile,
                )));

            //$this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/welcome.php", $this);
        }

        return new HtmlResponse($this->template);
    }

    /**
     * This page displays an error saying the .htaccess file was ignored by Apache.
     *
     */
    public function htaccessNotDetected()
    {
        $this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/missing_htaccess.php", $this);

        $this->template->toHtml();
    }

    /**
     * Performs the installation by creating all required files.
     *
     * @URL install/install
     */
    public function install($login, $password)
    {
        if ($this->userFileDao->isUserFileAvailable()) {
            $this->contentBlock->addFile(__DIR__."/../../views/mouf_installer/moufusers_exists.php", $this);
            $this->template->toHtml();

            return;
        }

        $harmonyUsersFile = $this->userFileDao->getUserFilePath();

        $userFileBean = new UserFileBean($login);
        $userFileBean->setClearTextPassword($password);
        $this->userFileDao->registerUser($userFileBean);

        $oldUmask = umask();
        umask(0);
        $this->userFileDao->write();
        chmod($harmonyUsersFile, 0664);

        umask($oldUmask);

        return new RedirectResponse(ROOT_URL);
    }
}
