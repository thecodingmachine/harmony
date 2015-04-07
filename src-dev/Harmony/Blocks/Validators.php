<?php
namespace Harmony\Blocks;

use Mouf\Html\HtmlElement\HtmlElementInterface;
use Mouf\Html\Renderer\Renderable;

class Validators implements HtmlElementInterface
{

    use Renderable;

    /**
     * A list of fully qualified class names that implement the StaticValidatorInterface.
     *
     * @var string[]
     */
    protected $classes;

    public function __construct($classes)
    {
        $this->classes = $classes;
    }
}
