<?php

namespace Thruway\Module;

use Thruway\Router\Router;

/**
 * Class RouterModule
 * @package Thruway\RouterModule
 */
class RouterModule implements RouterModuleInterface
{
    /**
     * @var Router
     */
    protected $router;

    /**
     * @param Router $router
     */
    public function initModule(Router $router)
    {
        $this->router = $router;
    }

    /**
     * If people don't want to implement this
     *
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [];
    }
}
