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

use Mouf\Html\Widgets\MessageService\Service\UserMessageInterface;

use Mouf\Installer\AbstractInstallTask;

use Mouf\Installer\ComposerInstaller;

use Mouf\Html\HtmlElement\HtmlBlock;

use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\Mvc\Splash\HtmlResponse;


/**
 * This controller displays the Mouf install process (when Mouf is started the first time).
 *
 */
class MoufInstallController extends Controller {

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
	 * @param $template
	 * @param $contentBlock
	 * @param $twigEnvironment
	 */
	public function __construct(TemplateInterface $template, HtmlBlock $contentBlock, MoufTwigEnvironment $twigEnvironment)
	{
		$this->template = $template;
		$this->contentBlock = $contentBlock;
		$this->twigEnvironment = $twigEnvironment;
	}


	/**
	 * Displays the page to install Mouf.
	 * Note: this is not a typical controller. This controller is called directly from index.php
	 *
	 * @URL install/
	 */
	public function index() {

		$root_path = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
		$harmonyUsersFile = $root_path.'/harmony/no_commit/HarmonyUsers.php';


		try {
			FileService::detectWriteIssues($harmonyUsersFile);
		} catch(FileNotWritableException $ex) {
			$dirname = $ex->getNotWritableFile();
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
			$phpUserMemberOfGroup = array_search($phpUser['name'], $group['members']);

			$this->contentBlock->addHtmlElement(new TwigTemplate($this->twigEnvironment, 'src-dev/views/harmony_installer/rights_issue.twig',
				array(
					"dirname"=>$dirname,
					"harmonyUsersFile"=>$harmonyUsersFile,
					"isDirectory"=>$isDir,
					"isWin"=>$isWin,
					"group"=>$group,
					"user"=>$user,
					"phpUser"=>$phpUser,
					"userWritable"=>($perms & 0x0080),
					"groupWritable"=>($perms & 0x0010),
					"rootPath"=>$root_path,
					"phpUserMemberOfGroup"=>$phpUserMemberOfGroup
				)));
			return new HtmlResponse($this->template);
		}

		if (!extension_loaded("curl")) {
			$this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/missing_curl.php", $this);
		} else {
			$this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/welcome.php", $this);
		}

		$this->template->toHtml();	
	}

	/**
	 * This page displays an error saying the .htaccess file was ignored by Apache.
	 * 
	 */
	public function htaccessNotDetected() {
		$this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/missing_htaccess.php", $this);
		
		$this->template->toHtml();
	}
	
	/**
	 * Performs the installation by creating all required files.
	 * 
	 * @URL install/install
	 */
	public function install() {
		if (file_exists(__DIR__.'/../../../../../../harmony/no_commit/HarmonyUsers.php')) {
			$this->contentBlock->addFile(dirname(__FILE__)."/../../views/mouf_installer/moufusers_exists.php", $this);
			$this->template->toHtml();
			return;
		}
		
		$oldUmask = umask();
		umask(0);
		
		// Now, let's write the basic Mouf files:
		if (!file_exists(__DIR__."/../../../../../../mouf")) {
			mkdir(__DIR__."/../../../../../../mouf", 0775);
		}
		if (!file_exists(__DIR__."/../../../../../../mouf/no_commit")) {
			mkdir(__DIR__."/../../../../../../mouf/no_commit", 0775);
		}
		
		
		// Write Mouf.php (always):
		//if (!file_exists(__DIR__."/../../../../../../mouf/Mouf.php")) {
			$moufStr = "<?php
define('ROOT_PATH', realpath(__DIR__.'/..').DIRECTORY_SEPARATOR);
require_once __DIR__.'/../config.php';
if (defined('ROOT_URL')) {
	define('MOUF_URL', ROOT_URL.'vendor/mouf/mouf/');
}
		
require_once __DIR__.'/../vendor/autoload.php';
		
require_once 'MoufComponents.php';
?>";
		
			file_put_contents(__DIR__."/../../../../../../mouf/Mouf.php", $moufStr);
			// Change rights on Mouf.php, but ignore errors (the file might be writable but still belong to someone else).
			@chmod(__DIR__."/../../../../../../mouf/Mouf.php", 0664);
		//}
		
		
		
		// Write MoufComponents.php:
		if (!file_exists(__DIR__."/../../../../../../mouf/MoufComponents.php")) {
			$moufComponentsStr = "<?php
/**
 * This is a file automatically generated by the Mouf framework. Do not modify it, as it could be overwritten.
 */
		
use Mouf\MoufManager;
MoufManager::initMoufManager();
\$moufManager = MoufManager::getMoufManager();
		
?>";
		
			file_put_contents(__DIR__."/../../../../../../mouf/MoufComponents.php", $moufComponentsStr);
			chmod(__DIR__."/../../../../../../mouf/MoufComponents.php", 0664);
		}
		
		// Finally, let's generate the MoufUI.php file:
		if (!file_exists(__DIR__."/../../../../../../mouf/MoufUI.php")) {
			$moufUIStr = "<?php
/**
 * This is a file automatically generated by the Mouf framework. Do not modify it, as it could be overwritten.
 */
		
	?>";
		
			file_put_contents(__DIR__."/../../../../../../mouf/MoufUI.php", $moufUIStr);
			chmod(__DIR__."/../../../../../../mouf/MoufUI.php", 0664);
		}
		
		// Finally 2, let's generate the config.php file:
		if (!file_exists(__DIR__."/../../../../../../config.php")) {
			$moufConfig = "<?php
/**
 * This is a file automatically generated by the Mouf framework. Do not modify it, as it could be overwritten.
 * Use the UI to edit it instead.
 */
		
?>";
		
			file_put_contents(__DIR__."/../../../../../../config.php", $moufConfig);
			chmod(__DIR__."/../../../../../../config.php", 0664);
		}
		
		// Finally 3 :), let's generate the MoufUsers.php file:
		if (!file_exists(__DIR__."/../../../../../../harmony/no_commit/HarmonyUsers.php")) {
			$moufConfig = "<?php
/**
 * This contains the users allowed to access the Mouf framework.
 */
\$users[".var_export(userinput_to_plainstring($_REQUEST['login']), true)."] = array('password'=>".var_export(sha1(userinput_to_plainstring($_REQUEST['password'])), true).", 'options'=>null);
		
?>";
		
			file_put_contents(__DIR__."/../../../../../../harmony/no_commit/HarmonyUsers.php", $moufConfig);
			chmod(__DIR__."/../../../../../../harmony/no_commit/HarmonyUsers.php", 0664);
		}
		
		umask($oldUmask);
		
		header("Location: ".ROOT_URL);
		
		
	}
}
