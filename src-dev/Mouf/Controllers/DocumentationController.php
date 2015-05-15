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

use Michelf\MarkdownExtra;
use Composer\Package\PackageInterface;
use Mouf\Html\Template\TemplateInterface;
use Mouf\Html\Widgets\Menu\MenuItem;
use Mouf\Composer\ComposerService;
use MoufAdmin;
use Mouf\MoufManager;
use Mouf\Html\HtmlElement\HtmlBlock;
use Mouf\MoufDocumentationPageDescriptor;
use Mouf\Security\UserService\Splash\Logged;
use Mouf\Mvc\Splash\Controllers\Controller;
use Mouf\DocumentationUtils;

/**
 * The controller displaying the documentation related to packages.
 *
 */
class DocumentationController extends Controller
{

    public $selfedit;

    /**
     * The active MoufManager to be edited/viewed
     *
     * @var MoufManager
     */
    public $moufManager;

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
     * The documentation menu.
     *
     * @Property
     * @Compulsory
     * @var Menu
     */
    public $documentationMenu;

    /**
     *
     * @var MoufPackageManager
     */
    //public $packageManager;

    /**
     *
     * @var array<PackageInterface>
     */
    protected $packageList;

    /**
     * Current package (if we are on view page).
     * @var MoufPackage
     */
    protected $package;

    protected $rootPath;

    /**
     * Displays the list of doc files from the packages
     *
     * @Action
     * @Logged
     * @param string $selfedit
     */
    public function index($selfedit = "false")
    {
        // TODO: CHANGE THE PACKAGE CONTROLLER SO WE CAN VIEW FROM THE APP SCOPE THE PACKAGES THAT ARE REQUESTED ON THE ADMIN SCOPE VIA A <scope>admin</scope> declaration.

        $this->selfedit = $selfedit;

        if ($selfedit == "true") {
            $this->moufManager = MoufManager::getMoufManager();
            $this->rootPath = ROOT_PATH;
        } else {
            $this->moufManager = MoufManager::getMoufManagerHiddenInstance();
            $this->rootPath = ROOT_PATH.'../../../';
        }

        $composerService = new ComposerService($this->selfedit == "true");

        $this->packageList = $composerService->getLocalPackages();

        $this->contentBlock->addFile(ROOT_PATH."src-dev/views/doc/index.php", $this);
        $this->template->toHtml();
    }

