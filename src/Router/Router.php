<?php

namespace Thruway\Router;

use Thruway\Common\Utils;
use Thruway\Event\ConnectionCloseEvent;
use Thruway\Event\EventDispatcher;
use Thruway\Event\EventDispatcherInterface;
use Thruway\Event\EventSubscriberInterface;
use Thruway\Event\ConnectionOpenEvent;
use Thruway\Event\RouterStartEvent;
use Thruway\Event\RouterStopEvent;
use Thruway\Logging\Logger;
use Thruway\Module\RouterModuleClient;
use Thruway\Module\RouterModuleInterface;
use Thruway\RealmManager;
use Thruway\Session;
use Thruway\Router\Transport\InternalClientTransportProvider;
use Thruway\Router\Transport\TransportInterface;

/**
 * Class Router
 *
 */
class Router implements EventSubscriberInterface
{
    /** @var \Thruway\RealmManager */
    private $realmManager;

    /** @var array */
    private $sessions = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var RouterModuleInterface[] */
    private $modules = [];

    public function __construct(array $modules = [])
    {
        Utils::checkPrecision();

        $this->realmManager    = new RealmManager();
        $this->eventDispatcher = new EventDispatcher();

        $this->eventDispatcher->addSubscriber($this);

        $this->registerModule($this->realmManager);

        foreach ($modules as $module) {
            $this->registerModule($module);
        }

        Logger::debug($this, 'New router created');

        $this->eventDispatcher->dispatch('router.start', new RouterStartEvent($this));
    }

    /**
     * Register for events
     *
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            'connection_open'  => ['handleConnectionOpen', 10],
            'connection_close' => ['handleConnectionClose', 10]
        ];
    }

    /**
     * @param \Thruway\Event\ConnectionOpenEvent $event
     */
    public function handleConnectionOpen(ConnectionOpenEvent $event)
    {
        $this->sessions[$event->session->getSessionId()] = $event->session;
    }

    /**
     * @param \Thruway\Event\ConnectionCloseEvent $event
     */
    public function handleConnectionClose(ConnectionCloseEvent $event)
    {
        unset($this->sessions[$event->session->getSessionId()]);
        // TODO: should this be a message dispatched from the Transport?
        $event->session->onClose();
    }


    /**
     * @inheritdoc
     */
    public function createNewSession($transport)
    {
        $session = new Session($transport);

        return $session;
    }

    /**
     * @inheritdoc
     */
    public function stop($gracefully = true)
    {
        $this->getEventDispatcher()->dispatch('router.stop', new RouterStopEvent());
    }

    /**
     * Handle close transport
     *
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onClose(TransportInterface $transport)
    {
        Logger::debug($this, 'onClose from ' . json_encode($transport->getTransportDetails()));

        $this->sessions->detach($transport);
    }

    /**
     * Set authentication manager
     * @deprecated
     *
     * @throws \Exception
     */
    public function setAuthenticationManager($authenticationManager)
    {
        throw new \Exception('You must add the AuthenticationManager as a module');

    }

    /**
     * Get authentication manager
     *
     * @deprecated
     *
     * @throws \Exception
     */
    public function getAuthenticationManager()
    {
        throw new \Exception('AuthenticationManager is now a module');
    }

    /**
     * @deprecated
     *
     * @throws \Exception
     */
    public function getAuthorizationManager()
    {
        throw new \Exception('You must add the AuthorizationManager as a module');
    }

    /**
     * @deprecated
     *
     * @param $authorizationManager
     * @throws \Exception
     */
    public function setAuthorizationManager($authorizationManager)
    {
        throw new \Exception('AuthorizationManager is now a module');
    }

    /**
     * Get session by session ID
     *
     * @param int $sessionId
     * @return \Thruway\Session|boolean
     */
    public function getSessionBySessionId($sessionId)
    {
        if (!is_scalar($sessionId)) {
            return false;
        }

        return isset($this->sessions[$sessionId]) ? $this->sessions[$sessionId] : false;
    }

    /**
     * Set realm manager
     *
     * @param \Thruway\RealmManager $realmManager
     */
    public function setRealmManager($realmManager)
    {
        $this->realmManager = $realmManager;
    }

    /**
     * Get realm manager
     *
     * @return \Thruway\RealmManager
     */
    public function getRealmManager()
    {
        return $this->realmManager;
    }


    /**
     * Count number sessions
     *
     * @return array
     */
    public function managerGetSessionCount()
    {
        return [count($this->sessions)];
    }

    /**
     * Get list sessions
     *
     * @return array
     */
    public function managerGetSessions()
    {
        $theSessions = [];

        foreach ($this->sessions as $session) {
            /* @var \Thruway\Session $session */

            $sessionRealm = null;
            // just in case the session is not in a realm yet
            if ($session->getRealm() !== null) {
                $sessionRealm = $session->getRealm()->getRealmName();
            }

            if ($session->getAuthenticationDetails() !== null) {
                $authDetails = $session->getAuthenticationDetails();
                $auth        = [
                    'authid'     => $authDetails->getAuthId(),
                    'authmethod' => $authDetails->getAuthMethod()
                ];
            } else {
                $auth = new \stdClass();
            }

            $theSessions[] = [
                'id'           => $session->getSessionId(),
                'transport'    => $session->getTransport()->getTransportDetails(),
                'messagesSent' => $session->getMessagesSent(),
                'sessionStart' => $session->getSessionStart(),
                'realm'        => $sessionRealm,
                'auth'         => $auth
            ];
        }

        return [$theSessions];
    }

    /**
     * Get list realms
     *
     * @return array
     */
    public function managerGetRealms()
    {
        $theRealms = [];

        foreach ($this->realmManager->getRealms() as $realm) {
            /* @var $realm \Thruway\Realm */
            $theRealms[] = [
                'name' => $realm->getRealmName()
            ];
        }

        return [$theRealms];
    }

    /**
     * Registers a RouterModule
     *
     * @param RouterModuleInterface $module
     */
    public function registerModule(RouterModuleInterface $module)
    {
        $module->initModule($this);
        $this->eventDispatcher->addSubscriber($module);
        if ($module instanceof RouterModuleClient) {
            $m = new InternalClientTransportProvider($module);
            $this->eventDispatcher->addSubscriber($m);
        }
    }

    /**
     * Register Multiple Modules
     *
     * @param array $modules
     */
    public function registerModules(Array $modules)
    {
        foreach ($modules as $module) {
            $this->registerModule($module);
        }
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }
}
