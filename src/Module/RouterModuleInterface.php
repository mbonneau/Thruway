<?php

namespace Thruway\Module;

use Thruway\Event\EventSubscriberInterface;
use Thruway\Router\Router;

/**
 * Interface RouterModuleInterface
 * @package Thruway\RouterModule
 */
interface RouterModuleInterface extends EventSubscriberInterface
{
    /**
     * Called by the router when it is added
     *
     * @param Router $router
     */
    public function initModule(Router $router);
}
