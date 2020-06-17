<?php

namespace Thruway\Router\Transport;

use Thruway\Message\Message;

/**
 * Class InternalClientTransport
 *
 * @package Thruway\Transport
 */
class InternalClientTransport extends \Thruway\Transport\AbstractTransport
{
    /**
     * Constructor
     *
     * @param callable $sendMessage
     */
    public function __construct(callable $sendMessage)
    {
        $this->sendMessageFunction = $sendMessage;
    }

    /**
     * Send message
     *
     * @param \Thruway\Message\Message $msg
     * @throws \Exception
     */
    public function sendMessage(Message $msg)
    {
        if (is_callable($this->sendMessageFunction)) {
            call_user_func_array($this->sendMessageFunction, [$msg]);
        }
    }

    /**
     * Get transport details
     *
     * @return array
     */
    public function getTransportDetails()
    {
        return [
            'type'             => 'internalClient',
            'transport_address' => 'internal'
        ];
    }

} 