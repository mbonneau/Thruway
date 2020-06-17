<?php

namespace Thruway\Event;

use React\EventLoop\LoopInterface;
use Thruway\Router\Router;

class RouterStartEvent extends Event
{
    /** @var Router */
    private $router;

    /**
     * RouterStartEvent constructor.
     * @param Router $router
     * @param LoopInterface $loop
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }
}
