<?php

namespace Thruway\Module;

use React\EventLoop\LoopInterface;
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
     * @param LoopInterface $loop
     */
    public function initModule(Router $router, LoopInterface $loop);

    /**
     * @return LoopInterface
     */
    public function getLoop();
}