    /**
     * Action that is run to view a documentation page.
     *
     * @URL doc/view/*
     * @Logged
     * @param string $selfedit
     */
    public function view($selfedit = "false")
    {
        // First, let's find the list of depending packages.
        $this->selfedit = $selfedit;
        if ($selfedit == "true") {
            $this->moufManager = MoufManager::getMoufManager();
            $this->rootPath = ROOT_PATH;
        } else {
            $this->moufManager = MoufManager::getMoufManagerHiddenInstance();
            $this->rootPath = ROOT_PATH.'../../../';
        }

        $composerService = new ComposerService($this->selfedit == "true");

        $this->packageList = $composerService->getLocalPackages();

        $redirect_uri = $_SERVER['REDIRECT_URL'];

        $pos = strpos($redirect_uri, ROOT_URL);
        if ($pos === false) {
            throw new \Exception('Error: the prefix of the web application "'.$this->splashUrlPrefix.'" was not found in the URL. The application must be misconfigured. Check the ROOT_URL parameter in your MoufUniversalParameters.php file at the root of your project.');
        }

        $tailing_url = substr($redirect_uri, $pos+strlen(ROOT_URL));
        $args = explode("/", $tailing_url);
        // We remove the first 2 parts of the URL (mouf/doc/view)
        array_shift($args);
        array_shift($args);

        $groupName = array_shift($args);
        $packageName = array_shift($args);

        //$this->package = $this->packageManager->getPackage($dirToPackage."package.xml");
        foreach ($this->packageList as $package) {
            if ($package->getName() == $groupName.'/'.$packageName) {
                $this->package = $package;
                break;
            }
        }

        if ($this->package == null) {
            MoufAdmin::getSplash()->print404("No package with this name");

            return;
        }

        $docPath = implode("/", $args);

        $filename = $this->rootPath."vendor/".$groupName."/".$packageName."/".$docPath;

        if (!file_exists($filename)) {
            MoufAdmin::getSplash()->print404("Documentation page does not exist");

            return;
        }
        if (!is_readable($filename)) {
            MoufAdmin::getSplash()->print404("Page not found");

            return;
        }

        if (is_dir($filename)) {
            // This is not a file but a directory.
            // Let's look for a README in it.

            $dir = rtrim($filename, '/\\');
            $root_url = ROOT_URL.rtrim($tailing_url, '/\\');

            // Let's try to find a README
            foreach (DocumentationUtils::$readMeFiles as $readme) {
                if (file_exists($dir.DIRECTORY_SEPARATOR.$readme)) {
                    header('Location: '.$root_url.'/'.$readme);

                    return;
                }
            }
            // If no readme found, let's go on a 404.
            //$this->addMenu ( $parsedComposerJson, $targetDir, $rootUrl, $packageVersion );
            /*if ($path) {
                MoufAdmin::getSplash()->print404("Sorry, this project does not seem to have documentation");
                //$this->http404Handler->pageNotFound ( "Sorry, this project does not seem to have documentation" );
                return;
            } else {*/
                $this->contentBlock->addText("<h4>".$this->package->getName()."</h4>");
            if ($this->package->getDescription()) {
                $this->contentBlock->addText("<p>".htmlentities($this->package->getDescription(), ENT_QUOTES, 'UTF-8')."</p>");
            }
            $this->contentBlock->addText('<div class="alert">Sorry, this project does not seem to have any documentation. Please bang the head of the developers until a proper README is added to this package!</div>');
            $this->template->toHtml();

            return;
            //}
        }

        $this->contentBlock->addText(
                "
				<script>
				$(document).ready(function() {
				$('pre code').each(function(i, e) {hljs.highlightBlock(e)});
				});
				</script>
				"
        );

        if (strripos($filename, ".html") !== false || strripos($filename, ".md") !== false || strripos($filename, "README") !== false) {
            $previousNextButtonsHtml = $this->getPreviousNextButtons($docPath, $this->package, ROOT_URL.'doc/view/'.$groupName.'/'.$packageName.'/');
            $this->addMenu();

            $fileStr = file_get_contents($filename);

            if (strripos($filename, ".md") !== false) {
                // The line below is a workaround around a bug in markdown implementation.
                $forceautoload = new \ReflectionClass('\\Michelf\\Markdown');

                $markdownParser = new MarkdownExtra();

                $fileStr = str_replace('```', '~~~', $fileStr);

                // Let's parse and transform markdown format in HTML
                $fileStr = $markdownParser->transform($fileStr);

                $fileStr = $previousNextButtonsHtml.$fileStr.$previousNextButtonsHtml;

                $this->contentBlock->addText('<div class="staticwebsite">'.$fileStr.'</div>');
                $this->template->toHtml();
            } else {
                $bodyStart = strpos($fileStr, "<body");
                if ($bodyStart === false) {
                    $this->contentBlock->addText('<div class="staticwebsite">'.$fileStr.'</div>');
                    $this->template->toHtml();
                } else {
                    $bodyOpenTagEnd = strpos($fileStr, ">", $bodyStart);

                    $partBody = substr($fileStr, $bodyOpenTagEnd+1);

                    $bodyEndTag = strpos($partBody, "</body>");
                    if ($bodyEndTag === false) {
                        return '<div class="staticwebsite">'.$partBody.'</div>';
                    }
                    $body = substr($partBody, 0, $bodyEndTag);

                    $body = $previousNextButtonsHtml.$body.$previousNextButtonsHtml;

                    $this->contentBlock->addText('<div class="staticwebsite">'.$body.'</div>');
                    $this->template->toHtml();
                }
            }
        } elseif (strripos($filename, ".php") !== false) {
            // PHP files are not accessible
            MoufAdmin::getSplash()->print404("Cannot access PHP file through doc");

            return;
        } else {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            switch (strtolower($extension)) {
                case 'png':
                    header("Content-Type: image/png");
                    break;
                case 'jpg':
                case 'jpeg':
                    header("Content-Type: image/jpeg");
                    break;
                case 'gif':
                    header("Content-Type: image/gif");
                    break;
            }

            header('Content-Length: '.filesize($filename));
            readfile($filename);
            exit;
        }
    }

