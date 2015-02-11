<?php

namespace Mouf\FrameworkInterop;

/**
 * Simple extension from framework-interop application class to add getters.
 */
class Application extends \Interop\Framework\Application
{
    public function getContainer() {
        return $this->container;
    }

    public function getModules() {
        return $this->modules;
    }

    public function initApp()
    {
        parent::init();
    }
}
