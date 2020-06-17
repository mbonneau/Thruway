<?php

namespace Thruway\Event;

use React\EventLoop\LoopInterface;
use Thruway\Router\Router;

class RouterStartEvent extends Event
{
    /** @var Router */
    private $router;

    /** @var LoopInterface */
    private $loop;

    /**
     * RouterStartEvent constructor.
     * @param Router $router
     * @param LoopInterface $loop
     */
    public function __construct(Router $router, LoopInterface $loop)
    {
        $this->router = $router;
        $this->loop   = $loop;
    }

    /**
     * @return Router
     */
    public function getRouter(): Router
    {
        return $this->router;
    }

    /**
     * @return LoopInterface
     */
    public function getLoop(): LoopInterface
    {
        return $this->loop;
    }
}