    protected function addMenu()
    {
        $docPages = $this->getDocPages($this->package);

        $documentationMenuMainItem = new MenuItem("Documentation for ".$this->package->getPrettyName());
        DocumentationUtils::fillMenu($documentationMenuMainItem, $docPages, $this->package->getName());
        $this->documentationMenu->addChild($documentationMenuMainItem);
    }

    /**
     * Returns an array of doc pages with the format:
     * 	[
     *   		{
     *   			"title": "Using FINE",
     *   			"url": "using_fine.html"
     *   		},
     *   		{
     *   			"title": "Date functions",
     *   			"url": "date_functions.html"
     *   		},
     *   		{
     *   			"title": "Currency functions",
     *   			"url": "currency_functions.html"
     *   		}
     *   	]
     *
     * @param \Composer\Package\PackageInterface $package
     */
    protected function getDocPages(PackageInterface $package)
    {
        $extra = $package->getExtra();

        $packagePath = $this->rootPath."vendor/".$package->getName()."/";

        return DocumentationUtils::getDocPages($extra, $packagePath);
    }

    protected function getLink(MoufDocumentationPageDescriptor $documentationPageDescriptor)
    {
        $link = $documentationPageDescriptor->getURL();
        if (strpos($link, "/") === 0
            || strpos($link, "http://") === 0
            || strpos($link, "https://") === 0
            || strpos($link, "javascript:") === 0) {
            return $link;
        }

        return ROOT_URL."mouf/doc/view/".$documentationPageDescriptor->getPackage()->getPackageDirectory()."/".$link;
    }

    /**
     * Display the doc links for one package.
     *
     * @param array<string, string> $docPages
     * @param string                $packageName
     */
    public function displayDocDirectory($docPages, $packageName)
    {
        ?>
<ul>
		<?php
        foreach ($docPages as $docPage):
            $url = $docPage['url'];
        $title = $docPage['title'];
        ?>
			<li>
			<?php
            if ($url) {
                echo "<a href='view/".$packageName."/".$url."'>";
            }
        echo $title;
        if ($url) {
            echo "</a>";
        }
            /*if ($docPage->getChildren()) {
                displayDocDirectory($docPage->getChildren());
            }*/
            ?>
			</li>
			<?php
        endforeach;
        ?>
		</ul>
<?php

    }

    private function getPreviousNextButtons($path, $package, $rootUrl)
    {
        $extra = $package->getExtra();
        if (isset($extra['mouf']['doc'])) {
            // TODO: suboptimal, getDocPages is called twice. We should pass $docPages directly in parameter.
            $docPages = $this->getDocPages($package);

            // Let's flatten the doc array (to find previous and next in children or parents.
            $flatDocArray = $this->flattenDocArray($docPages);
            for ($i = 0; $i<count($flatDocArray); $i++) {
                if ($flatDocArray[$i]['url'] == $path) {
                    $html = '<div>';
                    if ($i > 0) {
                        $html .= '<a href="'.$rootUrl.$flatDocArray[$i-1]['url'].'" class="btn btn-mini"><i class="icon-chevron-left"></i> '.$flatDocArray[$i-1]['title'].'</a>';
                    }
                    if ($i < count($flatDocArray) - 1) {
                        $html .= '<a href="'.$rootUrl.$flatDocArray[$i+1]['url'].'" class="btn btn-mini pull-right">'.$flatDocArray[$i+1]['title'].' <i class="icon-chevron-right"></i></a>';
                    }
                    $html .= '</div>';

                    return $html;
                }
            }
        }

        return "";
    }

    private function flattenDocArray(array $docArray)
    {
        $docs = array();
        foreach ($docArray as $doc) {
            $docs[] = $doc;
            if (isset($doc['children'])) {
                $docs = array_merge($docs, $this->flattenDocArray($doc['children']));
            }
        }

        return $docs;
    }
}
