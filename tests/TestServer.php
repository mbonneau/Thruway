<?php

require_once __DIR__ . '/bootstrap.php';

use Thruway\Router\Transport\InternalClientTransportProvider;
use Thruway\Router\Transport\RatchetTransportProvider;

//Logger::set(new \Psr\Log\NullLogger());

$timeout = isset($argv[1]) ? $argv[1] : 0;
$loop    = \React\EventLoop\Factory::create();

//Create a WebSocket connection that listens on localhost port 8090
//$router->addTransportProvider(new RatchetTransportProvider("127.0.0.1", 8090));

////////////////////////
//WAMP-CRA Authentication
// setup some users to auth against
$userDb = new \Thruway\Tests\UserDb();
$userDb->add('peter', 'secret1', 'salt123');
$userDb->add('joe', 'secret2', "mmm...salt");

//Add the WAMP-CRA Auth Provider
$authProvClient = new \Thruway\Authentication\WampCraAuthProvider(["test.wampcra.auth"], $loop);
$authProvClient->setUserDb($userDb);
///////////////////////

$modules = [
    // Create Authentication Manager
    new \Thruway\Authentication\AuthenticationManager(),
    // Test stuff for Authorization
    new \Thruway\Authentication\AuthorizationManager('authorizing_realm', $loop),
    // Create a realm with Authentication also to test some stuff
    new \Thruway\Authentication\AuthorizationManager("authful_realm", $loop),
    // Client for End-to-End testing
    new \Thruway\Tests\Clients\InternalClient('testRealm', $loop),
    // Client for Disclose Publisher Test
    new \Thruway\Tests\Clients\DisclosePublisherClient('testSimpleAuthRealm', $loop),
    // State Handler Testing
    new \Thruway\Subscription\StateHandlerRegistry('state.test.realm', $loop),

    // Websocket listener
    new RatchetTransportProvider($loop, "127.0.0.1", 8090),
    // Rawsocket listener
    new \Thruway\Router\Transport\RawSocketTransportProvider($loop, '127.0.0.1', 28181),

    //Provide authentication for the realm: 'testSimpleAuthRealm'
    new InternalClientTransportProvider(new \Thruway\Tests\Clients\SimpleAuthProviderClient(["testSimpleAuthRealm", "authful_realm"], $loop)),
    // provide aborting auth provider
    new InternalClientTransportProvider(new \Thruway\Tests\Clients\AbortAfterHelloAuthProviderClient(["abortafterhello"], $loop)),
    new InternalClientTransportProvider(new \Thruway\Tests\Clients\AbortAfterHelloWithDetailsAuthProviderClient(["abortafterhellowithdetails"], $loop)),
    new InternalClientTransportProvider(new \Thruway\Tests\Clients\AbortAfterAuthenticateWithDetailsAuthProviderClient(["aaawd"], $loop)),

    new InternalClientTransportProvider(new \Thruway\Tests\Clients\QueryParamAuthProviderClient(["query_param_auth_realm"], $loop)),
    new InternalClientTransportProvider($authProvClient)
];

$router  = new \Thruway\Router\Router($modules);

if ($timeout) {
    $loop->addTimer($timeout, function () use ($loop) {
        $loop->stop();
    });
}

$loop->run();
