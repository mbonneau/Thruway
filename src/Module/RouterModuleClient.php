<?php

namespace Thruway\Module;

use React\EventLoop\LoopInterface;
use Thruway\Peer\Client;
use Thruway\Router\Router;

/**
 * Class RouterModuleClient
 * @package Thruway\Module
 */
class RouterModuleClient extends Client implements RouterModuleInterface
{
    /** @var  Router */
    protected $router;

    /**
     * Called by the router when it is added
     *
     * @param Router $router
     * @param LoopInterface $loop
     */
    public function initModule(Router $router, LoopInterface $loop)
    {
        $this->router = $router;
        $this->setLoop($loop);

        $this->router->addInternalClient($this);
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
