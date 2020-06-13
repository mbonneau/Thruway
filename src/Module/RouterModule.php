<?php

namespace Thruway\Module;

use React\EventLoop\LoopInterface;
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
     * @var LoopInterface
     */
    protected $loop;

    /**
     * @param Router $router
     * @param LoopInterface $loop
     */
    public function initModule(Router $router, LoopInterface $loop)
    {
        $this->router = $router;
        $this->loop   = $loop;
    }

    /**
     * @return \React\EventLoop\LoopInterface
     */
    public function getLoop()
    {
        return $this->loop;
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
