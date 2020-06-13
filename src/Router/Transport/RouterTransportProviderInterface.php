<?php

namespace Thruway\Router\Transport;

use Thruway\Module\RouterModuleInterface;

interface RouterTransportProviderInterface extends RouterModuleInterface
{
    /**
     * @param boolean $trusted
     */
    public function setTrusted($trusted);
}
